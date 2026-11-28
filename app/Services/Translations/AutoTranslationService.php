<?php

namespace App\Services\Translations;

use App\Jobs\ProcessAutoTranslation;
use App\Models\Translations\AutoTranslationRun as Run;
use App\Models\Translations\Field;
use App\Models\Translations\Translation;
use App\Services\ContentWebhooks\TranslationPublisher;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class AutoTranslationService
{
    private const BATCH_SIZE = 20;

    private const BATCH_CHARACTERS = 12000;

    private const SOURCES = [TranslationService::REFERENCE_LANGUAGE_CODE, 'en'];

    public const OPENAI = 'openai';

    public const CLOUDFLARE = 'cloudflare';

    /**
     * Receive the services used to translate and publish dictionaries.
     *
     * @param  OpenAiTranslator  $openAiTranslator  OpenAI provider used to translate batches.
     * @param  CloudflareTranslator  $cloudflareTranslator  Cloudflare Workers AI provider used to translate batches.
     * @param  TranslationPublisher  $publisher  Dictionary writer.
     * @param  TranslationService  $translations  Export metadata and locale hierarchy service.
     */
    public function __construct(
        private readonly OpenAiTranslator $openAiTranslator,
        private readonly CloudflareTranslator $cloudflareTranslator,
        private readonly TranslationPublisher $publisher,
        private readonly TranslationService $translations,
    ) {}

    /**
     * Start a run, return an active run, or retry a failed publication.
     *
     * @param  Translation  $destination  Language selected by the admin.
     * @param  array{id: string, name: string, roles: list<string>}  $actor  Initiating admin captured for auditing.
     * @param  string  $provider  Translation provider selected by the admin.
     * @return Run The queued, active, or resumed run.
     */
    public function start(Translation $destination, array $actor, string $provider = self::OPENAI): Run
    {
        abort_unless(in_array($provider, [self::OPENAI, self::CLOUDFLARE], true), 422, 'ارائه‌دهنده ترجمه انتخاب‌شده معتبر نیست.');

        return DB::connection('sqlite')->transaction(function () use ($destination, $actor, $provider) {
            $created = Run::query()->insertOrIgnore([
                'translation_id' => $destination->id,
                'provider' => $provider,
                'active' => true,
                'status' => Run::QUEUED,
                'actor' => json_encode($actor, JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $run = Run::query()->where('translation_id', $destination->id)->where('active', true)->firstOrFail();

            if ($created) {
                abort_if($this->providerIsUnconfigured($provider), 503, $provider === self::OPENAI
                    ? 'کلید OpenAI برای ترجمه خودکار تنظیم نشده است.'
                    : 'اعتبار Cloudflare Workers AI برای ترجمه خودکار تنظیم نشده است.');
            } elseif ($run->status === Run::PUBLICATION_FAILED) {
                $run->update(['status' => Run::PUBLISHING, 'step' => $run->step + 1, 'error' => null]);
            } else {
                return $run;
            }
            $this->enqueue($run);

            return $run;
        }, 5);
    }

    /**
     * Stop an active run so reserved and queued jobs cannot save another batch.
     *
     * @param  Translation  $destination  Locale that owns the run.
     * @param  int  $runId  Active run identifier scoped to the locale.
     * @return Run Cancelled run with any translations saved before cancellation retained.
     */
    public function cancel(Translation $destination, int $runId): Run
    {
        return DB::connection('sqlite')->transaction(function () use ($destination, $runId) {
            $run = Run::query()
                ->where('translation_id', $destination->id)
                ->where('active', true)
                ->findOrFail($runId);

            abort_unless(in_array($run->status, [Run::QUEUED, Run::TRANSLATING, Run::PUBLISHING], true), 409, 'این صف ترجمه قابل توقف نیست.');

            $run->update([
                'status' => Run::CANCELLED,
                'active' => null,
                'items' => null,
                'results' => null,
                'error' => 'فرایند ترجمه خودکار توسط مدیر متوقف شد.',
            ]);

            return $run;
        }, 5);
    }

    /**
     * Process the current queue step and ignore previously superseded jobs.
     *
     * @param  int  $runId  Run to process.
     * @param  int  $step  Step number stored in the queued job.
     * @return void The worker advances or ends the run through its persisted state.
     */
    public function process(int $runId, int $step): void
    {
        $run = Run::query()->where('step', $step)->whereIn('status', [Run::QUEUED, Run::TRANSLATING, Run::PUBLISHING])->find($runId);
        if (! $run) {
            return;
        }

        $destination = Translation::query()->findOrFail($run->translation_id);

        match ($run->status) {
            Run::QUEUED => $this->prepare($run, $destination),
            Run::TRANSLATING => $this->translateBatch($run, $destination),
            Run::PUBLISHING => $this->publish($run),
        };
    }

    /**
     * Expose a retryable provider error while the queue waits for the next attempt.
     *
     * @param  int  $runId  Run with a failed provider request.
     * @param  int  $step  Step that made the request.
     * @param  string  $error  Displayable provider error.
     * @return void Updates the current step's progress record.
     */
    public function recordProviderError(int $runId, int $step, string $error): void
    {
        Run::query()->whereKey($runId)->where('step', $step)->where('status', Run::TRANSLATING)
            ->update(['error' => $error]);
    }

    /**
     * Replace a paused step with a durable delayed continuation.
     *
     * @param  int  $runId  Run awaiting provider availability.
     * @param  int  $step  Current step that should be superseded.
     * @param  TranslationProviderException  $error  Provider error and resume timestamp.
     * @param  int  $capacityRetries  Capacity retry count to carry forward.
     * @return void Saves the visible error and queues the next attempt.
     */
    public function defer(int $runId, int $step, TranslationProviderException $error, int $capacityRetries): void
    {
        $run = Run::query()->where('step', $step)->where('status', Run::TRANSLATING)->find($runId);
        if ($run) {
            $this->advance($run, function () use ($run, $error, $capacityRetries) {
                $run->update(['error' => $error->getMessage()]);
                Queue::connection('auto-translations')->later(
                    max(1, $error->resumeAt - now()->timestamp),
                    new ProcessAutoTranslation($run->id, $run->step, $capacityRetries),
                );
            });
        }
    }

    /**
     * Record an exhausted step and retain the run for publication retry when values were saved.
     *
     * @param  int  $runId  Run whose job failed.
     * @param  int  $step  Step number of the failed job.
     * @param  string|null  $error  Displayable provider error when a translation request failed.
     * @return void An exhausted publication stays eligible for a manual retry.
     */
    public function fail(int $runId, int $step, ?string $error): void
    {
        $query = Run::query()->whereKey($runId)->where('step', $step)->where('active', true);
        (clone $query)->where('status', Run::PUBLISHING)->update(['status' => Run::PUBLICATION_FAILED]);
        $query->whereIn('status', [Run::QUEUED, Run::TRANSLATING])->update([
            'status' => Run::FAILED,
            'active' => null,
            'items' => null,
            'results' => null,
            'error' => $error,
        ]);
    }

    /**
     * Snapshot candidate strings and queue the first batch when work is available.
     *
     * @param  Run  $run  Run entering the translation phase.
     * @param  Translation  $destination  Locale whose missing values are being selected.
     * @return void The run stores candidate keys and the initial progress count.
     */
    private function prepare(Run $run, Translation $destination): void
    {
        $items = $this->candidates($destination);
        $this->advance($run, function () use ($run, $items) {
            if ($items === []) {
                $run->update(['status' => Run::COMPLETED, 'active' => null]);

                return;
            }

            $run->update(['status' => Run::TRANSLATING, 'items' => $items, 'total' => count($items), 'results' => []]);
            $this->enqueue($run);
        });
    }

    /**
     * Translate a bounded batch and checkpoint its validated results.
     *
     * @param  Run  $run  Run with staged candidates and a progress offset.
     * @param  Translation  $destination  Locale to pass to the translation provider.
     * @return void Progress is persisted before the next job is queued.
     */
    private function translateBatch(Run $run, Translation $destination): void
    {
        $batch = [];
        $characters = 0;
        $translator = $this->translatorFor($run->provider);
        foreach (array_slice($run->items, $run->processed, min(self::BATCH_SIZE, $translator->batchSize()), preserve_keys: true) as $id => $item) {
            $characters += mb_strlen($item['text']);
            if ($batch !== [] && $characters > self::BATCH_CHARACTERS) {
                break;
            }
            $batch[$id] = $item;
        }

        $glossary = new TranslationGlossary;
        $providerBatch = $batch;
        $names = [];
        foreach ($providerBatch as $id => $item) {
            [$providerBatch[$id]['text'], $names[$id]] = $glossary->protect($item['text'], $item['source'], $destination->code);
        }

        try {
            $results = $translator->translate($destination, $providerBatch);
        } catch (TranslationProviderException $exception) {
            $this->commitPartialBatch($run, $destination, $batch, $this->restoreGlossary($exception->results, $names));

            throw $exception;
        }

        $this->commitBatch($run, $destination, $batch, $this->restoreGlossary($results, $names));
    }

    /**
     * Restore destination spellings in completed provider results.
     *
     * @param  array<int, string>  $results  Validated translations by field ID.
     * @param  array<int, array<string, string>>  $names  Placeholder-to-name maps by field ID.
     * @return array<int, string> Translations with glossary names restored.
     */
    private function restoreGlossary(array $results, array $names): array
    {
        foreach ($results as $id => $text) {
            $results[$id] = strtr($text, $names[$id]);
        }

        return $results;
    }

    /**
     * Commit a complete provider batch before scheduling the next provider request.
     *
     * @param  Run  $run  Active translation run.
     * @param  Translation  $destination  Locale receiving translated fields.
     * @param  array<int, array{id: string, source: string, text: string, modal: string, tab: string}>  $batch  Fields submitted to the provider.
     * @param  array<int, string>  $results  Validated provider translations keyed by field ID.
     * @return void Saves values and schedules the next batch or publication.
     */
    private function commitBatch(Run $run, Translation $destination, array $batch, array $results): void
    {
        $this->advance($run, function () use ($run, $destination, $batch, $results) {
            $saved = $this->saveBatchFields($destination, $batch, $results);
            $run->update([
                'processed' => $run->processed + count($results),
                'translated' => $run->translated + $saved,
                'error' => null,
            ]);

            if ($run->processed === $run->total) {
                $run->update([
                    'status' => $run->translated > 0 ? Run::PUBLISHING : Run::COMPLETED,
                    'active' => $run->translated > 0 ? true : null,
                    'items' => null,
                    'results' => null,
                ]);
            }

            if ($run->status === Run::TRANSLATING) {
                $this->enqueue($run);
            } elseif ($run->status === Run::PUBLISHING) {
                $this->enqueue($run);
            }
        });
    }

    /**
     * Commit completed Cloudflare fields without advancing the queue step that raised an error.
     *
     * @param  Run  $run  Active translation run.
     * @param  Translation  $destination  Locale receiving translated fields.
     * @param  array<int, array{id: string, source: string, text: string, modal: string, tab: string}>  $batch  Fields submitted to Cloudflare.
     * @param  array<int, string>  $results  Complete fields returned before the provider error.
     * @return void Persists fields so a rate-limit failure retains completed work.
     */
    private function commitPartialBatch(Run $run, Translation $destination, array $batch, array $results): void
    {
        if ($results === []) {
            return;
        }

        $step = $run->step;
        DB::connection('sqlite')->transaction(function () use ($run, $destination, $batch, $results, $step) {
            $run->refresh();
            if ($run->status !== Run::TRANSLATING || ! $run->active || $run->step !== $step) {
                return;
            }

            $saved = $this->saveBatchFields($destination, $batch, $results);
            $run->update([
                'processed' => $run->processed + count($results),
                'translated' => $run->translated + $saved,
            ]);
        }, 5);
    }

    /**
     * Apply validated fields and record the resulting dictionary changes for publication.
     *
     * @param  Translation  $destination  Locale receiving translated fields.
     * @param  array<int, array{id: string, source: string, text: string, modal: string, tab: string}>  $batch  Fields submitted to the provider.
     * @param  array<int, string>  $results  Validated provider translations keyed by field ID.
     * @return int Number of values written to SQLite.
     */
    private function saveBatchFields(Translation $destination, array $batch, array $results): int
    {
        $current = $this->candidates($destination);
        $saved = 0;

        foreach ($results as $id => $text) {
            if (($current[$id] ?? null) !== ($batch[$id] ?? null)) {
                continue;
            }

            $this->fillField($destination, $id, $batch[$id], $text);
            $saved++;
        }

        if ($saved > 0 && $destination->code === TranslationService::REFERENCE_LANGUAGE_CODE) {
            DB::connection('sqlite')->afterCommit($this->translations->forgetPersianHierarchyCache(...));
        }

        return $saved;
    }

    /**
     * Publish the committed dictionary.
     *
     * @param  Run  $run  Run whose translations are ready for publication.
     * @return void The run records completion after the dictionary is written.
     */
    private function publish(Run $run): void
    {
        $this->advance($run, function () use ($run) {
            $destination = Translation::query()->findOrFail($run->translation_id);
            $this->publisher->publish($destination->code);
            if ($destination->status) {
                $this->translations->updateExportMetadata($destination);
            }
            $run->update(['status' => Run::COMPLETED, 'active' => null]);
        });
    }

    /**
     * Fill existing blank rows or create the missing key in its source location.
     *
     * @param  Translation  $destination  Locale receiving the translated value.
     * @param  int  $id  Shared field ID.
     * @param  array{modal: string, tab: string}  $source  Location used when creating a missing field.
     * @param  string  $text  Validated translation to save.
     * @return void The affected field rows are saved in SQLite.
     */
    private function fillField(Translation $destination, int $id, array $source, string $text): void
    {
        $updated = Field::query()
            ->whereHas('tab.modal', fn ($query) => $query->where('translation_id', $destination->id))
            ->where('unique_id', $id)
            ->where(fn ($query) => $query->whereNull('translation')->orWhere('translation', ''))
            ->update(['translation' => $text]);

        if ($updated) {
            return;
        }

        $destination->modals()->firstOrCreate(['name' => $source['modal']])
            ->tabs()->firstOrCreate(['name' => $source['tab']])
            ->fields()->create(['unique_id' => $id, 'translation' => $text]);
    }

    /**
     * Select one source for each eligible key in Persian-to-English priority order.
     *
     * @param  Translation  $destination  Locale whose existing values determine eligibility.
     * @return array<int, array{id: string, source: string, text: string, modal: string, tab: string}>
     */
    private function candidates(Translation $destination): array
    {
        $sourceCodes = array_diff(self::SOURCES, [$destination->code]);
        $rows = DB::connection('sqlite')->table('fields')
            ->join('tabs', 'tabs.id', '=', 'fields.tab_id')
            ->join('modals', 'modals.id', '=', 'tabs.modal_id')
            ->join('translations', 'translations.id', '=', 'modals.translation_id')
            ->whereIn('translations.code', [...$sourceCodes, $destination->code])
            ->whereNotNull('fields.unique_id')
            ->whereNotNull('fields.translation')
            ->where('fields.translation', '<>', '')
            ->orderBy('fields.id')
            ->get(['fields.unique_id', 'fields.translation', 'translations.code', 'modals.name as modal', 'tabs.name as tab'])
            ->groupBy('code');

        $existing = ($rows[$destination->code] ?? collect())->keyBy('unique_id');
        $items = [];
        foreach ($sourceCodes as $code) {
            foreach ($rows[$code] ?? [] as $field) {
                $id = $field->unique_id;
                if (! isset($existing[$id]) && trim($field->translation) !== '') {
                    $items[$id] ??= [
                        'id' => (string) $id,
                        'source' => $code,
                        'text' => $field->translation,
                        'modal' => $field->modal,
                        'tab' => $field->tab,
                    ];
                }
            }
        }
        ksort($items, SORT_NUMERIC);

        return $items;
    }

    /**
     * Claim the current step before running its writes in the same SQLite transaction.
     *
     * @param  Run  $run  Run whose current step is being claimed.
     * @param  Closure(): void  $next  Writes to perform after claiming the step.
     * @return void The step claim and callback writes commit together.
     */
    private function advance(Run $run, Closure $next): void
    {
        $step = $run->step;
        DB::connection('sqlite')->transaction(function () use ($run, $step, $next) {
            if (Run::query()->whereKey($run->id)->where('step', $step)->where('active', true)->increment('step')) {
                $run->refresh();
                $next();
            }
        }, 5);
    }

    /**
     * Store the next queue job in the same SQLite transaction as the run.
     *
     * @param  Run  $run  Run with the step number to enqueue.
     * @return void The job becomes durable when the enclosing transaction commits.
     */
    private function enqueue(Run $run): void
    {
        Queue::connection('auto-translations')->push(new ProcessAutoTranslation($run->id, $run->step));
    }

    /**
     * Resolve the configured translator for a persisted run.
     *
     * @param  string  $provider  Provider identifier stored on the run.
     * @return OpenAiTranslator|CloudflareTranslator Provider implementation.
     */
    private function translatorFor(string $provider): TranslationProvider
    {
        return match ($provider) {
            self::CLOUDFLARE => $this->cloudflareTranslator,
            default => $this->openAiTranslator,
        };
    }

    /**
     * Determine whether the selected provider has the credentials required for a new run.
     *
     * @param  string  $provider  Provider identifier selected by the admin.
     * @return bool Whether configuration is incomplete.
     */
    private function providerIsUnconfigured(string $provider): bool
    {
        return $provider === self::CLOUDFLARE
            ? blank(config('services.cloudflare.account_id')) || blank(config('services.cloudflare.api_token'))
            : blank(config('services.openai.key'));
    }
}

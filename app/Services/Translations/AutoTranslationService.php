<?php

namespace App\Services\Translations;

use App\Jobs\ProcessAutoTranslation;
use App\Models\Translations\AutoTranslationRun as Run;
use App\Models\Translations\Field;
use App\Models\Translations\Translation;
use App\Services\ContentWebhooks\ChangeRecorder;
use App\Services\ContentWebhooks\TranslationPublisher;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Throwable;

class AutoTranslationService
{
    private const BATCH_SIZE = 20;

    private const BATCH_CHARACTERS = 12000;

    private const SOURCES = [TranslationService::REFERENCE_LANGUAGE_CODE, 'en'];

    /**
     * Receive the services used to translate and publish dictionaries.
     *
     * @param  OpenAiTranslator  $translator  Provider used to translate each batch.
     * @param  TranslationPublisher  $publisher  Dictionary writer and change recorder.
     * @param  TranslationService  $translations  Export metadata and locale hierarchy service.
     */
    public function __construct(
        private readonly OpenAiTranslator $translator,
        private readonly TranslationPublisher $publisher,
        private readonly TranslationService $translations,
    ) {}

    /**
     * Start a run, return an active run, or retry a failed publication.
     *
     * @param  Translation  $destination  Language selected by the admin.
     * @param  array{id: string, name: string, roles: list<string>}  $actor  Initiating admin captured for auditing.
     * @return Run The queued, active, or resumed run.
     */
    public function start(Translation $destination, array $actor): Run
    {
        return DB::connection('sqlite')->transaction(function () use ($destination, $actor) {
            $created = Run::query()->insertOrIgnore([
                'translation_id' => $destination->id,
                'active' => true,
                'status' => Run::QUEUED,
                'actor' => json_encode($actor, JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $run = Run::query()->where('translation_id', $destination->id)->where('active', true)->firstOrFail();

            if ($created) {
                abort_if(blank(config('services.openai.key')), 503, 'کلید OpenAI برای ترجمه خودکار تنظیم نشده است.');
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
     * @param  string  $error  Displayable OpenAI error.
     * @return void Updates the current step's progress record.
     */
    public function recordProviderError(int $runId, int $step, string $error): void
    {
        Run::query()->whereKey($runId)->where('step', $step)->where('status', Run::TRANSLATING)
            ->update(['error' => $error]);
    }

    /**
     * Record an exhausted step and retain the run for publication retry when values were saved.
     *
     * @param  int  $runId  Run whose job failed.
     * @param  int  $step  Step number of the failed job.
     * @param  string|null  $error  Displayable provider error when the failure came from OpenAI.
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
        foreach (array_slice($run->items, $run->processed, self::BATCH_SIZE, preserve_keys: true) as $id => $item) {
            $characters += mb_strlen($item['text']);
            if ($batch !== [] && $characters > self::BATCH_CHARACTERS) {
                break;
            }
            $batch[$id] = $item;
        }

        $results = $this->translator->translate($destination, $batch);
        $this->advance($run, function () use ($run, $results) {
            $run->update([
                'results' => $run->results + $results,
                'processed' => $run->processed + count($results),
                'error' => null,
            ]);

            if ($run->processed === $run->total) {
                $this->saveTranslations($run);
            } else {
                $this->enqueue($run);
            }
        });
    }

    /**
     * Apply the staged results after rechecking the source text and destination inside the write transaction.
     *
     * @param  Run  $run  Run with all candidate translations staged.
     * @return void The caller commits the translation writes and outbox event together.
     */
    private function saveTranslations(Run $run): void
    {
        $destination = Translation::query()->findOrFail($run->translation_id);
        $current = $this->candidates($destination);
        $before = $this->publisher->snapshot();
        $translated = 0;

        foreach ($run->results as $id => $text) {
            // A deleted or edited source, or a populated destination, invalidates the staged translation.
            if (($current[$id] ?? null) !== $run->items[$id]) {
                continue;
            }

            $this->fillField($destination, $id, $current[$id], $text);
            $translated++;
        }

        $run->update([
            'status' => $translated > 0 ? Run::PUBLISHING : Run::COMPLETED,
            'active' => $translated > 0 ? true : null,
            'translated' => $translated,
            'items' => null,
            'results' => null,
        ]);

        if ($translated > 0) {
            $this->publisher->record($before, actor: $run->actor);
            $this->enqueue($run);
            if ($destination->code === TranslationService::REFERENCE_LANGUAGE_CODE) {
                DB::connection('sqlite')->afterCommit($this->translations->forgetPersianHierarchyCache(...));
            }
        }
    }

    /**
     * Publish the committed dictionary and project its audit record after publication.
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

        try {
            app(ChangeRecorder::class)->reconcile();
        } catch (Throwable $exception) {
            report($exception);
        }
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
}

<?php

namespace App\Services\ContentWebhooks;

use App\Models\Translations\Translation;
use App\Services\Translations\TranslationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TranslationPublisher
{
    /**
     * Track field IDs changed since this locale's last successful manual export.
     *
     * @param  Translation  $translation  Exported locale and updated export metadata.
     * @param  array<int|string, string|null>  $dictionary  Exact flat dictionary written to disk.
     * @return void Saves the baseline and source event in the caller's SQLite transaction.
     */
    public function recordExport(Translation $translation, array $dictionary): void
    {
        unset($dictionary['']); // Legacy unkeyed fields have no unique_id.
        $snapshot = DB::connection('sqlite')->table('translation_export_snapshots');
        $previous = $snapshot->where('translation_id', $translation->id)->value('dictionary');
        $previous = $previous === null ? [] : json_decode($previous, true, flags: JSON_THROW_ON_ERROR);
        $keys = array_keys($dictionary + $previous);
        sort($keys, SORT_NUMERIC);
        $changed = [];
        foreach ($keys as $key) {
            if (! array_key_exists($key, $previous) || ! array_key_exists($key, $dictionary) || $dictionary[$key] !== $previous[$key]) {
                $changed[] = (string) $key;
            }
        }

        $snapshot->updateOrInsert(['translation_id' => $translation->id], [
            'dictionary' => json_encode($dictionary, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE),
        ]);
        app(ChangeRecorder::class)->write('sqlite', [
            'feature' => 'translations',
            'category' => 'translations',
            'entity_id' => (string) $translation->getKey(),
            'unique_ids' => $changed,
            'locale' => $translation->code,
            'resource' => 'dictionary',
            'event' => 'exported',
            'action' => 'UPDATE',
            'description' => __('content_webhooks.translation_exported', ['locale' => $translation->code]),
            'attributes' => ['version' => $translation->version],
            'affected_routes' => ['/lang/'.rawurlencode(strtolower($translation->code)).'.json'],
        ]);
    }

    /**
     * Write an active locale’s JSON or remove the file when the locale is unpublished.
     *
     * @param  string  $locale  Locale code used to select the dictionary and output filename.
     * @return void The resulting file state reflects the locale's publication status.
     */
    public function publish(string $locale): void
    {
        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $locale)) {
            throw new \RuntimeException('Invalid language code.');
        }
        $translation = Translation::where('code', $locale)->first();
        $path = storage_path('app/lang/'.strtolower($locale).'.json');
        if (! $translation || ! $translation->status) {
            if (is_file($path) && ! unlink($path)) {
                throw new \RuntimeException('Cannot remove unpublished dictionary.');
            }

            return;
        }
        $payload = app(TranslationService::class)->buildFlatPayloadForTranslation($translation);
        $payload = array_filter($payload, fn ($value) => $value !== null);
        File::ensureDirectoryExists(dirname($path));
        File::replace($path, json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT));
    }
}

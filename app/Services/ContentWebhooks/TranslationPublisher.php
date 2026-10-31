<?php

namespace App\Services\ContentWebhooks;

use App\Models\Translations\Translation;
use App\Services\Translations\TranslationService;
use Illuminate\Support\Facades\File;

class TranslationPublisher
{
    public function snapshot(): array
    {
        return Translation::with('modals.tabs')->get()->mapWithKeys(function ($translation) {
            $fields = app(TranslationService::class)->buildFlatPayloadForTranslation($translation);
            // Empty replicated placeholders are not published translations.
            $fields = array_filter($fields, fn ($value) => $value !== null);
            ksort($fields);
            $structure = $translation->modals->map(fn ($modal) => ['id' => $modal->id, 'name' => $modal->name,
                'tabs' => $modal->tabs->map(fn ($tab) => ['id' => $tab->id, 'name' => $tab->name])->all()])->all();

            return [$translation->code => ['id' => (string) $translation->id, 'status' => (bool) $translation->status, 'fields' => $fields, 'structure' => $structure]];
        })->all();
    }

    public function record(array $before, string $operation = 'updated'): void
    {
        $after = $this->snapshot();
        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $locale) {
            $old = $before[$locale] ?? null;
            $new = $after[$locale] ?? null;
            if ($old === $new) {
                continue;
            }
            $event = ! $old ? 'created' : (! $new ? 'deleted' : $operation);
            $publishedChanged = ! $old || ! $new || $old['status'] !== $new['status'] || $old['fields'] !== $new['fields'];
            $changedKeys = array_keys(array_diff_assoc($new['fields'] ?? [], $old['fields'] ?? []) + array_diff_assoc($old['fields'] ?? [], $new['fields'] ?? []));
            $detail = $changedKeys ? 'fields '.implode(', ', $changedKeys) :
                (($old['status'] ?? null) !== ($new['status'] ?? null) ? 'publication status' : 'structure');
            app(ChangeRecorder::class)->write('sqlite', [
                'feature' => 'translations', 'category' => 'translations', 'entity_id' => ($new ?? $old)['id'],
                'locale' => $locale, 'resource' => 'dictionary', 'event' => $event,
                'action' => match ($event) {
                    'created' => 'CREATE', 'deleted' => 'DELETE', default => 'UPDATE'
                },
                'description' => "The {$locale} translation {$detail} has been {$event}.",
                'attributes' => $new, 'old' => $old,
                'affected_routes' => $publishedChanged ? ['/lang/'.rawurlencode(strtolower($locale)).'.json'] : [],
            ]);
        }
    }

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

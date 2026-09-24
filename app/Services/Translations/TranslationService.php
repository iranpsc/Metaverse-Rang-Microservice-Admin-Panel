<?php

namespace App\Services\Translations;

use App\Models\Translations\Field;
use App\Models\Translations\Modal;
use App\Models\Translations\Tab;
use App\Models\Translations\Translation;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JsonException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TranslationService
{
    private const LANG_CACHE_KEY = 'translations.available_languages';

    /**
     * Persian is the canonical hierarchy source for flat JSON imports
     * (unique_id => modal/tab placement).
     */
    public const REFERENCE_LANGUAGE_CODE = 'fa';

    private const REFERENCE_HIERARCHY_CACHE_KEY = 'translations.persian_hierarchy_map';

    public function __construct(private readonly Filesystem $filesystem) {}

    /**
     * @return array<int, array{id: string, code: string, name: string, nativeName: string, dir: string}>
     *
     * @throws FileNotFoundException
     */
    public function availableLanguages(): array
    {
        return Cache::rememberForever(self::LANG_CACHE_KEY, function () {
            $path = public_path('lang/lang.json');

            if (! $this->filesystem->exists($path)) {
                throw new FileNotFoundException("Language definition file not found at {$path}");
            }

            $langFile = $this->filesystem->get($path);

            try {
                $languages = json_decode($langFile, true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw ValidationException::withMessages([
                    'languages' => __('translations.invalid_language_definition'),
                ]);
            }

            return collect($languages)
                ->map(function (array $language, string|int $key) {
                    $direction = in_array($language['code'], ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr';

                    return [
                        'id' => $key,
                        'code' => $language['code'],
                        'name' => $language['name'],
                        'nativeName' => $language['nativeName'],
                        'dir' => $direction,
                    ];
                })
                ->values()
                ->all();
        });
    }

    public function createTranslationByCode(string $languageCode): Translation
    {
        $language = collect($this->availableLanguages())
            ->firstWhere('code', $languageCode);

        if (! $language) {
            throw ValidationException::withMessages([
                'code' => __('translations.unsupported_language_code', ['code' => $languageCode]),
            ]);
        }

        return DB::connection('sqlite')->transaction(function () use ($language) {
            $translation = Translation::create([
                'code' => $language['code'],
                'name' => $language['name'],
                'native_name' => Arr::get($language, 'nativeName'),
                'direction' => Arr::get($language, 'dir', 'ltr'),
                'status' => true,
            ]);

            $this->replicateStructureForTranslation($translation);

            return $translation;
        });
    }

    public function deleteTranslation(Translation $translation): void
    {
        $translation->delete();
    }

    public function toggleStatus(Translation $translation): Translation
    {
        $translation->status = ! (bool) $translation->status;
        $translation->save();

        return $translation->refresh();
    }

    public function exportTranslation(Translation $translation): BinaryFileResponse
    {
        $payload = $this->buildFlatPayloadForTranslation($translation);
        $filePath = $this->writeLangFile($translation, $payload);

        $translation->increment('version');
        $fileName = strtolower($translation->code).'.json';
        $absoluteUrl = sprintf('%s/lang/%s', rtrim((string) config('app.url'), '/'), $fileName);
        $translation->update(['file_url' => $absoluteUrl]);

        return response()->download($filePath, $fileName);
    }

    /**
     * Create a new translation language and import flat JSON values into it.
     * Modal/tab placement for each unique_id is resolved from the Persian (fa) hierarchy.
     *
     * @return array{
     *     updated: int,
     *     created: int,
     *     skipped: int,
     *     unknown_ids: list<int|string>,
     *     translation: Translation
     * }
     */
    public function createAndImportTranslation(string $languageCode, UploadedFile|array|string $source): array
    {
        // Validate JSON shape against the fa.json / en.json flat pattern before creating anything.
        $payload = $this->normalizeImportPayload($source);
        $this->assertPersianHierarchyAvailable();

        return DB::connection('sqlite')->transaction(function () use ($languageCode, $payload) {
            $translation = $this->createTranslationByCode($languageCode);

            return $this->importTranslation($translation, $payload);
        });
    }

    /**
     * Import a flat lang JSON file (same shape as public/lang/fa.json) into a translation.
     * Placement of each unique_id is resolved from the Persian (fa) hierarchy.
     *
     * @return array{
     *     updated: int,
     *     created: int,
     *     skipped: int,
     *     unknown_ids: list<int|string>,
     *     translation: Translation
     * }
     */
    public function importTranslation(Translation $translation, UploadedFile|array|string $source): array
    {
        $payload = $this->normalizeImportPayload($source);
        $hierarchy = $this->assertPersianHierarchyAvailable();

        $stats = DB::connection('sqlite')->transaction(function () use ($translation, $payload, $hierarchy) {
            $updated = 0;
            $created = 0;
            $skipped = 0;
            $unknownIds = [];

            $modalCache = [];
            $tabCache = [];

            foreach ($payload as $rawUniqueId => $value) {
                $normalizedValue = $this->normalizeImportedValue($value);

                // Flat JSON may include "" for fields whose unique_id is null in the DB.
                if ($rawUniqueId === '' || $rawUniqueId === null) {
                    $emptyResult = $this->upsertFieldsByUniqueId(
                        $translation,
                        null,
                        $normalizedValue
                    );
                    $updated += $emptyResult['updated'];
                    $created += $emptyResult['created'];

                    if ($emptyResult['updated'] === 0 && $emptyResult['created'] === 0) {
                        $skipped++;
                    }

                    continue;
                }

                if (! is_numeric($rawUniqueId)) {
                    $unknownIds[] = $rawUniqueId;
                    $skipped++;

                    continue;
                }

                $uniqueId = (int) $rawUniqueId;
                $locations = $hierarchy[$uniqueId] ?? [];

                if ($locations === []) {
                    $unknownIds[] = $uniqueId;
                    $skipped++;

                    continue;
                }

                foreach ($locations as $location) {
                    $modalName = $location['modal'];
                    $tabName = $location['tab'];
                    $modalKey = $modalName;
                    $tabKey = $modalName.'|'.$tabName;

                    if (! isset($modalCache[$modalKey])) {
                        $modalCache[$modalKey] = Modal::query()->firstOrCreate(
                            [
                                'translation_id' => $translation->id,
                                'name' => $modalName,
                            ]
                        );
                    }

                    if (! isset($tabCache[$tabKey])) {
                        $tabCache[$tabKey] = Tab::query()->firstOrCreate(
                            [
                                'modal_id' => $modalCache[$modalKey]->id,
                                'name' => $tabName,
                            ]
                        );
                    }

                    $result = $this->upsertFieldsInTab(
                        $tabCache[$tabKey],
                        $uniqueId,
                        $normalizedValue
                    );
                    $updated += $result['updated'];
                    $created += $result['created'];
                }
            }

            return [
                'updated' => $updated,
                'created' => $created,
                'skipped' => $skipped,
                'unknown_ids' => array_values(array_unique($unknownIds)),
            ];
        });

        $flatPayload = $this->buildFlatPayloadForTranslation($translation);
        $this->writeLangFile($translation, $flatPayload);

        $translation->increment('version');
        $fileName = strtolower($translation->code).'.json';
        $absoluteUrl = sprintf('%s/lang/%s', rtrim((string) config('app.url'), '/'), $fileName);
        $translation->update(['file_url' => $absoluteUrl]);

        return [
            ...$stats,
            'translation' => $translation->refresh()->loadCount('modals'),
        ];
    }

    /**
     * Map each unique_id to its Persian modal/tab locations.
     *
     * @return array<int, list<array{modal: string, tab: string}>>
     */
    public function buildPersianHierarchyMap(): array
    {
        return Cache::rememberForever(self::REFERENCE_HIERARCHY_CACHE_KEY, function () {
            $persian = Translation::query()
                ->where('code', self::REFERENCE_LANGUAGE_CODE)
                ->with([
                    'modals.tabs.fields' => function ($query) {
                        $query->orderBy('unique_id');
                    },
                ])
                ->first();

            if (! $persian) {
                return [];
            }

            $map = [];

            foreach ($persian->modals as $modal) {
                foreach ($modal->tabs as $tab) {
                    foreach ($tab->fields as $field) {
                        if ($field->unique_id === null) {
                            continue;
                        }

                        $uniqueId = (int) $field->unique_id;
                        $location = [
                            'modal' => $modal->name,
                            'tab' => $tab->name,
                        ];

                        $map[$uniqueId] ??= [];

                        $alreadyMapped = collect($map[$uniqueId])->contains(
                            fn (array $existing) => $existing['modal'] === $location['modal']
                                && $existing['tab'] === $location['tab']
                        );

                        if (! $alreadyMapped) {
                            $map[$uniqueId][] = $location;
                        }
                    }
                }
            }

            ksort($map);

            return $map;
        });
    }

    public function forgetPersianHierarchyCache(): void
    {
        Cache::forget(self::REFERENCE_HIERARCHY_CACHE_KEY);
    }

    public function getModalsForTranslation(Translation $translation, int $perPage = 10)
    {
        return $translation->modals()->withCount([
            'tabs',
            'tabs as total_fields_count' => function ($query) {
                $query->join('fields', 'tabs.id', '=', 'fields.tab_id');
            },
            'tabs as translated_fields_count' => function ($query) {
                $query->join('fields', 'tabs.id', '=', 'fields.tab_id')
                    ->whereNotNull('fields.translation');
            },
        ])->paginate($perPage);
    }

    public function getTabsForModal(Modal $modal, int $perPage = 10)
    {
        return $modal->tabs()->withCount([
            'fields',
            'fields as translated_fields_count' => function ($query) {
                $query->whereNotNull('translation');
            },
        ])->paginate($perPage);
    }

    public function getFieldsForTab(Tab $tab, int $perPage = 10)
    {
        return $tab->fields()->orderBy('unique_id')->paginate($perPage);
    }

    public function createModal(string $name): void
    {
        DB::connection('sqlite')->transaction(function () use ($name) {
            $translations = Translation::all();

            foreach ($translations as $translation) {
                $translation->modals()->create([
                    'name' => trim($name),
                ]);
            }
        });

        $this->forgetPersianHierarchyCache();
    }

    public function updateModal(Modal $modal, string $name): void
    {
        DB::connection('sqlite')->transaction(function () use ($modal, $name) {
            $modals = Modal::where('name', $modal->name)->get();

            foreach ($modals as $existingModal) {
                $existingModal->update([
                    'name' => trim($name),
                ]);
            }
        });

        $this->forgetPersianHierarchyCache();
    }

    public function deleteModal(Modal $modal): void
    {
        DB::connection('sqlite')->transaction(function () use ($modal) {
            Modal::where('name', $modal->name)->delete();
        });

        $this->forgetPersianHierarchyCache();
    }

    public function createTab(Modal $modal, string $name): void
    {
        DB::connection('sqlite')->transaction(function () use ($modal, $name) {
            $modals = Modal::where('name', $modal->name)->get();

            foreach ($modals as $eachModal) {
                $eachModal->tabs()->create([
                    'name' => trim($name),
                ]);
            }
        });

        $this->forgetPersianHierarchyCache();
    }

    public function updateTab(Tab $tab, string $name): void
    {
        DB::connection('sqlite')->transaction(function () use ($tab, $name) {
            $tabs = Tab::where('name', $tab->name)
                ->whereHas('modal', function ($query) use ($tab) {
                    $query->where('name', $tab->modal->name);
                })
                ->get();

            foreach ($tabs as $existingTab) {
                $existingTab->update([
                    'name' => trim($name),
                ]);
            }
        });

        $this->forgetPersianHierarchyCache();
    }

    public function deleteTab(Tab $tab): void
    {
        DB::connection('sqlite')->transaction(function () use ($tab) {
            Tab::where('name', $tab->name)
                ->whereHas('modal', function ($query) use ($tab) {
                    $query->where('name', $tab->modal->name);
                })
                ->delete();
        });

        $this->forgetPersianHierarchyCache();
    }

    public function createField(Tab $tab, string $translationValue): Field
    {
        $field = DB::connection('sqlite')->transaction(function () use ($tab, $translationValue) {
            $tab->loadMissing('modal');

            $latestUniqueId = (int) Field::max('unique_id');
            $nextUniqueId = $latestUniqueId > 0 ? $latestUniqueId + 1 : 1;

            $field = $tab->fields()->create([
                'unique_id' => $nextUniqueId,
                'translation' => $translationValue,
            ]);

            $matchingTabs = Tab::query()
                ->where('name', $tab->name)
                ->whereHas('modal', function ($query) use ($tab) {
                    $query->where('name', $tab->modal->name);
                })
                ->whereKeyNot($tab->getKey())
                ->get();

            foreach ($matchingTabs as $matchingTab) {
                $matchingTab->fields()->create([
                    'unique_id' => $nextUniqueId,
                ]);
            }

            return $field;
        });

        $this->forgetPersianHierarchyCache();

        return $field;
    }

    public function updateField(Field $field, string $value): void
    {
        $field->update([
            'translation' => $value,
        ]);
    }

    public function deleteField(Field $field): void
    {
        Field::where('unique_id', $field->unique_id)->delete();
        $this->forgetPersianHierarchyCache();
    }

    private function replicateStructureForTranslation(Translation $translation): void
    {
        $modals = Modal::query()
            ->with([
                'tabs.fields' => function ($query) {
                    $query->orderBy('unique_id');
                },
            ])
            ->get()
            ->unique('name');

        foreach ($modals as $modal) {
            if ($translation->modals()->where('name', $modal->name)->exists()) {
                continue;
            }

            $newModal = $translation->modals()->create([
                'name' => $modal->name,
            ]);

            foreach ($modal->tabs as $tab) {
                $newTab = $newModal->tabs()->create([
                    'name' => $tab->name,
                ]);

                // Source data can contain duplicate unique_ids in the same tab.
                // Copy each unique_id only once so imports/exports stay deterministic.
                $seenUniqueIds = [];

                foreach ($tab->fields as $field) {
                    $dedupeKey = $field->unique_id === null
                        ? 'null'
                        : (string) (int) $field->unique_id;

                    if (isset($seenUniqueIds[$dedupeKey])) {
                        continue;
                    }

                    $seenUniqueIds[$dedupeKey] = true;

                    $newTab->fields()->create([
                        'unique_id' => $field->unique_id,
                        'translation' => null,
                    ]);
                }
            }
        }
    }

    /**
     * Build the flat lang JSON payload. When the same unique_id exists on multiple
     * rows, prefer a non-null translation so export matches the imported file.
     *
     * @return array<int|string, string|null>
     */
    private function buildFlatPayloadForTranslation(Translation $translation): array
    {
        $fields = Field::query()
            ->whereHas('tab.modal', function ($query) use ($translation) {
                $query->where('translation_id', $translation->id);
            })
            ->orderBy('unique_id')
            ->orderBy('id')
            ->get(['unique_id', 'translation']);

        $payload = [];

        foreach ($fields as $field) {
            $key = $field->unique_id === null ? '' : (int) $field->unique_id;

            if (! array_key_exists($key, $payload)) {
                $payload[$key] = $field->translation;

                continue;
            }

            if ($payload[$key] === null && $field->translation !== null) {
                $payload[$key] = $field->translation;
            }
        }

        return $payload;
    }

    /**
     * Update every field with the given unique_id inside a tab, or create one.
     * Persian source data can contain duplicate unique_ids in the same tab;
     * firstOrNew alone would leave sibling duplicates as null and break export.
     *
     * @return array{updated: int, created: int}
     */
    private function upsertFieldsInTab(Tab $tab, int $uniqueId, ?string $value): array
    {
        $fields = Field::query()
            ->where('tab_id', $tab->id)
            ->where('unique_id', $uniqueId)
            ->get();

        if ($fields->isEmpty()) {
            $tab->fields()->create([
                'unique_id' => $uniqueId,
                'translation' => $value,
            ]);

            return ['updated' => 0, 'created' => 1];
        }

        foreach ($fields as $field) {
            $field->translation = $value;
            $field->save();
        }

        return ['updated' => $fields->count(), 'created' => 0];
    }

    /**
     * Update every field with the given unique_id across a translation.
     * Used for the empty JSON key ("") which maps to unique_id = null.
     *
     * @return array{updated: int, created: int}
     */
    private function upsertFieldsByUniqueId(Translation $translation, ?int $uniqueId, ?string $value): array
    {
        $fields = Field::query()
            ->whereHas('tab.modal', function ($query) use ($translation) {
                $query->where('translation_id', $translation->id);
            })
            ->when(
                $uniqueId === null,
                fn ($query) => $query->whereNull('unique_id'),
                fn ($query) => $query->where('unique_id', $uniqueId)
            )
            ->get();

        if ($fields->isEmpty()) {
            return ['updated' => 0, 'created' => 0];
        }

        foreach ($fields as $field) {
            $field->translation = $value;
            $field->save();
        }

        return ['updated' => $fields->count(), 'created' => 0];
    }

    /**
     * @param  array<int|string, string|null>  $payload
     */
    private function writeLangFile(Translation $translation, array $payload): string
    {
        $fileName = strtolower($translation->code).'.json';
        $filePath = public_path("lang/{$fileName}");
        $encodedPayload = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT
        );

        $this->filesystem->ensureDirectoryExists(dirname($filePath));
        $this->filesystem->put($filePath, $encodedPayload);

        return $filePath;
    }

    /**
     * @return array<int, list<array{modal: string, tab: string}>>
     */
    private function assertPersianHierarchyAvailable(): array
    {
        $hierarchy = $this->buildPersianHierarchyMap();

        if ($hierarchy === []) {
            throw ValidationException::withMessages([
                'file' => __('translations.persian_hierarchy_missing'),
            ]);
        }

        return $hierarchy;
    }

    /**
     * Normalize and validate import payload against the public/lang/fa.json pattern:
     * a flat JSON object of numeric unique_id keys to string/null values.
     *
     * @return array<int|string, mixed>
     */
    private function normalizeImportPayload(UploadedFile|array|string $source): array
    {
        if (is_array($source)) {
            $payload = $source;
        } else {
            $raw = $source instanceof UploadedFile
                ? $this->filesystem->get($source->getRealPath())
                : $source;

            try {
                $payload = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw ValidationException::withMessages([
                    'file' => __('translations.invalid_json_file'),
                ]);
            }
        }

        if (! is_array($payload)) {
            throw ValidationException::withMessages([
                'file' => __('translations.invalid_structure_flat_object'),
            ]);
        }

        // json_decode('{}') becomes [] — allow empty; reject true JSON arrays.
        if ($payload !== [] && array_is_list($payload)) {
            throw ValidationException::withMessages([
                'file' => __('translations.invalid_structure_flat_object'),
            ]);
        }

        foreach ($payload as $rawUniqueId => $value) {
            if ($rawUniqueId === '' || $rawUniqueId === null) {
                continue;
            }

            if (! is_numeric($rawUniqueId) || (string) (int) $rawUniqueId !== (string) $rawUniqueId) {
                throw ValidationException::withMessages([
                    'file' => __('translations.invalid_structure_numeric_keys'),
                ]);
            }

            if (is_bool($value) || is_array($value) || is_object($value)) {
                throw ValidationException::withMessages([
                    'file' => __('translations.invalid_structure_string_values'),
                ]);
            }
        }

        return $payload;
    }

    private function normalizeImportedValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value) || is_array($value) || is_object($value)) {
            throw ValidationException::withMessages([
                'file' => __('translations.invalid_value_type'),
            ]);
        }

        return (string) $value;
    }
}

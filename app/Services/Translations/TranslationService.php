<?php

namespace App\Services\Translations;

use App\Models\Translations\Field;
use App\Models\Translations\Modal;
use App\Models\Translations\Tab;
use App\Models\Translations\Translation;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use JsonException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TranslationService
{
    private const LANG_CACHE_KEY = 'translations.available_languages';

    public function __construct(private readonly Filesystem $filesystem)
    {
    }

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
                    'languages' => 'Invalid language definition file.',
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
                'code' => "The provided language code [{$languageCode}] is not supported.",
            ]);
        }

        return DB::connection('sqlite')->transaction(function () use ($language) {
            $translation = Translation::create([
                'code' => $language['code'],
                'name' => $language['name'],
                'native_name' => Arr::get($language, 'nativeName'),
                'direction' => Arr::get($language, 'dir', 'ltr'),
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

    public function exportTranslation(Translation $translation): BinaryFileResponse|string
    {
        $translation->load('modals.tabs.fields');

        $clone = $translation->replicate();
        $clone->setRelation('modals', $translation->modals->map(function (Modal $modal) {
            $modalClone = $modal->replicate();
            $modalClone->setRelation('tabs', $modal->tabs->map(function (Tab $tab) {
                $tabClone = $tab->replicate();
                $tabClone->setRelation('fields', $tab->fields->map(function (Field $field) {
                    $fieldClone = $field->replicate();
                    $fieldClone->makeHidden(['id', 'tab_id', 'name']);
                    return $fieldClone;
                }));

                $tabClone->makeHidden(['id', 'modal_id']);
                return $tabClone;
            }));

            $modalClone->makeHidden(['id', 'translation_id']);
            return $modalClone;
        }));

        $clone->makeHidden(['id', 'created_at', 'updated_at']);

        $fileName = strtolower($translation->code).'.json';
        $filePath = public_path("lang/{$fileName}");

        $payload = collect($clone->toArray())
            ->put('file_url', null)
            ->toArray();

        file_put_contents($filePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $translation->increment('version');
        $absoluteUrl = sprintf('https://rgb.irpsc.com/lang/%s', $fileName);
        $translation->update(['file_url' => $absoluteUrl]);

        $payload['file_url'] = $absoluteUrl;
        file_put_contents($filePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if (app()->environment('local')) {
            return response()->download($filePath, $fileName);
        }

        if (! Storage::disk('ftp')->put("lang/{$fileName}", json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            throw ValidationException::withMessages([
                'export' => __('translations.export_failed'),
            ]);
        }

        return __('translations.export_success');
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
    }

    public function deleteModal(Modal $modal): void
    {
        DB::connection('sqlite')->transaction(function () use ($modal) {
            Modal::where('name', $modal->name)->delete();
        });
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
    }

    public function createField(Tab $tab, string $translationValue): Field
    {
        return DB::connection('sqlite')->transaction(function () use ($tab, $translationValue) {
            $latestUniqueId = (int) Field::max('unique_id');
            $nextUniqueId = $latestUniqueId > 0 ? $latestUniqueId + 1 : 1;

            $field = $tab->fields()->create([
                'unique_id' => $nextUniqueId,
                'translation' => $translationValue,
            ]);

            $matchingTabs = Tab::where('name', $tab->name)
                ->whereKeyNot($tab->getKey())
                ->get();

            foreach ($matchingTabs as $matchingTab) {
                $matchingTab->fields()->create([
                    'unique_id' => $nextUniqueId,
                ]);
            }

            return $field;
        });
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

                foreach ($tab->fields as $field) {
                    $newTab->fields()->create([
                        'unique_id' => $field->unique_id,
                        'translation' => null,
                    ]);
                }
            }
        }
    }
}



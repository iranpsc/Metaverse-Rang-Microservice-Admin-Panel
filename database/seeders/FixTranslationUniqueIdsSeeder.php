<?php

namespace Database\Seeders;

use App\Models\Translations\Translation;
use Illuminate\Database\Seeder;

class FixTranslationUniqueIdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $translations = Translation::with('modals.tabs.fields')->get();
        foreach ($translations as $translation) {
            $uniqueId = 1;
            $fields = $translation->modals->flatMap(function ($modal) {
                return $modal->tabs->flatMap(function ($tab) {
                    return $tab->fields;
                });
            });

            foreach ($fields as $field) {
                $field->unique_id = $uniqueId;
                $field->save();
                $uniqueId++;
            }
        }
    }
}

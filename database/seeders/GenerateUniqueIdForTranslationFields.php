<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Translations\Translation;
use App\Models\Translations\Modal;
use App\Models\Translations\Tab;
use App\Models\Translations\Field;

class GenerateUniqueIdForTranslationFields extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $translations = Translation::all();
        foreach ($translations as $translation) {
            // Find all fields that belong to this translation's tabs (through modals and tabs)
            $fields = Field::whereIn('tab_id', function ($query) use ($translation) {
                $query->select('id')
                      ->from('tabs')
                      ->whereIn('modal_id', function ($query2) use ($translation) {
                          $query2->select('id')
                                 ->from('modals')
                                 ->where('translation_id', $translation->id);
                      });
            })->orderBy('id')->get();

            $uniqueId = 1;
            foreach ($fields as $field) {
                $field->unique_id = $uniqueId;
                $field->save();
                $uniqueId++;
            }
        }

    }
}

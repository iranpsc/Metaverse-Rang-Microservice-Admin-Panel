<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Translations\Field as TranslationField;

class TranslationFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TranslationField::chunkById(100, function ($fields) {
            foreach ($fields as $field) {
                $field->name = Str::slug(trim($field->name));
                $field->save();
            }
        });

        $fieldsWithDistinctNames = TranslationField::select('name')->distinct()->get();
        $uniqueId = 1;

        foreach ($fieldsWithDistinctNames as $field) {
            $fields = TranslationField::where('name', $field->name)->get();

            foreach ($fields as $field) {
                $field->unique_id = $uniqueId++;
                $field->save();
            }
        }
    }
}

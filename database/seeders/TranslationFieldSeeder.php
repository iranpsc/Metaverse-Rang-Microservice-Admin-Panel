<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Translations\Field as TranslationField;
use App\Models\Translations\Modal as TranslationModal;
use App\Models\Translations\Tab as TranslationTab;

class TranslationFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TranslationModal::chunkById(100, function ($modals) {
            foreach ($modals as $modal) {
                $modal->name = str_replace(' ', '-', Str::lower($modal->name));
                $modal->save();
            }
        });

        TranslationTab::chunkById(100, function ($tabs) {
            foreach ($tabs as $tab) {
                $tab->name = str_replace(' ', '-', Str::lower($tab->name));
                $tab->save();
            }
        });
    }
}

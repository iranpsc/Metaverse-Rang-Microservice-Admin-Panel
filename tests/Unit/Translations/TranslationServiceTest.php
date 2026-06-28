<?php

namespace Tests\Unit\Translations;

use App\Models\Translations\Field;
use App\Models\Translations\Translation;
use App\Services\Translations\TranslationService;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    private const TRANSLATION_MIGRATIONS = [
        'database/migrations/2023_07_09_113647_create_translations_table.php',
        'database/migrations/2023_07_11_111320_create_modals_table.php',
        'database/migrations/2023_07_11_111616_create_tabs_table.php',
        'database/migrations/2023_07_11_111647_create_fields_table.php',
        'database/migrations/2023_08_12_124110_add_direction_to_translations_table.php',
        'database/migrations/2023_10_11_080424_add_version_to_translations_table.php',
        'database/migrations/2024_12_06_141200_add_unique_id_to_fields_table.php',
        'database/migrations/2025_01_30_154700_change_unique_id_type_from_fields_table.php',
        'database/migrations/2025_04_13_090013_drop_name_from_fields_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        foreach (self::TRANSLATION_MIGRATIONS as $path) {
            $this->artisan('migrate', [
                '--database' => 'sqlite',
                '--path' => $path,
                '--force' => true,
            ]);
        }
    }

    public function test_create_field_only_syncs_to_same_modal_and_tab_across_languages(): void
    {
        $english = Translation::create([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'status' => true,
        ]);

        $german = Translation::create([
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'direction' => 'ltr',
            'status' => true,
        ]);

        $englishSafety = $english->modals()->create(['name' => 'safety']);
        $englishProfile = $english->modals()->create(['name' => 'profile']);
        $germanSafety = $german->modals()->create(['name' => 'safety']);
        $germanProfile = $german->modals()->create(['name' => 'profile']);

        $englishSafetyTab = $englishSafety->tabs()->create(['name' => 'security-and-privacy']);
        $englishProfileTab = $englishProfile->tabs()->create(['name' => 'security-and-privacy']);
        $germanSafetyTab = $germanSafety->tabs()->create(['name' => 'security-and-privacy']);
        $germanProfileTab = $germanProfile->tabs()->create(['name' => 'security-and-privacy']);

        $service = new TranslationService(new Filesystem());
        $field = $service->createField($englishSafetyTab, 'Two-factor authentication');

        $uniqueId = $field->unique_id;

        $this->assertDatabaseHas('fields', [
            'tab_id' => $germanSafetyTab->id,
            'unique_id' => $uniqueId,
            'translation' => null,
        ], 'sqlite');
        $this->assertDatabaseMissing('fields', [
            'tab_id' => $englishProfileTab->id,
            'unique_id' => $uniqueId,
        ], 'sqlite');
        $this->assertDatabaseMissing('fields', [
            'tab_id' => $germanProfileTab->id,
            'unique_id' => $uniqueId,
        ], 'sqlite');
        $this->assertSame(2, Field::where('unique_id', $uniqueId)->count());
    }
}

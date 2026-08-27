<?php

namespace Tests\Unit\Translations;

use App\Models\Translations\Field;
use App\Models\Translations\Translation;
use App\Services\Translations\TranslationService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

    /** @var list<string> */
    private array $createdLangFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        foreach (self::TRANSLATION_MIGRATIONS as $path) {
            $this->artisan('migrate', [
                '--database' => 'sqlite',
                '--path' => $path,
                '--force' => true,
            ]);
        }

        $this->createdLangFiles = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->createdLangFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
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

        $service = new TranslationService(new Filesystem);
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

    public function test_export_translation_writes_flat_json_sorted_by_unique_id_ascending(): void
    {
        $this->app['env'] = 'local';

        $translation = Translation::create([
            'code' => 'fr',
            'name' => 'French',
            'native_name' => 'Français',
            'direction' => 'ltr',
            'status' => true,
            'version' => 1,
        ]);

        $modalA = $translation->modals()->create(['name' => 'profile']);
        $modalB = $translation->modals()->create(['name' => 'safety']);
        $tabA = $modalA->tabs()->create(['name' => 'general']);
        $tabB = $modalB->tabs()->create(['name' => 'privacy']);

        $tabA->fields()->create(['unique_id' => 10, 'translation' => 'Ten']);
        $tabB->fields()->create(['unique_id' => 2, 'translation' => 'Two']);
        $tabA->fields()->create(['unique_id' => 5, 'translation' => null]);

        $service = new TranslationService(new Filesystem);
        $result = $service->exportTranslation($translation);

        $filePath = public_path('lang/fr.json');
        $this->createdLangFiles[] = $filePath;

        $this->assertInstanceOf(BinaryFileResponse::class, $result);
        $this->assertFileExists($filePath);

        $payload = json_decode((string) file_get_contents($filePath), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame([
            2 => 'Two',
            5 => null,
            10 => 'Ten',
        ], $payload);
        $this->assertSame([2, 5, 10], array_keys($payload));

        $raw = (string) file_get_contents($filePath);
        $this->assertStringContainsString("\n", $raw);
        $this->assertMatchesRegularExpression('/"2"\s*:\s*"Two".*"5"\s*:\s*null.*"10"\s*:\s*"Ten"/s', $raw);

        $translation->refresh();
        $this->assertSame(2, (int) $translation->version);
        $this->assertSame(sprintf('%s/lang/fr.json', config('app.url')), $translation->file_url);
    }

    public function test_available_languages_throws_when_definition_file_is_missing(): void
    {
        Cache::forget('translations.available_languages');

        $filesystem = \Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->once()->andReturn(false);

        $service = new TranslationService($filesystem);

        $this->expectException(FileNotFoundException::class);
        $service->availableLanguages();
    }

    public function test_available_languages_throws_validation_exception_for_invalid_json(): void
    {
        Cache::forget('translations.available_languages');

        $filesystem = \Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->once()->andReturn(true);
        $filesystem->shouldReceive('get')->once()->andReturn('{not-valid-json');

        $service = new TranslationService($filesystem);

        $this->expectException(ValidationException::class);
        $service->availableLanguages();
    }

    public function test_get_modals_tabs_and_fields_pagination_helpers(): void
    {
        $translation = Translation::create([
            'code' => 'it',
            'name' => 'Italian',
            'native_name' => 'Italiano',
            'direction' => 'ltr',
            'status' => true,
        ]);

        $modal = $translation->modals()->create(['name' => 'home']);
        $tab = $modal->tabs()->create(['name' => 'main']);
        $tab->fields()->create(['unique_id' => 1, 'translation' => 'Ciao']);
        $tab->fields()->create(['unique_id' => 2, 'translation' => null]);

        $service = new TranslationService(new Filesystem);

        $modals = $service->getModalsForTranslation($translation, 10);
        $tabs = $service->getTabsForModal($modal, 10);
        $fields = $service->getFieldsForTab($tab, 10);

        $this->assertSame(1, $modals->total());
        $this->assertSame(1, $tabs->total());
        $this->assertSame(2, $fields->total());
        $this->assertSame(1, (int) $modals->items()[0]->tabs_count);
        $this->assertSame(2, (int) $tabs->items()[0]->fields_count);
        $this->assertSame(1, (int) $tabs->items()[0]->translated_fields_count);
    }

    public function test_replicate_structure_skips_modals_that_already_exist_on_target(): void
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

        $englishHome = $english->modals()->create(['name' => 'home']);
        $englishHome->tabs()->create(['name' => 'main'])->fields()->create([
            'unique_id' => 11,
            'translation' => 'Hi',
        ]);
        $english->modals()->create(['name' => 'profile']);

        $german->modals()->create(['name' => 'home']);

        $service = new TranslationService(new Filesystem);
        $method = new ReflectionMethod(TranslationService::class, 'replicateStructureForTranslation');
        $method->setAccessible(true);
        $method->invoke($service, $german);

        $this->assertSame(1, $german->modals()->where('name', 'home')->count());
        $this->assertSame(1, $german->modals()->where('name', 'profile')->count());
        $this->assertSame(2, $german->modals()->count());
    }
}

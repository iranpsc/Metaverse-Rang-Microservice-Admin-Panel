<?php

namespace Tests\Feature\Translations;

use App\Models\Admin;
use App\Models\Translations\Translation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class TranslationApiTest extends TestCase
{
    use CreatesAuthApiSchema;

    private const INDEX_PATH = '/api/translations';

    private const LANGUAGES_PATH = '/api/translations/languages';

    /** @var list<string> */
    private array $createdLangFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthApiSchema();
        $this->createTranslationsTable();
        $this->createTranslationStructureTables();

        Cache::forget('translations.available_languages');
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

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_index_is_publicly_accessible_without_authentication(): void
    {
        $this->getJson(self::INDEX_PATH)->assertOk();
    }

    public function test_index_returns_only_active_translations(): void
    {
        $active = $this->createTranslation([
            'code' => 'en',
            'name' => 'English',
            'status' => true,
        ]);
        $this->createTranslation([
            'code' => 'de',
            'name' => 'German',
            'status' => false,
        ]);

        $response = $this->getJson(self::INDEX_PATH);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.code', 'en')
            ->assertJsonPath('data.0.status', true);
    }

    public function test_index_excludes_inactive_translations(): void
    {
        $this->createTranslation([
            'code' => 'de',
            'name' => 'German',
            'status' => false,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_index_returns_expected_json_structure_including_icon_and_modals_count(): void
    {
        $translation = $this->createTranslation([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'status' => true,
            'version' => 3,
            'file_url' => 'https://metarang.com/lang/en.json',
        ]);

        $modal = $translation->modals()->create(['name' => 'profile']);
        $modal->tabs()->create(['name' => 'general']);
        $translation->modals()->create(['name' => 'safety']);

        $response = $this->getJson(self::INDEX_PATH);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'code',
                        'name',
                        'native_name',
                        'direction',
                        'version',
                        'status',
                        'modals_count',
                        'icon',
                        'file_url',
                    ],
                ],
            ])
            ->assertJsonPath('data.0.id', $translation->id)
            ->assertJsonPath('data.0.code', 'en')
            ->assertJsonPath('data.0.name', 'English')
            ->assertJsonPath('data.0.native_name', 'English')
            ->assertJsonPath('data.0.direction', 'ltr')
            ->assertJsonPath('data.0.version', 3)
            ->assertJsonPath('data.0.status', true)
            ->assertJsonPath('data.0.modals_count', 2)
            ->assertJsonPath('data.0.file_url', 'https://metarang.com/lang/en.json')
            ->assertJsonPath('data.0.icon', asset('assets/images/flags/EN.svg'));
    }

    public function test_index_orders_translations_by_name_ascending(): void
    {
        $this->createTranslation([
            'code' => 'zu',
            'name' => 'Zulu',
            'status' => true,
        ]);
        $this->createTranslation([
            'code' => 'ar',
            'name' => 'Arabic',
            'status' => true,
        ]);
        $this->createTranslation([
            'code' => 'en',
            'name' => 'English',
            'status' => true,
        ]);

        $response = $this->getJson(self::INDEX_PATH);

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();

        $this->assertSame(['Arabic', 'English', 'Zulu'], $names);
    }

    // -------------------------------------------------------------------------
    // languages
    // -------------------------------------------------------------------------

    public function test_languages_requires_authentication(): void
    {
        $this->getJson(self::LANGUAGES_PATH)->assertUnauthorized();
    }

    public function test_admin_can_fetch_available_languages(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson(self::LANGUAGES_PATH);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Languages fetched successfully.')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'languages' => [
                        ['id', 'code', 'name', 'nativeName', 'dir'],
                    ],
                ],
                'message',
            ]);

        $languages = $response->json('data.languages');
        $this->assertNotEmpty($languages);
        $this->assertTrue(collect($languages)->contains(fn (array $language) => $language['code'] === 'de'));
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_requires_authentication(): void
    {
        $this->postJson(self::INDEX_PATH, ['code' => 'de'])->assertUnauthorized();
    }

    public function test_admin_can_create_translation_with_supported_code(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson(self::INDEX_PATH, [
            'code' => 'de',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'translation' => [
                        'code' => 'de',
                        'name' => 'German',
                        'native_name' => 'Deutsch',
                        'direction' => 'ltr',
                        'status' => true,
                        'modals_count' => 0,
                    ],
                ],
                'message' => 'Translation created successfully.',
            ]);

        $this->assertDatabaseHas('translations', [
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
        ], 'sqlite');
    }

    public function test_store_validates_missing_code(): void
    {
        $this->actingAsAdmin();

        $this->postJson(self::INDEX_PATH, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_store_rejects_unsupported_language_code(): void
    {
        $this->actingAsAdmin();

        $this->postJson(self::INDEX_PATH, ['code' => 'zz'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_store_rejects_duplicate_code(): void
    {
        $this->actingAsAdmin();

        $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $this->postJson(self::INDEX_PATH, ['code' => 'de'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_store_rejects_code_longer_than_ten_characters(): void
    {
        $this->actingAsAdmin();

        $this->postJson(self::INDEX_PATH, ['code' => 'toolongcode'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_store_replicates_modal_tab_field_structure_from_existing_translations(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation([
            'code' => 'en',
            'name' => 'English',
            'status' => true,
        ]);

        $modal = $english->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);
        $tab->fields()->create([
            'unique_id' => 7,
            'translation' => 'Hello',
        ]);
        $tab->fields()->create([
            'unique_id' => 8,
            'translation' => 'World',
        ]);

        $response = $this->postJson(self::INDEX_PATH, ['code' => 'de']);

        $response->assertCreated()
            ->assertJsonPath('data.translation.code', 'de')
            ->assertJsonPath('data.translation.modals_count', 1);

        $german = Translation::where('code', 'de')->firstOrFail();
        $germanModal = $german->modals()->where('name', 'profile')->firstOrFail();
        $germanTab = $germanModal->tabs()->where('name', 'general')->firstOrFail();

        $this->assertDatabaseHas('fields', [
            'tab_id' => $germanTab->id,
            'unique_id' => 7,
            'translation' => null,
        ], 'sqlite');
        $this->assertDatabaseHas('fields', [
            'tab_id' => $germanTab->id,
            'unique_id' => 8,
            'translation' => null,
        ], 'sqlite');
        $this->assertSame(2, $germanTab->fields()->count());
    }

    // -------------------------------------------------------------------------
    // show
    // -------------------------------------------------------------------------

    public function test_show_requires_authentication(): void
    {
        $translation = $this->createTranslation(['code' => 'en', 'name' => 'English']);

        $this->getJson($this->translationPath($translation))->assertUnauthorized();
    }

    public function test_admin_can_show_translation_with_modals_count(): void
    {
        $this->actingAsAdmin();

        $translation = $this->createTranslation([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'status' => true,
            'version' => 2,
        ]);
        $translation->modals()->create(['name' => 'profile']);
        $translation->modals()->create(['name' => 'safety']);

        $this->getJson($this->translationPath($translation))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'translation' => [
                        'id' => $translation->id,
                        'code' => 'en',
                        'name' => 'English',
                        'modals_count' => 2,
                        'status' => true,
                        'version' => 2,
                    ],
                ],
                'message' => 'Translation fetched successfully.',
            ]);
    }

    public function test_show_returns_not_found_for_missing_translation(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/translations/999999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_requires_authentication(): void
    {
        $translation = $this->createTranslation(['code' => 'en', 'name' => 'English']);

        $this->deleteJson($this->translationPath($translation))->assertUnauthorized();
    }

    public function test_admin_can_destroy_translation(): void
    {
        $this->actingAsAdmin();

        $translation = $this->createTranslation(['code' => 'en', 'name' => 'English']);

        $this->deleteJson($this->translationPath($translation))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => null,
                'message' => 'Translation deleted successfully.',
            ]);

        $this->assertDatabaseMissing('translations', [
            'id' => $translation->id,
        ], 'sqlite');
    }

    public function test_destroy_returns_not_found_for_missing_translation(): void
    {
        $this->actingAsAdmin();

        $this->deleteJson('/api/translations/999999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // toggleStatus
    // -------------------------------------------------------------------------

    public function test_toggle_status_requires_authentication(): void
    {
        $translation = $this->createTranslation(['code' => 'en', 'name' => 'English']);

        $this->patchJson($this->statusPath($translation))->assertUnauthorized();
    }

    public function test_admin_can_toggle_status_from_false_to_true(): void
    {
        $this->actingAsAdmin();

        $translation = $this->createTranslation([
            'code' => 'en',
            'name' => 'English',
            'status' => false,
        ]);

        $this->patchJson($this->statusPath($translation))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'translation' => [
                        'id' => $translation->id,
                        'status' => true,
                    ],
                ],
                'message' => 'Translation status updated.',
            ]);

        $this->assertTrue((bool) $translation->fresh()->status);
    }

    public function test_admin_can_toggle_status_from_true_to_false(): void
    {
        $this->actingAsAdmin();

        $translation = $this->createTranslation([
            'code' => 'en',
            'name' => 'English',
            'status' => true,
        ]);

        $this->patchJson($this->statusPath($translation))
            ->assertOk()
            ->assertJsonPath('data.translation.status', false);

        $this->assertFalse((bool) $translation->fresh()->status);
    }

    // -------------------------------------------------------------------------
    // export
    // -------------------------------------------------------------------------

    public function test_export_requires_authentication(): void
    {
        $translation = $this->createTranslation(['code' => 'fr', 'name' => 'French']);

        $this->postJson($this->exportPath($translation))->assertUnauthorized();
    }

    public function test_export_in_local_env_downloads_sorted_flat_json_and_updates_model(): void
    {
        $this->actingAsAdmin();
        $this->app['env'] = 'local';

        $translation = $this->createTranslation([
            'code' => 'fr',
            'name' => 'French',
            'version' => 1,
            'file_url' => null,
        ]);

        $this->seedTranslationFields($translation, [
            ['unique_id' => 10, 'translation' => 'Ten', 'modal' => 'a', 'tab' => 'one'],
            ['unique_id' => 2, 'translation' => 'Two', 'modal' => 'a', 'tab' => 'one'],
            ['unique_id' => 5, 'translation' => null, 'modal' => 'b', 'tab' => 'two'],
        ]);

        $response = $this->post($this->exportPath($translation));

        $response->assertOk();
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);

        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('fr.json', $disposition);

        $filePath = public_path('lang/fr.json');
        $this->trackLangFile($filePath);
        $this->assertFileExists($filePath);

        $payload = json_decode((string) file_get_contents($filePath), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame([
            2 => 'Two',
            5 => null,
            10 => 'Ten',
        ], $payload);
        $this->assertSame([2, 5, 10], array_keys($payload));

        $raw = (string) file_get_contents($filePath);
        $this->assertMatchesRegularExpression('/"2"\s*:\s*"Two".*"5"\s*:\s*null.*"10"\s*:\s*"Ten"/s', $raw);

        $translation->refresh();
        $this->assertSame(2, (int) $translation->version);
        $this->assertSame(sprintf('%s/lang/fr.json', config('app.url')), $translation->file_url);
    }

    public function test_export_includes_fields_from_multiple_modals_and_tabs(): void
    {
        $this->actingAsAdmin();
        $this->app['env'] = 'local';

        $translation = $this->createTranslation([
            'code' => 'af',
            'name' => 'Afrikaans',
            'version' => 0,
        ]);

        $this->seedTranslationFields($translation, [
            ['unique_id' => 1, 'translation' => 'Hello', 'modal' => 'profile', 'tab' => 'general'],
            ['unique_id' => 2, 'translation' => 'World', 'modal' => 'safety', 'tab' => 'privacy'],
            ['unique_id' => 3, 'translation' => 'Again', 'modal' => 'profile', 'tab' => 'advanced'],
        ]);

        $this->post($this->exportPath($translation))->assertOk();

        $filePath = public_path('lang/af.json');
        $this->trackLangFile($filePath);

        $payload = json_decode((string) file_get_contents($filePath), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame([
            1 => 'Hello',
            2 => 'World',
            3 => 'Again',
        ], $payload);
    }

    public function test_export_in_non_local_env_downloads_file_and_updates_model(): void
    {
        $this->actingAsAdmin();
        // Avoid "production" which enables phone-verification middleware.
        $this->app['env'] = 'staging';

        $translation = $this->createTranslation([
            'code' => 'sq',
            'name' => 'Albanian',
            'version' => 4,
        ]);

        $this->seedTranslationFields($translation, [
            ['unique_id' => 1, 'translation' => 'Pershendetje', 'modal' => 'home', 'tab' => 'main'],
        ]);

        $response = $this->post($this->exportPath($translation));

        $filePath = public_path('lang/sq.json');
        $this->trackLangFile($filePath);

        $response->assertOk();
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
        $this->assertFileExists($filePath);
        $this->assertSame(
            [1 => 'Pershendetje'],
            json_decode((string) file_get_contents($filePath), true, flags: JSON_THROW_ON_ERROR)
        );

        $translation->refresh();
        $this->assertSame(5, (int) $translation->version);
        $this->assertSame(sprintf('%s/lang/sq.json', config('app.url')), $translation->file_url);
    }

    public function test_export_uses_configured_app_url_for_file_url(): void
    {
        $this->actingAsAdmin();
        config(['app.url' => 'https://cdn.example.test']);

        $translation = $this->createTranslation([
            'code' => 'ak',
            'name' => 'Akan',
            'version' => 0,
            'file_url' => null,
        ]);

        $this->seedTranslationFields($translation, [
            ['unique_id' => 1, 'translation' => 'Hi', 'modal' => 'home', 'tab' => 'main'],
        ]);

        $response = $this->post($this->exportPath($translation));

        $filePath = public_path('lang/ak.json');
        $this->trackLangFile($filePath);

        $response->assertOk();
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);

        $translation->refresh();
        $this->assertSame(1, (int) $translation->version);
        $this->assertSame('https://cdn.example.test/lang/ak.json', $translation->file_url);
    }

    public function test_export_returns_not_found_for_missing_translation(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/translations/999999/export')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function translationPath(Translation $translation): string
    {
        return self::INDEX_PATH.'/'.$translation->id;
    }

    private function statusPath(Translation $translation): string
    {
        return $this->translationPath($translation).'/status';
    }

    private function exportPath(Translation $translation): string
    {
        return $this->translationPath($translation).'/export';
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createTranslation(array $attributes = []): Translation
    {
        return Translation::create(array_merge([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'status' => false,
            'version' => 0,
            'file_url' => null,
        ], $attributes));
    }

    /**
     * @param  list<array{unique_id: int, translation: ?string, modal: string, tab: string}>  $fields
     */
    private function seedTranslationFields(Translation $translation, array $fields): void
    {
        $modals = [];
        $tabs = [];

        foreach ($fields as $field) {
            $modalName = $field['modal'];
            $tabName = $field['tab'];

            if (! isset($modals[$modalName])) {
                $modals[$modalName] = $translation->modals()->create(['name' => $modalName]);
            }

            $tabKey = $modalName.'.'.$tabName;
            if (! isset($tabs[$tabKey])) {
                $tabs[$tabKey] = $modals[$modalName]->tabs()->create(['name' => $tabName]);
            }

            $tabs[$tabKey]->fields()->create([
                'unique_id' => $field['unique_id'],
                'translation' => $field['translation'],
            ]);
        }
    }

    private function trackLangFile(string $path): void
    {
        $this->createdLangFiles[] = $path;
    }

    private function actingAsAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => Str::uuid().'@example.com',
            'password' => bcrypt('password'),
        ]);

        Sanctum::actingAs($admin, abilities: ['*'], guard: 'admin');

        return $admin;
    }

    private function createTranslationsTable(): void
    {
        if (Schema::connection('sqlite')->hasTable('translations')) {
            return;
        }

        Schema::connection('sqlite')->create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->string('native_name')->nullable();
            $table->string('direction')->nullable();
            $table->boolean('status')->default(false);
            $table->unsignedTinyInteger('version')->default(0);
            $table->string('file_url')->nullable();
        });
    }

    private function createTranslationStructureTables(): void
    {
        $schema = Schema::connection('sqlite');

        if (! $schema->hasTable('modals')) {
            $schema->create('modals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('translation_id')->constrained()->cascadeOnDelete();
                $table->string('name');
            });
        }

        if (! $schema->hasTable('tabs')) {
            $schema->create('tabs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('modal_id')->constrained()->cascadeOnDelete();
                $table->string('name');
            });
        }

        if (! $schema->hasTable('fields')) {
            $schema->create('fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tab_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('unique_id')->nullable();
                $table->text('translation')->nullable();
            });
        }
    }
}

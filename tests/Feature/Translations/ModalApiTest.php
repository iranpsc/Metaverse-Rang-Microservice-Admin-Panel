<?php

namespace Tests\Feature\Translations;

use App\Models\Admin;
use App\Models\Translations\Modal;
use App\Models\Translations\Translation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class ModalApiTest extends TestCase
{
    use CreatesAuthApiSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthApiSchema();
        $this->createTranslationsTable();
        $this->createTranslationStructureTables();
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    public function test_index_requires_authentication(): void
    {
        $translation = $this->createTranslation();

        $this->getJson($this->modalsPath($translation))->assertUnauthorized();
    }

    public function test_store_requires_authentication(): void
    {
        $translation = $this->createTranslation();

        $this->postJson($this->modalsPath($translation), ['name' => 'profile'])->assertUnauthorized();
    }

    public function test_show_requires_authentication(): void
    {
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->getJson($this->modalPath($translation, $modal))->assertUnauthorized();
    }

    public function test_update_requires_authentication(): void
    {
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->patchJson($this->modalPath($translation, $modal), ['name' => 'settings'])->assertUnauthorized();
    }

    public function test_destroy_requires_authentication(): void
    {
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->deleteJson($this->modalPath($translation, $modal))->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_index_returns_empty_list_when_no_modals(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $response = $this->getJson($this->modalsPath($translation));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Modals fetched successfully.')
            ->assertJsonCount(0, 'data.modals')
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.current_page', 1);
    }

    public function test_index_returns_only_modals_for_given_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $englishModal = $english->modals()->create(['name' => 'profile']);
        $german->modals()->create(['name' => 'wallet']);

        $response = $this->getJson($this->modalsPath($english));

        $response->assertOk()
            ->assertJsonCount(1, 'data.modals')
            ->assertJsonPath('data.modals.0.id', $englishModal->id)
            ->assertJsonPath('data.modals.0.name', 'profile')
            ->assertJsonPath('data.modals.0.translation_id', $english->id);
    }

    public function test_index_orders_modals_by_name_ascending(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $translation->modals()->create(['name' => 'wallet']);
        $translation->modals()->create(['name' => 'profile']);
        $translation->modals()->create(['name' => 'settings']);

        $response = $this->getJson($this->modalsPath($translation));

        $response->assertOk()
            ->assertJsonCount(3, 'data.modals')
            ->assertJsonPath('data.modals.0.name', 'profile')
            ->assertJsonPath('data.modals.1.name', 'settings')
            ->assertJsonPath('data.modals.2.name', 'wallet');
    }

    public function test_index_includes_tabs_count(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $modal = $translation->modals()->create(['name' => 'profile']);
        $modal->tabs()->create(['name' => 'general']);
        $modal->tabs()->create(['name' => 'security']);

        $response = $this->getJson($this->modalsPath($translation));

        $response->assertOk()
            ->assertJsonPath('data.modals.0.tabs_count', 2);
    }

    public function test_index_respects_per_page_and_pagination_metadata(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        foreach (['a', 'b', 'c', 'd', 'e'] as $name) {
            $translation->modals()->create(['name' => $name]);
        }

        $response = $this->getJson($this->modalsPath($translation).'?per_page=2&page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data.modals')
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.from', 3)
            ->assertJsonPath('data.pagination.to', 4);
    }

    public function test_index_defaults_per_page_to_ten(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        for ($i = 1; $i <= 12; $i++) {
            $translation->modals()->create(['name' => 'modal_'.$i]);
        }

        $response = $this->getJson($this->modalsPath($translation));

        $response->assertOk()
            ->assertJsonCount(10, 'data.modals')
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonPath('data.pagination.last_page', 2);
    }

    public function test_index_returns_expected_json_structure_and_success_message(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $modal->tabs()->create(['name' => 'general']);

        $response = $this->getJson($this->modalsPath($translation));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Modals fetched successfully.')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'modals' => [
                        [
                            'id',
                            'translation_id',
                            'name',
                            'tabs_count',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'from',
                        'to',
                    ],
                ],
                'message',
            ]);
    }

    public function test_index_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/translations/99999/modals')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_modal_successfully(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $response = $this->postJson($this->modalsPath($translation), [
            'name' => 'profile',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Modal created successfully.')
            ->assertJsonPath('data.modal.name', 'profile')
            ->assertJsonPath('data.modal.translation_id', $translation->id)
            ->assertJsonPath('data.modal.tabs_count', 0)
            ->assertJsonStructure([
                'data' => [
                    'modal' => [
                        'id',
                        'translation_id',
                        'name',
                        'tabs_count',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('modals', [
            'translation_id' => $translation->id,
            'name' => 'profile',
        ], 'sqlite');
    }

    public function test_store_trims_whitespace_from_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $response = $this->postJson($this->modalsPath($translation), [
            'name' => '  profile  ',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.modal.name', 'profile');

        $this->assertDatabaseHas('modals', [
            'translation_id' => $translation->id,
            'name' => 'profile',
        ], 'sqlite');
    }

    public function test_store_syncs_modal_creation_across_all_translations(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $response = $this->postJson($this->modalsPath($english), [
            'name' => 'profile',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.modal.translation_id', $english->id)
            ->assertJsonPath('data.modal.name', 'profile');

        $this->assertDatabaseHas('modals', [
            'translation_id' => $english->id,
            'name' => 'profile',
        ], 'sqlite');

        $this->assertDatabaseHas('modals', [
            'translation_id' => $german->id,
            'name' => 'profile',
        ], 'sqlite');

        $this->assertSame(2, Modal::where('name', 'profile')->count());
    }

    public function test_store_validates_missing_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $this->postJson($this->modalsPath($translation), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_invalid_alpha_dash_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $this->postJson($this->modalsPath($translation), ['name' => 'my profile'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        $this->postJson($this->modalsPath($translation), ['name' => 'profile!'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_name_too_long(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $this->postJson($this->modalsPath($translation), [
            'name' => str_repeat('a', 256),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_duplicate_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $translation->modals()->create(['name' => 'profile']);

        $this->postJson($this->modalsPath($translation), ['name' => 'profile'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_rejects_duplicate_name_from_another_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $german->modals()->create(['name' => 'profile']);

        $this->postJson($this->modalsPath($english), ['name' => 'profile'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/translations/99999/modals', ['name' => 'profile'])
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // show
    // -------------------------------------------------------------------------

    public function test_show_returns_modal_with_tabs_count(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $modal->tabs()->create(['name' => 'general']);
        $modal->tabs()->create(['name' => 'security']);

        $response = $this->getJson($this->modalPath($translation, $modal));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Modal fetched successfully.')
            ->assertJsonPath('data.modal.id', $modal->id)
            ->assertJsonPath('data.modal.translation_id', $translation->id)
            ->assertJsonPath('data.modal.name', 'profile')
            ->assertJsonPath('data.modal.tabs_count', 2);
    }

    public function test_show_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $this->getJson($this->modalPath($english, $germanModal))->assertNotFound();
    }

    public function test_show_returns_404_when_modal_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $this->getJson($this->modalsPath($translation).'/99999')->assertNotFound();
    }

    public function test_show_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->getJson('/api/translations/99999/modals/'.$modal->id)->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_renames_modal_successfully(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $response = $this->patchJson($this->modalPath($translation, $modal), [
            'name' => 'settings',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Modal updated successfully.')
            ->assertJsonPath('data.modal.name', 'settings')
            ->assertJsonPath('data.modal.translation_id', $translation->id)
            ->assertJsonPath('data.modal.tabs_count', 0);

        $this->assertDatabaseHas('modals', [
            'id' => $modal->id,
            'name' => 'settings',
        ], 'sqlite');

        $this->assertDatabaseMissing('modals', [
            'id' => $modal->id,
            'name' => 'profile',
        ], 'sqlite');
    }

    public function test_update_syncs_rename_across_all_translations(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $englishModal = $english->modals()->create(['name' => 'profile']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $response = $this->patchJson($this->modalPath($english, $englishModal), [
            'name' => 'settings',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.modal.name', 'settings');

        $this->assertDatabaseHas('modals', [
            'id' => $englishModal->id,
            'name' => 'settings',
        ], 'sqlite');

        $this->assertDatabaseHas('modals', [
            'id' => $germanModal->id,
            'name' => 'settings',
        ], 'sqlite');
    }

    public function test_update_allows_keeping_the_same_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $response = $this->patchJson($this->modalPath($translation, $modal), [
            'name' => 'profile',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.modal.name', 'profile')
            ->assertJsonPath('data.modal.id', $modal->id);
    }

    public function test_update_trims_whitespace_from_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $response = $this->patchJson($this->modalPath($translation, $modal), [
            'name' => '  settings  ',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.modal.name', 'settings');

        $this->assertDatabaseHas('modals', [
            'id' => $modal->id,
            'name' => 'settings',
        ], 'sqlite');
    }

    public function test_update_validates_missing_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->patchJson($this->modalPath($translation, $modal), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_validates_name_too_long(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->patchJson($this->modalPath($translation, $modal), [
            'name' => str_repeat('a', 256),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_validates_duplicate_of_another_modal_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $translation->modals()->create(['name' => 'settings']);

        $this->patchJson($this->modalPath($translation, $modal), [
            'name' => 'settings',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $this->patchJson($this->modalPath($english, $germanModal), [
            'name' => 'settings',
        ])->assertNotFound();
    }

    public function test_update_returns_404_when_modal_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $this->patchJson($this->modalsPath($translation).'/99999', [
            'name' => 'settings',
        ])->assertNotFound();
    }

    public function test_update_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->patchJson('/api/translations/99999/modals/'.$modal->id, [
            'name' => 'settings',
        ])->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_modal_successfully(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $response = $this->deleteJson($this->modalPath($translation, $modal));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'Modal deleted successfully.');

        $this->assertDatabaseMissing('modals', [
            'id' => $modal->id,
        ], 'sqlite');
    }

    public function test_destroy_deletes_same_named_modals_across_all_translations(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $englishModal = $english->modals()->create(['name' => 'profile']);
        $germanModal = $german->modals()->create(['name' => 'profile']);
        $englishOther = $english->modals()->create(['name' => 'wallet']);

        $response = $this->deleteJson($this->modalPath($english, $englishModal));

        $response->assertOk()
            ->assertJsonPath('message', 'Modal deleted successfully.');

        $this->assertDatabaseMissing('modals', [
            'id' => $englishModal->id,
        ], 'sqlite');

        $this->assertDatabaseMissing('modals', [
            'id' => $germanModal->id,
        ], 'sqlite');

        $this->assertDatabaseHas('modals', [
            'id' => $englishOther->id,
            'name' => 'wallet',
        ], 'sqlite');
    }

    public function test_destroy_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $this->deleteJson($this->modalPath($english, $germanModal))->assertNotFound();

        $this->assertDatabaseHas('modals', [
            'id' => $germanModal->id,
            'name' => 'profile',
        ], 'sqlite');
    }

    public function test_destroy_returns_404_when_modal_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $this->deleteJson($this->modalsPath($translation).'/99999')->assertNotFound();
    }

    public function test_destroy_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->deleteJson('/api/translations/99999/modals/'.$modal->id)->assertNotFound();

        $this->assertDatabaseHas('modals', [
            'id' => $modal->id,
        ], 'sqlite');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function modalsPath(Translation $translation): string
    {
        return '/api/translations/'.$translation->id.'/modals';
    }

    private function modalPath(Translation $translation, Modal $modal): string
    {
        return $this->modalsPath($translation).'/'.$modal->id;
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

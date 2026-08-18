<?php

namespace Tests\Feature\Translations;

use App\Models\Admin;
use App\Models\Translations\Modal;
use App\Models\Translations\Tab;
use App\Models\Translations\Translation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class TabApiTest extends TestCase
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
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->getJson($this->tabsPath($translation, $modal))->assertUnauthorized();
    }

    public function test_store_requires_authentication(): void
    {
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->postJson($this->tabsPath($translation, $modal), ['name' => 'general'])->assertUnauthorized();
    }

    public function test_show_requires_authentication(): void
    {
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->getJson($this->tabPath($translation, $modal, $tab))->assertUnauthorized();
    }

    public function test_update_requires_authentication(): void
    {
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->patchJson($this->tabPath($translation, $modal, $tab), ['name' => 'security'])->assertUnauthorized();
    }

    public function test_destroy_requires_authentication(): void
    {
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->deleteJson($this->tabPath($translation, $modal, $tab))->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_index_returns_empty_list_when_no_tabs(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $response = $this->getJson($this->tabsPath($translation, $modal));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tabs fetched successfully.')
            ->assertJsonCount(0, 'data.tabs')
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.current_page', 1);
    }

    public function test_index_returns_only_tabs_for_given_modal(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $profile = $translation->modals()->create(['name' => 'profile']);
        $wallet = $translation->modals()->create(['name' => 'wallet']);

        $profileTab = $profile->tabs()->create(['name' => 'general']);
        $wallet->tabs()->create(['name' => 'overview']);

        $response = $this->getJson($this->tabsPath($translation, $profile));

        $response->assertOk()
            ->assertJsonCount(1, 'data.tabs')
            ->assertJsonPath('data.tabs.0.id', $profileTab->id)
            ->assertJsonPath('data.tabs.0.name', 'general')
            ->assertJsonPath('data.tabs.0.modal_id', $profile->id);
    }

    public function test_index_orders_tabs_by_name_ascending(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $modal->tabs()->create(['name' => 'wallet']);
        $modal->tabs()->create(['name' => 'general']);
        $modal->tabs()->create(['name' => 'security']);

        $response = $this->getJson($this->tabsPath($translation, $modal));

        $response->assertOk()
            ->assertJsonCount(3, 'data.tabs')
            ->assertJsonPath('data.tabs.0.name', 'general')
            ->assertJsonPath('data.tabs.1.name', 'security')
            ->assertJsonPath('data.tabs.2.name', 'wallet');
    }

    public function test_index_includes_field_counts_and_progress(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);
        $tab->fields()->create(['unique_id' => 2, 'translation' => null]);
        $tab->fields()->create(['unique_id' => 3, 'translation' => 'World']);

        $response = $this->getJson($this->tabsPath($translation, $modal));

        $response->assertOk()
            ->assertJsonPath('data.tabs.0.fields_count', 3)
            ->assertJsonPath('data.tabs.0.translated_fields_count', 2)
            ->assertJsonPath('data.tabs.0.progress', 67);
    }

    public function test_index_respects_per_page_and_pagination_metadata(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        foreach (['a', 'b', 'c', 'd', 'e'] as $name) {
            $modal->tabs()->create(['name' => $name]);
        }

        $response = $this->getJson($this->tabsPath($translation, $modal).'?per_page=2&page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data.tabs')
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
        $modal = $translation->modals()->create(['name' => 'profile']);

        for ($i = 1; $i <= 12; $i++) {
            $modal->tabs()->create(['name' => 'tab_'.$i]);
        }

        $response = $this->getJson($this->tabsPath($translation, $modal));

        $response->assertOk()
            ->assertJsonCount(10, 'data.tabs')
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

        $response = $this->getJson($this->tabsPath($translation, $modal));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tabs fetched successfully.')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tabs' => [
                        [
                            'id',
                            'modal_id',
                            'name',
                            'fields_count',
                            'translated_fields_count',
                            'progress',
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
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->getJson('/api/translations/99999/modals/'.$modal->id.'/tabs')->assertNotFound();
    }

    public function test_index_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $this->getJson($this->tabsPath($english, $germanModal))->assertNotFound();
    }

    public function test_index_returns_404_when_modal_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $this->getJson('/api/translations/'.$translation->id.'/modals/99999/tabs')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_tab_successfully(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $response = $this->postJson($this->tabsPath($translation, $modal), [
            'name' => 'general',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tab created successfully.')
            ->assertJsonPath('data.tab.name', 'general')
            ->assertJsonPath('data.tab.modal_id', $modal->id)
            ->assertJsonPath('data.tab.fields_count', 0)
            ->assertJsonPath('data.tab.translated_fields_count', 0)
            ->assertJsonPath('data.tab.progress', 0)
            ->assertJsonStructure([
                'data' => [
                    'tab' => [
                        'id',
                        'modal_id',
                        'name',
                        'fields_count',
                        'translated_fields_count',
                        'progress',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('tabs', [
            'modal_id' => $modal->id,
            'name' => 'general',
        ], 'sqlite');
    }

    public function test_store_trims_whitespace_from_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $response = $this->postJson($this->tabsPath($translation, $modal), [
            'name' => '  general  ',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tab.name', 'general');

        $this->assertDatabaseHas('tabs', [
            'modal_id' => $modal->id,
            'name' => 'general',
        ], 'sqlite');
    }

    public function test_store_syncs_tab_creation_across_all_translations(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $englishModal = $english->modals()->create(['name' => 'profile']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $response = $this->postJson($this->tabsPath($english, $englishModal), [
            'name' => 'general',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tab.modal_id', $englishModal->id)
            ->assertJsonPath('data.tab.name', 'general');

        $this->assertDatabaseHas('tabs', [
            'modal_id' => $englishModal->id,
            'name' => 'general',
        ], 'sqlite');

        $this->assertDatabaseHas('tabs', [
            'modal_id' => $germanModal->id,
            'name' => 'general',
        ], 'sqlite');

        $this->assertSame(2, Tab::where('name', 'general')->count());
    }

    public function test_store_validates_missing_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->postJson($this->tabsPath($translation, $modal), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_invalid_alpha_dash_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->postJson($this->tabsPath($translation, $modal), ['name' => 'my tab'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        $this->postJson($this->tabsPath($translation, $modal), ['name' => 'tab!'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_name_too_long(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->postJson($this->tabsPath($translation, $modal), [
            'name' => str_repeat('a', 256),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->postJson('/api/translations/99999/modals/'.$modal->id.'/tabs', ['name' => 'general'])
            ->assertNotFound();
    }

    public function test_store_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $this->postJson($this->tabsPath($english, $germanModal), ['name' => 'general'])
            ->assertNotFound();
    }

    public function test_store_returns_404_when_modal_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $this->postJson('/api/translations/'.$translation->id.'/modals/99999/tabs', ['name' => 'general'])
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // show
    // -------------------------------------------------------------------------

    public function test_show_returns_tab_with_field_counts_and_progress(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);
        $tab->fields()->create(['unique_id' => 2, 'translation' => null]);

        $response = $this->getJson($this->tabPath($translation, $modal, $tab));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tab fetched successfully.')
            ->assertJsonPath('data.tab.id', $tab->id)
            ->assertJsonPath('data.tab.modal_id', $modal->id)
            ->assertJsonPath('data.tab.name', 'general')
            ->assertJsonPath('data.tab.fields_count', 2)
            ->assertJsonPath('data.tab.translated_fields_count', 1)
            ->assertJsonPath('data.tab.progress', 50);
    }

    public function test_show_returns_404_when_tab_belongs_to_different_modal(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $profile = $translation->modals()->create(['name' => 'profile']);
        $wallet = $translation->modals()->create(['name' => 'wallet']);
        $walletTab = $wallet->tabs()->create(['name' => 'overview']);

        $this->getJson($this->tabPath($translation, $profile, $walletTab))->assertNotFound();
    }

    public function test_show_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);

        $this->getJson($this->tabPath($english, $germanModal, $germanTab))->assertNotFound();
    }

    public function test_show_returns_404_when_tab_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->getJson($this->tabsPath($translation, $modal).'/99999')->assertNotFound();
    }

    public function test_show_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->getJson('/api/translations/99999/modals/'.$modal->id.'/tabs/'.$tab->id)->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_renames_tab_successfully(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $response = $this->patchJson($this->tabPath($translation, $modal, $tab), [
            'name' => 'security',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tab updated successfully.')
            ->assertJsonPath('data.tab.name', 'security')
            ->assertJsonPath('data.tab.modal_id', $modal->id)
            ->assertJsonPath('data.tab.fields_count', 0);

        $this->assertDatabaseHas('tabs', [
            'id' => $tab->id,
            'name' => 'security',
        ], 'sqlite');

        $this->assertDatabaseMissing('tabs', [
            'id' => $tab->id,
            'name' => 'general',
        ], 'sqlite');
    }

    public function test_update_syncs_rename_across_all_translations(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $englishModal = $english->modals()->create(['name' => 'profile']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $englishTab = $englishModal->tabs()->create(['name' => 'general']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);

        $response = $this->patchJson($this->tabPath($english, $englishModal, $englishTab), [
            'name' => 'security',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.tab.name', 'security');

        $this->assertDatabaseHas('tabs', [
            'id' => $englishTab->id,
            'name' => 'security',
        ], 'sqlite');

        $this->assertDatabaseHas('tabs', [
            'id' => $germanTab->id,
            'name' => 'security',
        ], 'sqlite');
    }

    public function test_update_allows_keeping_the_same_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $response = $this->patchJson($this->tabPath($translation, $modal, $tab), [
            'name' => 'general',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.tab.name', 'general')
            ->assertJsonPath('data.tab.id', $tab->id);
    }

    public function test_update_trims_whitespace_from_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $response = $this->patchJson($this->tabPath($translation, $modal, $tab), [
            'name' => '  security  ',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.tab.name', 'security');

        $this->assertDatabaseHas('tabs', [
            'id' => $tab->id,
            'name' => 'security',
        ], 'sqlite');
    }

    public function test_update_validates_missing_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->patchJson($this->tabPath($translation, $modal, $tab), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_validates_name_too_long(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->patchJson($this->tabPath($translation, $modal, $tab), [
            'name' => str_repeat('a', 256),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_validates_duplicate_of_another_tab_name(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);
        $modal->tabs()->create(['name' => 'security']);

        $this->patchJson($this->tabPath($translation, $modal, $tab), [
            'name' => 'security',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_returns_404_when_tab_belongs_to_different_modal(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $profile = $translation->modals()->create(['name' => 'profile']);
        $wallet = $translation->modals()->create(['name' => 'wallet']);
        $walletTab = $wallet->tabs()->create(['name' => 'overview']);

        $this->patchJson($this->tabPath($translation, $profile, $walletTab), [
            'name' => 'security',
        ])->assertNotFound();

        $this->assertDatabaseHas('tabs', [
            'id' => $walletTab->id,
            'name' => 'overview',
        ], 'sqlite');
    }

    public function test_update_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);

        $this->patchJson($this->tabPath($english, $germanModal, $germanTab), [
            'name' => 'security',
        ])->assertNotFound();
    }

    public function test_update_returns_404_when_tab_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->patchJson($this->tabsPath($translation, $modal).'/99999', [
            'name' => 'security',
        ])->assertNotFound();
    }

    public function test_update_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->patchJson('/api/translations/99999/modals/'.$modal->id.'/tabs/'.$tab->id, [
            'name' => 'security',
        ])->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_tab_successfully(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $response = $this->deleteJson($this->tabPath($translation, $modal, $tab));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'Tab deleted successfully.');

        $this->assertDatabaseMissing('tabs', [
            'id' => $tab->id,
        ], 'sqlite');
    }

    public function test_destroy_deletes_same_named_tabs_across_all_translations(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $englishModal = $english->modals()->create(['name' => 'profile']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $englishTab = $englishModal->tabs()->create(['name' => 'general']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);
        $englishOther = $englishModal->tabs()->create(['name' => 'security']);

        $response = $this->deleteJson($this->tabPath($english, $englishModal, $englishTab));

        $response->assertOk()
            ->assertJsonPath('message', 'Tab deleted successfully.');

        $this->assertDatabaseMissing('tabs', [
            'id' => $englishTab->id,
        ], 'sqlite');

        $this->assertDatabaseMissing('tabs', [
            'id' => $germanTab->id,
        ], 'sqlite');

        $this->assertDatabaseHas('tabs', [
            'id' => $englishOther->id,
            'name' => 'security',
        ], 'sqlite');
    }

    public function test_destroy_returns_404_when_tab_belongs_to_different_modal(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $profile = $translation->modals()->create(['name' => 'profile']);
        $wallet = $translation->modals()->create(['name' => 'wallet']);
        $walletTab = $wallet->tabs()->create(['name' => 'overview']);

        $this->deleteJson($this->tabPath($translation, $profile, $walletTab))->assertNotFound();

        $this->assertDatabaseHas('tabs', [
            'id' => $walletTab->id,
            'name' => 'overview',
        ], 'sqlite');
    }

    public function test_destroy_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);

        $this->deleteJson($this->tabPath($english, $germanModal, $germanTab))->assertNotFound();

        $this->assertDatabaseHas('tabs', [
            'id' => $germanTab->id,
            'name' => 'general',
        ], 'sqlite');
    }

    public function test_destroy_returns_404_when_tab_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->deleteJson($this->tabsPath($translation, $modal).'/99999')->assertNotFound();
    }

    public function test_destroy_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->deleteJson('/api/translations/99999/modals/'.$modal->id.'/tabs/'.$tab->id)->assertNotFound();

        $this->assertDatabaseHas('tabs', [
            'id' => $tab->id,
        ], 'sqlite');
    }

    // -------------------------------------------------------------------------
    // Progress calculation edge cases
    // -------------------------------------------------------------------------

    public function test_progress_is_zero_when_tab_has_no_fields(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $response = $this->getJson($this->tabPath($translation, $modal, $tab));

        $response->assertOk()
            ->assertJsonPath('data.tab.fields_count', 0)
            ->assertJsonPath('data.tab.translated_fields_count', 0)
            ->assertJsonPath('data.tab.progress', 0);
    }

    public function test_progress_is_zero_when_fields_exist_but_none_translated(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $tab->fields()->create(['unique_id' => 1, 'translation' => null]);
        $tab->fields()->create(['unique_id' => 2, 'translation' => null]);

        $response = $this->getJson($this->tabPath($translation, $modal, $tab));

        $response->assertOk()
            ->assertJsonPath('data.tab.fields_count', 2)
            ->assertJsonPath('data.tab.translated_fields_count', 0)
            ->assertJsonPath('data.tab.progress', 0);
    }

    public function test_progress_is_100_when_all_fields_translated(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);
        $tab->fields()->create(['unique_id' => 2, 'translation' => 'World']);

        $response = $this->getJson($this->tabPath($translation, $modal, $tab));

        $response->assertOk()
            ->assertJsonPath('data.tab.fields_count', 2)
            ->assertJsonPath('data.tab.translated_fields_count', 2)
            ->assertJsonPath('data.tab.progress', 100);
    }

    public function test_progress_is_rounded_for_partial_translations(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);
        $tab->fields()->create(['unique_id' => 2, 'translation' => null]);
        $tab->fields()->create(['unique_id' => 3, 'translation' => null]);

        $response = $this->getJson($this->tabPath($translation, $modal, $tab));

        $response->assertOk()
            ->assertJsonPath('data.tab.fields_count', 3)
            ->assertJsonPath('data.tab.translated_fields_count', 1)
            ->assertJsonPath('data.tab.progress', 33);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function tabsPath(Translation $translation, Modal $modal): string
    {
        return '/api/translations/'.$translation->id.'/modals/'.$modal->id.'/tabs';
    }

    private function tabPath(Translation $translation, Modal $modal, Tab $tab): string
    {
        return $this->tabsPath($translation, $modal).'/'.$tab->id;
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

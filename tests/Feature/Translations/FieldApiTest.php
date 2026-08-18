<?php

namespace Tests\Feature\Translations;

use App\Models\Admin;
use App\Models\Translations\Field;
use App\Models\Translations\Modal;
use App\Models\Translations\Tab;
use App\Models\Translations\Translation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class FieldApiTest extends TestCase
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
        [$translation, $modal, $tab] = $this->createNestedResources();

        $this->getJson($this->fieldsPath($translation, $modal, $tab))->assertUnauthorized();
    }

    public function test_store_requires_authentication(): void
    {
        [$translation, $modal, $tab] = $this->createNestedResources();

        $this->postJson($this->fieldsPath($translation, $modal, $tab), [
            'value' => 'Hello',
        ])->assertUnauthorized();
    }

    public function test_update_requires_authentication(): void
    {
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $this->patchJson($this->fieldPath($translation, $modal, $tab, $field), [
            'translation' => 'Updated',
        ])->assertUnauthorized();
    }

    public function test_destroy_requires_authentication(): void
    {
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $this->deleteJson($this->fieldPath($translation, $modal, $tab, $field))->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_index_returns_empty_list_when_no_fields(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        $response = $this->getJson($this->fieldsPath($translation, $modal, $tab));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Fields fetched successfully.')
            ->assertJsonCount(0, 'data.fields')
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.current_page', 1);
    }

    public function test_index_returns_only_fields_for_given_tab(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $general = $modal->tabs()->create(['name' => 'general']);
        $security = $modal->tabs()->create(['name' => 'security']);

        $field = $general->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);
        $security->fields()->create(['unique_id' => 2, 'translation' => 'Other']);

        $response = $this->getJson($this->fieldsPath($translation, $modal, $general));

        $response->assertOk()
            ->assertJsonCount(1, 'data.fields')
            ->assertJsonPath('data.fields.0.id', $field->id)
            ->assertJsonPath('data.fields.0.tab_id', $general->id)
            ->assertJsonPath('data.fields.0.unique_id', 1)
            ->assertJsonPath('data.fields.0.translation', 'Hello');
    }

    public function test_index_orders_fields_by_unique_id_ascending(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        $tab->fields()->create(['unique_id' => 30, 'translation' => 'Thirty']);
        $tab->fields()->create(['unique_id' => 5, 'translation' => 'Five']);
        $tab->fields()->create(['unique_id' => 12, 'translation' => 'Twelve']);

        $response = $this->getJson($this->fieldsPath($translation, $modal, $tab));

        $response->assertOk()
            ->assertJsonCount(3, 'data.fields')
            ->assertJsonPath('data.fields.0.unique_id', 5)
            ->assertJsonPath('data.fields.1.unique_id', 12)
            ->assertJsonPath('data.fields.2.unique_id', 30);
    }

    public function test_index_respects_per_page_and_pagination_metadata(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        for ($i = 1; $i <= 5; $i++) {
            $tab->fields()->create(['unique_id' => $i, 'translation' => 'Field '.$i]);
        }

        $response = $this->getJson($this->fieldsPath($translation, $modal, $tab).'?per_page=2&page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data.fields')
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
        [$translation, $modal, $tab] = $this->createNestedResources();

        for ($i = 1; $i <= 12; $i++) {
            $tab->fields()->create(['unique_id' => $i, 'translation' => 'Field '.$i]);
        }

        $response = $this->getJson($this->fieldsPath($translation, $modal, $tab));

        $response->assertOk()
            ->assertJsonCount(10, 'data.fields')
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonPath('data.pagination.last_page', 2);
    }

    public function test_index_returns_expected_json_structure_and_success_message(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $response = $this->getJson($this->fieldsPath($translation, $modal, $tab));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Fields fetched successfully.')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'fields' => [
                        [
                            'id',
                            'tab_id',
                            'unique_id',
                            'name',
                            'translation',
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

    public function test_index_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);

        $this->getJson($this->fieldsPath($english, $germanModal, $germanTab))->assertNotFound();
    }

    public function test_index_returns_404_when_tab_belongs_to_different_modal(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $profile = $translation->modals()->create(['name' => 'profile']);
        $wallet = $translation->modals()->create(['name' => 'wallet']);
        $walletTab = $wallet->tabs()->create(['name' => 'overview']);

        $this->getJson($this->fieldsPath($translation, $profile, $walletTab))->assertNotFound();
    }

    public function test_index_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->getJson('/api/translations/99999/modals/'.$modal->id.'/tabs/'.$tab->id.'/fields')
            ->assertNotFound();
    }

    public function test_index_returns_404_when_modal_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->getJson('/api/translations/'.$translation->id.'/modals/99999/tabs/'.$tab->id.'/fields')
            ->assertNotFound();
    }

    public function test_index_returns_404_when_tab_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->getJson('/api/translations/'.$translation->id.'/modals/'.$modal->id.'/tabs/99999/fields')
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_field_with_value_and_assigns_unique_id(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        $response = $this->postJson($this->fieldsPath($translation, $modal, $tab), [
            'value' => 'Welcome message',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Field created successfully.')
            ->assertJsonPath('data.field.tab_id', $tab->id)
            ->assertJsonPath('data.field.unique_id', 1)
            ->assertJsonPath('data.field.translation', 'Welcome message');

        $this->assertDatabaseHas('fields', [
            'tab_id' => $tab->id,
            'unique_id' => 1,
            'translation' => 'Welcome message',
        ], 'sqlite');
    }

    public function test_store_returns_201_with_field_resource_payload(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        $response = $this->postJson($this->fieldsPath($translation, $modal, $tab), [
            'value' => 'Hello',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'field' => [
                        'id',
                        'tab_id',
                        'unique_id',
                        'name',
                        'translation',
                    ],
                ],
                'message',
            ])
            ->assertJsonPath('data.field.name', null);
    }

    public function test_store_replicates_field_onto_matching_tabs_in_other_translations(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $englishModal = $english->modals()->create(['name' => 'profile']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $englishTab = $englishModal->tabs()->create(['name' => 'general']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);

        $response = $this->postJson($this->fieldsPath($english, $englishModal, $englishTab), [
            'value' => 'Hello world',
        ]);

        $response->assertCreated();

        $uniqueId = $response->json('data.field.unique_id');

        $this->assertDatabaseHas('fields', [
            'tab_id' => $englishTab->id,
            'unique_id' => $uniqueId,
            'translation' => 'Hello world',
        ], 'sqlite');

        $this->assertDatabaseHas('fields', [
            'tab_id' => $germanTab->id,
            'unique_id' => $uniqueId,
            'translation' => null,
        ], 'sqlite');

        $this->assertSame(2, Field::where('unique_id', $uniqueId)->count());
    }

    public function test_store_does_not_replicate_onto_same_tab_name_under_differently_named_modal(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $englishSafety = $english->modals()->create(['name' => 'safety']);
        $englishProfile = $english->modals()->create(['name' => 'profile']);
        $germanSafety = $german->modals()->create(['name' => 'safety']);
        $germanProfile = $german->modals()->create(['name' => 'profile']);

        $englishSafetyTab = $englishSafety->tabs()->create(['name' => 'security-and-privacy']);
        $englishProfileTab = $englishProfile->tabs()->create(['name' => 'security-and-privacy']);
        $germanSafetyTab = $germanSafety->tabs()->create(['name' => 'security-and-privacy']);
        $germanProfileTab = $germanProfile->tabs()->create(['name' => 'security-and-privacy']);

        $response = $this->postJson($this->fieldsPath($english, $englishSafety, $englishSafetyTab), [
            'value' => 'Two-factor authentication',
        ]);

        $response->assertCreated();

        $uniqueId = $response->json('data.field.unique_id');

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

    public function test_store_increments_unique_id_based_on_global_max(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        $otherModal = $translation->modals()->create(['name' => 'wallet']);
        $otherTab = $otherModal->tabs()->create(['name' => 'overview']);
        $otherTab->fields()->create(['unique_id' => 42, 'translation' => 'Existing']);

        $response = $this->postJson($this->fieldsPath($translation, $modal, $tab), [
            'value' => 'Next field',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.field.unique_id', 43)
            ->assertJsonPath('data.field.translation', 'Next field');

        $this->assertDatabaseHas('fields', [
            'tab_id' => $tab->id,
            'unique_id' => 43,
            'translation' => 'Next field',
        ], 'sqlite');
    }

    public function test_store_assigns_unique_id_one_when_no_fields_exist(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        $response = $this->postJson($this->fieldsPath($translation, $modal, $tab), [
            'value' => 'First field',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.field.unique_id', 1);
    }

    public function test_store_validates_missing_value(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        $this->postJson($this->fieldsPath($translation, $modal, $tab), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['value']);
    }

    public function test_store_validates_value_too_long(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        $this->postJson($this->fieldsPath($translation, $modal, $tab), [
            'value' => str_repeat('a', 2001),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['value']);
    }

    public function test_store_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);

        $this->postJson($this->fieldsPath($english, $germanModal, $germanTab), [
            'value' => 'Hello',
        ])->assertNotFound();
    }

    public function test_store_returns_404_when_tab_belongs_to_different_modal(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $profile = $translation->modals()->create(['name' => 'profile']);
        $wallet = $translation->modals()->create(['name' => 'wallet']);
        $walletTab = $wallet->tabs()->create(['name' => 'overview']);

        $this->postJson($this->fieldsPath($translation, $profile, $walletTab), [
            'value' => 'Hello',
        ])->assertNotFound();
    }

    public function test_store_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->postJson('/api/translations/99999/modals/'.$modal->id.'/tabs/'.$tab->id.'/fields', [
            'value' => 'Hello',
        ])->assertNotFound();
    }

    public function test_store_returns_404_when_modal_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        $this->postJson('/api/translations/'.$translation->id.'/modals/99999/tabs/'.$tab->id.'/fields', [
            'value' => 'Hello',
        ])->assertNotFound();
    }

    public function test_store_returns_404_when_tab_does_not_exist(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $this->postJson('/api/translations/'.$translation->id.'/modals/'.$modal->id.'/tabs/99999/fields', [
            'value' => 'Hello',
        ])->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_updates_only_target_field_translation(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Old value']);

        $response = $this->patchJson($this->fieldPath($translation, $modal, $tab, $field), [
            'translation' => 'New value',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Field updated successfully.')
            ->assertJsonPath('data.field.id', $field->id)
            ->assertJsonPath('data.field.translation', 'New value')
            ->assertJsonPath('data.field.unique_id', 1);

        $this->assertDatabaseHas('fields', [
            'id' => $field->id,
            'translation' => 'New value',
        ], 'sqlite');
    }

    public function test_update_does_not_change_sibling_fields_with_same_unique_id(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $englishModal = $english->modals()->create(['name' => 'profile']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $englishTab = $englishModal->tabs()->create(['name' => 'general']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);

        $englishField = $englishTab->fields()->create(['unique_id' => 7, 'translation' => 'Hello']);
        $germanField = $germanTab->fields()->create(['unique_id' => 7, 'translation' => null]);

        $response = $this->patchJson($this->fieldPath($english, $englishModal, $englishTab, $englishField), [
            'translation' => 'Hello updated',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.field.translation', 'Hello updated');

        $this->assertDatabaseHas('fields', [
            'id' => $englishField->id,
            'translation' => 'Hello updated',
        ], 'sqlite');

        $this->assertDatabaseHas('fields', [
            'id' => $germanField->id,
            'translation' => null,
        ], 'sqlite');
    }

    public function test_update_returns_field_resource_with_new_translation(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 3, 'translation' => 'Before']);

        $response = $this->patchJson($this->fieldPath($translation, $modal, $tab, $field), [
            'translation' => 'After',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'field' => [
                        'id',
                        'tab_id',
                        'unique_id',
                        'name',
                        'translation',
                    ],
                ],
            ])
            ->assertJsonPath('data.field.tab_id', $tab->id)
            ->assertJsonPath('data.field.unique_id', 3)
            ->assertJsonPath('data.field.translation', 'After')
            ->assertJsonPath('data.field.name', null);
    }

    public function test_update_validates_missing_translation(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $this->patchJson($this->fieldPath($translation, $modal, $tab, $field), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['translation']);
    }

    public function test_update_validates_translation_too_long(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $this->patchJson($this->fieldPath($translation, $modal, $tab, $field), [
            'translation' => str_repeat('a', 5001),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['translation']);
    }

    public function test_update_returns_404_when_field_belongs_to_different_tab(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $general = $modal->tabs()->create(['name' => 'general']);
        $security = $modal->tabs()->create(['name' => 'security']);
        $securityField = $security->fields()->create(['unique_id' => 1, 'translation' => 'Secret']);

        $this->patchJson($this->fieldPath($translation, $modal, $general, $securityField), [
            'translation' => 'Hacked',
        ])->assertNotFound();

        $this->assertDatabaseHas('fields', [
            'id' => $securityField->id,
            'translation' => 'Secret',
        ], 'sqlite');
    }

    public function test_update_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);
        $germanField = $germanTab->fields()->create(['unique_id' => 1, 'translation' => 'Hallo']);

        $this->patchJson($this->fieldPath($english, $germanModal, $germanTab, $germanField), [
            'translation' => 'Updated',
        ])->assertNotFound();
    }

    public function test_update_returns_404_when_tab_belongs_to_different_modal(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $profile = $translation->modals()->create(['name' => 'profile']);
        $wallet = $translation->modals()->create(['name' => 'wallet']);
        $walletTab = $wallet->tabs()->create(['name' => 'overview']);
        $walletField = $walletTab->fields()->create(['unique_id' => 1, 'translation' => 'Balance']);

        $this->patchJson($this->fieldPath($translation, $profile, $walletTab, $walletField), [
            'translation' => 'Updated',
        ])->assertNotFound();
    }

    public function test_update_returns_404_when_field_does_not_exist(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        $this->patchJson($this->fieldsPath($translation, $modal, $tab).'/99999', [
            'translation' => 'Updated',
        ])->assertNotFound();
    }

    public function test_update_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $this->patchJson(
            '/api/translations/99999/modals/'.$modal->id.'/tabs/'.$tab->id.'/fields/'.$field->id,
            ['translation' => 'Updated']
        )->assertNotFound();
    }

    public function test_update_returns_404_when_modal_does_not_exist(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $this->patchJson(
            '/api/translations/'.$translation->id.'/modals/99999/tabs/'.$tab->id.'/fields/'.$field->id,
            ['translation' => 'Updated']
        )->assertNotFound();
    }

    public function test_update_returns_404_when_tab_does_not_exist(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $this->patchJson(
            '/api/translations/'.$translation->id.'/modals/'.$modal->id.'/tabs/99999/fields/'.$field->id,
            ['translation' => 'Updated']
        )->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_the_field(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $response = $this->deleteJson($this->fieldPath($translation, $modal, $tab, $field));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'Field deleted successfully.');

        $this->assertDatabaseMissing('fields', [
            'id' => $field->id,
        ], 'sqlite');
    }

    public function test_destroy_deletes_all_fields_sharing_same_unique_id(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);

        $englishModal = $english->modals()->create(['name' => 'profile']);
        $germanModal = $german->modals()->create(['name' => 'profile']);

        $englishTab = $englishModal->tabs()->create(['name' => 'general']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);

        $englishField = $englishTab->fields()->create(['unique_id' => 9, 'translation' => 'Hello']);
        $germanField = $germanTab->fields()->create(['unique_id' => 9, 'translation' => null]);

        $response = $this->deleteJson($this->fieldPath($english, $englishModal, $englishTab, $englishField));

        $response->assertOk()
            ->assertJsonPath('message', 'Field deleted successfully.');

        $this->assertDatabaseMissing('fields', [
            'id' => $englishField->id,
        ], 'sqlite');

        $this->assertDatabaseMissing('fields', [
            'id' => $germanField->id,
        ], 'sqlite');

        $this->assertSame(0, Field::where('unique_id', 9)->count());
    }

    public function test_destroy_does_not_delete_fields_with_different_unique_id(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        $target = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Delete me']);
        $kept = $tab->fields()->create(['unique_id' => 2, 'translation' => 'Keep me']);

        $response = $this->deleteJson($this->fieldPath($translation, $modal, $tab, $target));

        $response->assertOk();

        $this->assertDatabaseMissing('fields', [
            'id' => $target->id,
        ], 'sqlite');

        $this->assertDatabaseHas('fields', [
            'id' => $kept->id,
            'unique_id' => 2,
            'translation' => 'Keep me',
        ], 'sqlite');
    }

    public function test_destroy_returns_success_with_data_null(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $this->deleteJson($this->fieldPath($translation, $modal, $tab, $field))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'Field deleted successfully.');
    }

    public function test_destroy_returns_404_when_field_belongs_to_different_tab(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();
        $modal = $translation->modals()->create(['name' => 'profile']);

        $general = $modal->tabs()->create(['name' => 'general']);
        $security = $modal->tabs()->create(['name' => 'security']);
        $securityField = $security->fields()->create(['unique_id' => 1, 'translation' => 'Secret']);

        $this->deleteJson($this->fieldPath($translation, $modal, $general, $securityField))
            ->assertNotFound();

        $this->assertDatabaseHas('fields', [
            'id' => $securityField->id,
        ], 'sqlite');
    }

    public function test_destroy_returns_404_when_modal_belongs_to_different_translation(): void
    {
        $this->actingAsAdmin();

        $english = $this->createTranslation(['code' => 'en', 'name' => 'English']);
        $german = $this->createTranslation(['code' => 'de', 'name' => 'German']);
        $germanModal = $german->modals()->create(['name' => 'profile']);
        $germanTab = $germanModal->tabs()->create(['name' => 'general']);
        $germanField = $germanTab->fields()->create(['unique_id' => 1, 'translation' => 'Hallo']);

        $this->deleteJson($this->fieldPath($english, $germanModal, $germanTab, $germanField))
            ->assertNotFound();

        $this->assertDatabaseHas('fields', [
            'id' => $germanField->id,
        ], 'sqlite');
    }

    public function test_destroy_returns_404_when_tab_belongs_to_different_modal(): void
    {
        $this->actingAsAdmin();
        $translation = $this->createTranslation();

        $profile = $translation->modals()->create(['name' => 'profile']);
        $wallet = $translation->modals()->create(['name' => 'wallet']);
        $walletTab = $wallet->tabs()->create(['name' => 'overview']);
        $walletField = $walletTab->fields()->create(['unique_id' => 1, 'translation' => 'Balance']);

        $this->deleteJson($this->fieldPath($translation, $profile, $walletTab, $walletField))
            ->assertNotFound();

        $this->assertDatabaseHas('fields', [
            'id' => $walletField->id,
        ], 'sqlite');
    }

    public function test_destroy_returns_404_when_field_does_not_exist(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();

        $this->deleteJson($this->fieldsPath($translation, $modal, $tab).'/99999')->assertNotFound();
    }

    public function test_destroy_returns_404_when_translation_does_not_exist(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $this->deleteJson(
            '/api/translations/99999/modals/'.$modal->id.'/tabs/'.$tab->id.'/fields/'.$field->id
        )->assertNotFound();

        $this->assertDatabaseHas('fields', [
            'id' => $field->id,
        ], 'sqlite');
    }

    public function test_destroy_returns_404_when_modal_does_not_exist(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $this->deleteJson(
            '/api/translations/'.$translation->id.'/modals/99999/tabs/'.$tab->id.'/fields/'.$field->id
        )->assertNotFound();

        $this->assertDatabaseHas('fields', [
            'id' => $field->id,
        ], 'sqlite');
    }

    public function test_destroy_returns_404_when_tab_does_not_exist(): void
    {
        $this->actingAsAdmin();
        [$translation, $modal, $tab] = $this->createNestedResources();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $this->deleteJson(
            '/api/translations/'.$translation->id.'/modals/'.$modal->id.'/tabs/99999/fields/'.$field->id
        )->assertNotFound();

        $this->assertDatabaseHas('fields', [
            'id' => $field->id,
        ], 'sqlite');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @return array{0: Translation, 1: Modal, 2: Tab}
     */
    private function createNestedResources(array $translationAttributes = []): array
    {
        $translation = $this->createTranslation($translationAttributes);
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);

        return [$translation, $modal, $tab];
    }

    private function fieldsPath(Translation $translation, Modal $modal, Tab $tab): string
    {
        return '/api/translations/'.$translation->id.'/modals/'.$modal->id.'/tabs/'.$tab->id.'/fields';
    }

    private function fieldPath(Translation $translation, Modal $modal, Tab $tab, Field $field): string
    {
        return $this->fieldsPath($translation, $modal, $tab).'/'.$field->id;
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

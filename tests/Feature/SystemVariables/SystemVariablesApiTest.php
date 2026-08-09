<?php

namespace Tests\Feature\SystemVariables;

use App\Models\SystemVariable;
use Illuminate\Support\Str;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesSystemVariablesApiSchema;
use Tests\TestCase;

class SystemVariablesApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesSystemVariablesApiSchema;

    private const INDEX_PATH = '/api/system-variables';

    private const INDEX_SUCCESS_MESSAGE = 'System variables retrieved successfully.';

    private const STORE_SUCCESS_MESSAGE = 'متغیر سیستم با موفقیت ایجاد شد.';

    private const UPDATE_SUCCESS_MESSAGE = 'متغیر سیستم با موفقیت به روز شد.';

    private const DESTROY_SUCCESS_MESSAGE = 'متغیر سیستم با موفقیت حذف شد.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSystemVariablesApiSchema();
    }

    private function systemVariablePath(int|SystemVariable $variable): string
    {
        $id = $variable instanceof SystemVariable ? $variable->id : $variable;

        return self::INDEX_PATH.'/'.$id;
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_store_returns_unauthorized(): void
    {
        $this->postJson(self::INDEX_PATH, $this->validSystemVariableStorePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $this->putJson($this->systemVariablePath(1), $this->validSystemVariableStorePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $this->deleteJson($this->systemVariablePath(1))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $response = $this->postJson(self::INDEX_PATH, $this->validSystemVariableStorePayload([
            'slug' => 'super-admin-sv',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $id = $response->json('data.id');

        $this->putJson($this->systemVariablePath($id), [
            'name' => 'Updated SV',
            'slug' => 'super-admin-sv',
            'value' => 20,
            'note' => 'updated',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->deleteJson($this->systemVariablePath($id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $response = $this->postJson(self::INDEX_PATH, $this->validSystemVariableStorePayload([
            'slug' => 'regular-admin-sv',
        ]))->assertCreated();

        $id = $response->json('data.id');

        $this->putJson($this->systemVariablePath($id), [
            'name' => 'Regular Updated',
            'slug' => 'regular-admin-sv',
            'value' => 33,
        ])->assertOk();

        $this->deleteJson($this->systemVariablePath($id))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.variables', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure_with_change_logs(): void
    {
        $this->actingAsSuperAdmin();

        $variable = $this->createSystemVariable([
            'name' => 'Tax Rate',
            'slug' => 'tax-rate',
            'value' => 5.5,
        ]);
        $log = $this->createSystemVariableChangeLog($variable, [
            'changer_name' => 'Seed Admin',
            'previous_value' => 1,
            'current_value' => 5.5,
            'note' => 'seed',
        ]);

        $response = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'variables' => [
                        [
                            'id',
                            'name',
                            'slug',
                            'value',
                            'updated_at',
                            'created_at',
                            'change_logs' => [
                                [
                                    'id',
                                    'changer_name',
                                    'previous_value',
                                    'current_value',
                                    'note',
                                    'created_at',
                                    'updated_at',
                                ],
                            ],
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
            ]);

        $this->assertSame($variable->id, $response->json('data.variables.0.id'));
        $this->assertSame('Tax Rate', $response->json('data.variables.0.name'));
        $this->assertSame('tax-rate', $response->json('data.variables.0.slug'));
        $this->assertEquals(5.5, $response->json('data.variables.0.value'));
        $this->assertSame($log->id, $response->json('data.variables.0.change_logs.0.id'));
        $this->assertSame('Seed Admin', $response->json('data.variables.0.change_logs.0.changer_name'));
    }

    public function test_index_orders_by_created_at_descending(): void
    {
        $this->actingAsSuperAdmin();

        $older = $this->createSystemVariable(['name' => 'Older', 'slug' => 'older']);
        $older->forceFill(['created_at' => now()->subDay()])->save();

        $newer = $this->createSystemVariable(['name' => 'Newer', 'slug' => 'newer']);
        $newer->forceFill(['created_at' => now()])->save();

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $this->assertSame($newer->id, $response->json('data.variables.0.id'));
        $this->assertSame($older->id, $response->json('data.variables.1.id'));
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_default_per_page_is_ten(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 12; $i++) {
            $this->createSystemVariable([
                'name' => "Var {$i}",
                'slug' => "var-{$i}",
            ]);
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonCount(10, 'data.variables');
    }

    public function test_custom_per_page_and_page_are_respected(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->createSystemVariable([
                'name' => "Paged {$i}",
                'slug' => "paged-{$i}",
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonCount(2, 'data.variables');
    }

    public function test_per_page_above_one_hundred_is_capped(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 101; $i++) {
            $this->createSystemVariable([
                'name' => "Cap {$i}",
                'slug' => "cap-{$i}",
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=150')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 100)
            ->assertJsonCount(100, 'data.variables');
    }

    public function test_per_page_zero_or_negative_falls_back_to_ten(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 11; $i++) {
            $this->createSystemVariable([
                'name' => "Fallback {$i}",
                'slug' => "fallback-{$i}",
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=0')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonCount(10, 'data.variables');

        $this->getJson(self::INDEX_PATH.'?per_page=-5')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonCount(10, 'data.variables');

        $this->getJson(self::INDEX_PATH.'?per_page=invalid')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonCount(10, 'data.variables');
    }

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    public function test_search_filters_by_name(): void
    {
        $this->actingAsSuperAdmin();

        $this->createSystemVariable(['name' => 'Alpha Setting', 'slug' => 'alpha']);
        $this->createSystemVariable(['name' => 'Beta Setting', 'slug' => 'beta']);

        $this->getJson(self::INDEX_PATH.'?search=Alpha')
            ->assertOk()
            ->assertJsonCount(1, 'data.variables')
            ->assertJsonPath('data.variables.0.name', 'Alpha Setting');
    }

    public function test_search_filters_by_slug(): void
    {
        $this->actingAsSuperAdmin();

        $this->createSystemVariable(['name' => 'One', 'slug' => 'unique-slug-one']);
        $this->createSystemVariable(['name' => 'Two', 'slug' => 'other-slug']);

        $this->getJson(self::INDEX_PATH.'?search=unique-slug')
            ->assertOk()
            ->assertJsonCount(1, 'data.variables')
            ->assertJsonPath('data.variables.0.slug', 'unique-slug-one');
    }

    public function test_search_returns_empty_when_no_match(): void
    {
        $this->actingAsSuperAdmin();
        $this->createSystemVariable(['name' => 'Exists', 'slug' => 'exists']);

        $this->getJson(self::INDEX_PATH.'?search=no-such-thing')
            ->assertOk()
            ->assertJsonPath('data.variables', [])
            ->assertJsonPath('data.pagination.total', 0);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_system_variable(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validSystemVariableStorePayload([
            'name' => 'Max Withdraw',
            'slug' => 'max-withdraw',
            'value' => 250.5,
        ]);

        $this->postJson(self::INDEX_PATH, $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.name', 'Max Withdraw')
            ->assertJsonPath('data.slug', 'max-withdraw')
            ->assertJsonPath('data.value', 250.5)
            ->assertJsonPath('data.change_logs', [])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'value',
                    'updated_at',
                    'created_at',
                    'change_logs',
                ],
            ]);

        $this->assertDatabaseHas('system_variables', [
            'name' => 'Max Withdraw',
            'slug' => 'max-withdraw',
        ]);
    }

    public function test_store_rejects_missing_required_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'slug', 'value']);
    }

    public function test_store_rejects_duplicate_slug(): void
    {
        $this->actingAsSuperAdmin();
        $this->createSystemVariable(['slug' => 'taken-slug']);

        $this->postJson(self::INDEX_PATH, $this->validSystemVariableStorePayload([
            'slug' => 'taken-slug',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_store_rejects_non_numeric_value(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validSystemVariableStorePayload([
            'value' => 'not-a-number',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['value']);
    }

    public function test_store_rejects_oversized_name_and_slug(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validSystemVariableStorePayload([
            'name' => Str::random(256),
            'slug' => Str::random(256),
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'slug']);
    }

    public function test_store_allows_nullable_note(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validSystemVariableStorePayload([
            'slug' => 'no-note-slug',
            'note' => null,
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_fields_and_creates_change_log(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $variable = $this->createSystemVariable([
            'name' => 'Old Name',
            'slug' => 'old-slug',
            'value' => 10,
        ]);

        $this->putJson(
            $this->systemVariablePath($variable),
            $this->validSystemVariableUpdatePayload($variable, [
                'name' => 'New Name',
                'slug' => 'new-slug',
                'value' => 42.25,
                'note' => 'raised value',
            ])
        )
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.slug', 'new-slug')
            ->assertJsonPath('data.value', 42.25)
            ->assertJsonPath('data.change_logs.0.changer_name', $admin->name)
            ->assertJsonPath('data.change_logs.0.note', 'raised value');

        $this->assertDatabaseHas('system_variables', [
            'id' => $variable->id,
            'name' => 'New Name',
            'slug' => 'new-slug',
        ]);
        $this->assertDatabaseHas('variable_change_logs', [
            'changeable_type' => SystemVariable::class,
            'changeable_id' => $variable->id,
            'changer_name' => $admin->name,
            'note' => 'raised value',
        ]);
    }

    public function test_update_allows_keeping_same_slug(): void
    {
        $this->actingAsSuperAdmin();
        $variable = $this->createSystemVariable(['slug' => 'keep-me']);

        $this->putJson($this->systemVariablePath($variable), [
            'name' => 'Still Keep',
            'slug' => 'keep-me',
            'value' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.slug', 'keep-me');
    }

    public function test_update_rejects_slug_taken_by_another_record(): void
    {
        $this->actingAsSuperAdmin();
        $this->createSystemVariable(['slug' => 'taken']);
        $target = $this->createSystemVariable(['slug' => 'mine']);

        $this->putJson($this->systemVariablePath($target), [
            'name' => 'Conflict',
            'slug' => 'taken',
            'value' => 1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_update_rejects_missing_required_fields(): void
    {
        $this->actingAsSuperAdmin();
        $variable = $this->createSystemVariable();

        $this->putJson($this->systemVariablePath($variable), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'slug', 'value']);
    }

    public function test_update_returns_404_for_missing_system_variable(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->systemVariablePath(99999), [
            'name' => 'Missing',
            'slug' => 'missing',
            'value' => 1,
        ])->assertNotFound();
    }

    public function test_regular_admin_change_log_uses_authenticated_admin_name(): void
    {
        $admin = $this->actingAsRegularAdmin();
        $variable = $this->createSystemVariable(['value' => 3]);

        $this->putJson($this->systemVariablePath($variable), [
            'name' => $variable->name,
            'slug' => $variable->slug,
            'value' => 8,
            'note' => 'by regular',
        ])
            ->assertOk()
            ->assertJsonPath('data.change_logs.0.changer_name', $admin->name);

        $this->assertDatabaseHas('variable_change_logs', [
            'changeable_id' => $variable->id,
            'changer_name' => $admin->name,
        ]);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_system_variable_and_change_logs(): void
    {
        $this->actingAsSuperAdmin();
        $variable = $this->createSystemVariable();
        $log = $this->createSystemVariableChangeLog($variable);

        $this->deleteJson($this->systemVariablePath($variable))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('system_variables', ['id' => $variable->id]);
        $this->assertDatabaseMissing('variable_change_logs', ['id' => $log->id]);
    }

    public function test_destroy_returns_404_for_missing_system_variable(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson($this->systemVariablePath(99999))->assertNotFound();
    }
}

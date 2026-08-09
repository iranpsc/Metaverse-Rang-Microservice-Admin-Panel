<?php

namespace Tests\Feature\Variables;

use App\Models\Variable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesVariablesApiSchema;
use Tests\TestCase;

class VariablesApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesVariablesApiSchema;

    private const INDEX_PATH = '/api/variables';

    private const STORE_SUCCESS_MESSAGE = 'قیمت رنگ با موفقیت وارد شد';

    private const UPDATE_SUCCESS_MESSAGE = 'ارز با موفقیت بروزرسانی شد';

    private const DESTROY_SUCCESS_MESSAGE = 'ارز با موفقیت حذف شد';

    private const NOT_FOUND_MESSAGE = 'ارز یافت نشد';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpVariablesApiSchema();
    }

    private function variablePath(int|Variable $variable): string
    {
        $id = $variable instanceof Variable ? $variable->id : $variable;

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
        $this->post(self::INDEX_PATH, $this->validVariableStorePayload(), [
            'Accept' => 'application/json',
        ])->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $this->putJson($this->variablePath(1), $this->validVariableUpdatePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $this->deleteJson($this->variablePath(1))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->post(self::INDEX_PATH, $this->validVariableStorePayload([
            'asset' => 'blue',
        ]), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $variable = Variable::firstOrFail();

        $this->putJson($this->variablePath($variable), $this->validVariableUpdatePayload())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->deleteJson($this->variablePath($variable))
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

        $this->post(self::INDEX_PATH, $this->validVariableStorePayload([
            'asset' => 'yellow',
        ]), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $variable = Variable::firstOrFail();

        $this->putJson($this->variablePath($variable), $this->validVariableUpdatePayload([
            'price' => 3000,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->deleteJson($this->variablePath($variable))
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_empty_collection_when_no_variables_exist(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', [])
            ->assertJsonMissing(['message']);
    }

    public function test_index_returns_full_json_structure_with_relationships(): void
    {
        $this->actingAsSuperAdmin();

        $variable = $this->createVariableWithImage([
            'asset' => 'red',
            'price' => 1200,
            'note' => 'seed note',
        ]);
        $log = $this->createVariableChangeLog($variable, [
            'changer_name' => 'Seed Admin',
            'previous_value' => 1000,
            'current_value' => 1200,
            'note' => 'seed log',
        ]);

        $response = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    [
                        'id',
                        'asset',
                        'asset_title',
                        'price',
                        'note',
                        'updated_at',
                        'image_url',
                        'price_change_logs' => [
                            [
                                'id',
                                'changer_name',
                                'previous_value',
                                'current_value',
                                'note',
                                'created_at',
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertSame($variable->id, $response->json('data.0.id'));
        $this->assertSame('red', $response->json('data.0.asset'));
        $this->assertSame('قرمز', $response->json('data.0.asset_title'));
        $this->assertSame(1200, $response->json('data.0.price'));
        $this->assertSame('seed note', $response->json('data.0.note'));
        $this->assertNotNull($response->json('data.0.image_url'));
        $this->assertSame($log->id, $response->json('data.0.price_change_logs.0.id'));
        $this->assertSame('Seed Admin', $response->json('data.0.price_change_logs.0.changer_name'));
    }

    public function test_index_maps_asset_titles_correctly(): void
    {
        $this->actingAsSuperAdmin();

        $expected = [
            'red' => 'قرمز',
            'blue' => 'آبی',
            'yellow' => 'زرد',
            'psc' => 'psc',
            'irr' => 'ریال',
            'satisfaction' => 'رضایت',
            'effect' => 'حد تاثیر',
        ];

        foreach ($expected as $asset => $title) {
            $this->createVariable(['asset' => $asset, 'price' => 100]);
        }

        $response = $this->getJson(self::INDEX_PATH)->assertOk();
        $rows = collect($response->json('data'))->keyBy('asset');

        foreach ($expected as $asset => $title) {
            $this->assertSame($title, $rows[$asset]['asset_title']);
        }
    }

    public function test_index_returns_null_image_url_when_no_image(): void
    {
        $this->actingAsSuperAdmin();
        $this->createVariable(['asset' => 'irr', 'price' => 50]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.0.image_url', null)
            ->assertJsonPath('data.0.price_change_logs', []);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_variable_with_image(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::INDEX_PATH, $this->validVariableStorePayload([
            'asset' => 'psc',
            'price' => 777,
        ]), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.asset', 'psc')
            ->assertJsonPath('data.asset_title', 'psc')
            ->assertJsonPath('data.price', 777)
            ->assertJsonPath('data.price_change_logs', []);

        $this->assertNotNull($response->json('data.image_url'));
        $this->assertDatabaseHas('variables', [
            'asset' => 'psc',
            'price' => 777,
        ]);
        $this->assertDatabaseCount('images', 1);
        Storage::disk('public')->assertExists(
            str_replace(url('uploads').'/', '', $response->json('data.image_url'))
        );
    }

    public function test_store_rejects_missing_required_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price', 'asset', 'image']);
    }

    public function test_store_rejects_invalid_asset_enum(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validVariableStorePayload([
            'asset' => 'green',
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['asset']);
    }

    public function test_store_rejects_duplicate_asset(): void
    {
        $this->actingAsSuperAdmin();
        $this->createVariable(['asset' => 'red', 'price' => 10]);

        $this->post(self::INDEX_PATH, $this->validVariableStorePayload([
            'asset' => 'red',
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['asset']);
    }

    public function test_store_rejects_price_below_minimum(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validVariableStorePayload([
            'price' => 0,
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price']);
    }

    public function test_store_rejects_non_numeric_price(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validVariableStorePayload([
            'price' => 'abc',
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price']);
    }

    public function test_store_rejects_missing_image(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validVariableStorePayload();
        unset($payload['image']);

        $this->post(self::INDEX_PATH, $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    public function test_store_rejects_non_image_file(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validVariableStorePayload([
            'image' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    public function test_store_rejects_oversized_image(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validVariableStorePayload([
            'image' => UploadedFile::fake()->image('big.jpg')->size(1025),
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_price_note_and_creates_change_log(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $variable = $this->createVariable([
            'asset' => 'blue',
            'price' => 500,
            'note' => 'old',
        ]);

        $this->putJson($this->variablePath($variable), $this->validVariableUpdatePayload([
            'price' => 800,
            'note' => 'new note',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.price', 800)
            ->assertJsonPath('data.note', 'new note')
            ->assertJsonPath('data.asset_title', 'آبی')
            ->assertJsonPath('data.price_change_logs.0.changer_name', $admin->name)
            ->assertJsonPath('data.price_change_logs.0.previous_value', '500')
            ->assertJsonPath('data.price_change_logs.0.current_value', '800')
            ->assertJsonPath('data.price_change_logs.0.note', 'new note');

        $this->assertDatabaseHas('variables', [
            'id' => $variable->id,
            'price' => 800,
            'note' => 'new note',
        ]);
        $this->assertDatabaseHas('variable_change_logs', [
            'changeable_type' => Variable::class,
            'changeable_id' => $variable->id,
            'changer_name' => $admin->name,
            'previous_value' => 500,
            'current_value' => 800,
            'note' => 'new note',
        ]);
    }

    public function test_update_replaces_image_when_uploaded(): void
    {
        $this->actingAsSuperAdmin();
        $variable = $this->createVariableWithImage(['asset' => 'yellow', 'price' => 100]);
        $oldImageId = $variable->image->id;

        $response = $this->put($this->variablePath($variable), $this->validVariableUpdatePayload([
            'price' => 110,
            'image' => UploadedFile::fake()->image('replacement.png'),
        ]), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull($response->json('data.image_url'));
        $this->assertDatabaseMissing('images', ['id' => $oldImageId]);
        $this->assertDatabaseHas('images', [
            'imageable_type' => Variable::class,
            'imageable_id' => $variable->id,
        ]);
    }

    public function test_update_allows_nullable_note_and_image(): void
    {
        $this->actingAsSuperAdmin();
        $variable = $this->createVariable(['asset' => 'effect', 'price' => 10]);

        $this->putJson($this->variablePath($variable), [
            'price' => 20,
        ])
            ->assertOk()
            ->assertJsonPath('data.price', 20)
            ->assertJsonPath('data.note', null);
    }

    public function test_update_returns_404_for_missing_variable(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->variablePath(99999), $this->validVariableUpdatePayload())
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::NOT_FOUND_MESSAGE);
    }

    public function test_update_rejects_missing_price(): void
    {
        $this->actingAsSuperAdmin();
        $variable = $this->createVariable(['asset' => 'satisfaction', 'price' => 5]);

        $this->putJson($this->variablePath($variable), ['note' => 'only note'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price']);
    }

    public function test_update_rejects_price_below_minimum(): void
    {
        $this->actingAsSuperAdmin();
        $variable = $this->createVariable(['asset' => 'irr', 'price' => 5]);

        $this->putJson($this->variablePath($variable), ['price' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price']);
    }

    public function test_update_rejects_invalid_image(): void
    {
        $this->actingAsSuperAdmin();
        $variable = $this->createVariable(['asset' => 'blue', 'price' => 5]);

        $this->put($this->variablePath($variable), [
            'price' => 6,
            'image' => UploadedFile::fake()->create('file.txt', 10, 'text/plain'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_variable_image_and_change_logs(): void
    {
        $this->actingAsSuperAdmin();
        $variable = $this->createVariableWithImage(['asset' => 'red', 'price' => 100]);
        $log = $this->createVariableChangeLog($variable);
        $imageId = $variable->image->id;

        $this->deleteJson($this->variablePath($variable))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('variables', ['id' => $variable->id]);
        $this->assertDatabaseMissing('images', ['id' => $imageId]);
        $this->assertDatabaseMissing('variable_change_logs', ['id' => $log->id]);
    }

    public function test_destroy_returns_404_for_missing_variable(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson($this->variablePath(99999))
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::NOT_FOUND_MESSAGE);
    }

    public function test_regular_admin_change_log_uses_authenticated_admin_name(): void
    {
        $admin = $this->actingAsRegularAdmin();
        $variable = $this->createVariable(['asset' => 'psc', 'price' => 40]);

        $this->putJson($this->variablePath($variable), [
            'price' => 55,
            'note' => 'by regular',
        ])
            ->assertOk()
            ->assertJsonPath('data.price_change_logs.0.changer_name', $admin->name);

        $this->assertDatabaseHas('variable_change_logs', [
            'changeable_id' => $variable->id,
            'changer_name' => $admin->name,
        ]);
    }

    // -------------------------------------------------------------------------
    // Error paths
    // -------------------------------------------------------------------------

    public function test_index_returns_500_when_query_fails(): void
    {
        $this->actingAsSuperAdmin();

        Schema::drop('variables');

        $this->getJson(self::INDEX_PATH)
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بارگذاری اطلاعات');
    }

    public function test_store_returns_500_when_create_fails(): void
    {
        $this->actingAsSuperAdmin();
        Storage::fake('public');

        Variable::creating(function () {
            throw new \RuntimeException('forced variable create failure');
        });

        $this->post(self::INDEX_PATH, [
            'asset' => 'yellow',
            'price' => 12,
            'image' => UploadedFile::fake()->image('yellow.png'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در ثبت اطلاعات');
    }

    public function test_update_returns_500_when_update_fails(): void
    {
        $this->actingAsSuperAdmin();

        $variable = $this->createVariable(['asset' => 'blue', 'price' => 10]);

        Variable::updating(function () {
            throw new \RuntimeException('forced variable update failure');
        });

        $this->putJson($this->variablePath($variable), [
            'price' => 20,
            'note' => 'fail',
        ])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بروزرسانی اطلاعات');
    }

    public function test_destroy_returns_500_when_delete_fails(): void
    {
        $this->actingAsSuperAdmin();

        $variable = $this->createVariable(['asset' => 'irr', 'price' => 1]);

        Variable::deleting(function () {
            throw new \RuntimeException('forced variable delete failure');
        });

        $this->deleteJson($this->variablePath($variable))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در حذف ارز');
    }
}

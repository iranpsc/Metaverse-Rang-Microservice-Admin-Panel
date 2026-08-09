<?php

namespace Tests\Feature\Options;

use App\Models\Option;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesOptionsApiSchema;
use Tests\TestCase;

class OptionsApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesOptionsApiSchema;

    private const INDEX_PATH = '/api/options';

    private const VARIABLES_PATH = '/api/options/variables';

    private const STORE_SUCCESS_MESSAGE = 'پکیج رنگ وارد شد';

    private const UPDATE_SUCCESS_MESSAGE = 'بسته بروزرسانی شد';

    private const DESTROY_SUCCESS_MESSAGE = 'پکیج با موفقیت حذف شد';

    private const NOT_FOUND_MESSAGE = 'پکیج یافت نشد';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpOptionsApiSchema();
    }

    private function optionPath(int|Option $option): string
    {
        $id = $option instanceof Option ? $option->id : $option;

        return self::INDEX_PATH.'/'.$id;
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_get_variables_returns_unauthorized(): void
    {
        $this->getJson(self::VARIABLES_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_store_returns_unauthorized(): void
    {
        $this->postJson(self::INDEX_PATH, $this->validOptionStorePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $this->putJson($this->optionPath(1), $this->validOptionUpdatePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $this->deleteJson($this->optionPath(1))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson(self::VARIABLES_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $response = $this->postJson(self::INDEX_PATH, $this->validOptionStorePayload([
            'code' => 'SUPER-1',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $id = $response->json('data.id');

        $this->putJson($this->optionPath($id), $this->validOptionUpdatePayload([
            'code' => 'SUPER-1-UPD',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->deleteJson($this->optionPath($id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)->assertOk()->assertJsonPath('success', true);
        $this->getJson(self::VARIABLES_PATH)->assertOk()->assertJsonPath('success', true);

        $response = $this->postJson(self::INDEX_PATH, $this->validOptionStorePayload([
            'code' => 'REG-1',
        ]))->assertOk();

        $id = $response->json('data.id');

        $this->putJson($this->optionPath($id), $this->validOptionUpdatePayload([
            'code' => 'REG-1-UPD',
        ]))->assertOk();

        $this->deleteJson($this->optionPath($id))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_empty_options_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.options', [])
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure_with_package_price_and_relations(): void
    {
        $this->actingAsSuperAdmin();

        $this->createVariable(['asset' => 'red', 'price' => 100]);
        $option = $this->createOptionWithImage([
            'asset' => 'red',
            'amount' => 5,
            'code' => 'RED-5',
            'note' => 'pack note',
        ]);
        $log = $this->createOptionChangeLog($option, [
            'changer_name' => 'Seed Admin',
            'previous_value' => 3,
            'current_value' => 5,
            'note' => 'seed log',
        ]);

        $response = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'options' => [
                        [
                            'id',
                            'code',
                            'asset',
                            'asset_title',
                            'amount',
                            'package_price',
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
                    'pagination' => [
                        'total',
                        'per_page',
                        'current_page',
                        'last_page',
                        'from',
                        'to',
                    ],
                ],
            ]);

        $this->assertSame($option->id, $response->json('data.options.0.id'));
        $this->assertSame('RED-5', $response->json('data.options.0.code'));
        $this->assertSame('red', $response->json('data.options.0.asset'));
        $this->assertSame('قرمز', $response->json('data.options.0.asset_title'));
        $this->assertSame(5, $response->json('data.options.0.amount'));
        $this->assertSame(500, $response->json('data.options.0.package_price'));
        $this->assertNotNull($response->json('data.options.0.image_url'));
        $this->assertSame($log->id, $response->json('data.options.0.price_change_logs.0.id'));
    }

    public function test_index_package_price_is_zero_when_no_variable_rate_exists(): void
    {
        $this->actingAsSuperAdmin();
        $this->createOption(['asset' => 'blue', 'amount' => 10, 'code' => 'NO-RATE']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.options.0.package_price', 0);
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
        ];

        foreach ($expected as $asset => $title) {
            $this->createOption([
                'asset' => $asset,
                'amount' => 1,
                'code' => 'T-'.$asset,
            ]);
        }

        $response = $this->getJson(self::INDEX_PATH)->assertOk();
        $rows = collect($response->json('data.options'))->keyBy('asset');

        foreach ($expected as $asset => $title) {
            $this->assertSame($title, $rows[$asset]['asset_title']);
        }
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_default_per_page_is_ten(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 12; $i++) {
            $this->createOption(['code' => "PAGE-{$i}", 'amount' => $i]);
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonCount(10, 'data.options');
    }

    public function test_custom_per_page_and_page_are_respected(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->createOption(['code' => "CUST-{$i}", 'amount' => $i]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonCount(2, 'data.options');
    }

    // -------------------------------------------------------------------------
    // Get variables
    // -------------------------------------------------------------------------

    public function test_get_variables_returns_asset_and_asset_title_list(): void
    {
        $this->actingAsSuperAdmin();

        $this->createVariable(['asset' => 'red', 'price' => 10]);
        $this->createVariable(['asset' => 'satisfaction', 'price' => 20]);

        $response = $this->getJson(self::VARIABLES_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    [
                        'asset',
                        'asset_title',
                    ],
                ],
            ]);

        $rows = collect($response->json('data'))->keyBy('asset');
        $this->assertSame('قرمز', $rows['red']['asset_title']);
        $this->assertSame('رضایت', $rows['satisfaction']['asset_title']);
    }

    public function test_get_variables_returns_empty_list_when_none_exist(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::VARIABLES_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', []);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_option_without_image(): void
    {
        $this->actingAsSuperAdmin();
        $this->createVariable(['asset' => 'yellow', 'price' => 50]);

        $this->postJson(self::INDEX_PATH, $this->validOptionStorePayload([
            'asset' => 'yellow',
            'amount' => 4,
            'code' => 'YEL-4',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.asset', 'yellow')
            ->assertJsonPath('data.asset_title', 'زرد')
            ->assertJsonPath('data.amount', 4)
            ->assertJsonPath('data.code', 'YEL-4')
            ->assertJsonPath('data.package_price', 200)
            ->assertJsonPath('data.image_url', null)
            ->assertJsonPath('data.price_change_logs', []);

        $this->assertDatabaseHas('options', [
            'asset' => 'yellow',
            'amount' => 4,
            'code' => 'YEL-4',
        ]);
    }

    public function test_store_creates_option_with_image(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::INDEX_PATH, $this->validOptionStorePayloadWithImage([
            'code' => 'IMG-1',
        ]), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'IMG-1');

        $this->assertNotNull($response->json('data.image_url'));
        $this->assertDatabaseCount('images', 1);
        Storage::disk('public')->assertExists(
            str_replace(url('uploads').'/', '', $response->json('data.image_url'))
        );
    }

    public function test_store_rejects_missing_required_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount', 'asset', 'code']);
    }

    public function test_store_rejects_invalid_asset_enum(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validOptionStorePayload([
            'asset' => 'satisfaction',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['asset']);
    }

    public function test_store_rejects_duplicate_code(): void
    {
        $this->actingAsSuperAdmin();
        $this->createOption(['code' => 'DUP-CODE']);

        $this->postJson(self::INDEX_PATH, $this->validOptionStorePayload([
            'code' => 'DUP-CODE',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_store_rejects_amount_below_minimum(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validOptionStorePayload([
            'amount' => 0,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_store_rejects_non_integer_amount(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validOptionStorePayload([
            'amount' => 1.5,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_store_rejects_invalid_image_mime(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validOptionStorePayload([
            'code' => 'BAD-IMG',
            'image' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    public function test_store_rejects_oversized_image(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validOptionStorePayload([
            'code' => 'BIG-IMG',
            'image' => UploadedFile::fake()->image('big.jpg')->size(2049),
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_fields_and_creates_change_log(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $this->createVariable(['asset' => 'blue', 'price' => 20]);
        $option = $this->createOption([
            'asset' => 'red',
            'amount' => 2,
            'code' => 'OLD-CODE',
            'note' => 'old',
        ]);

        $this->putJson($this->optionPath($option), $this->validOptionUpdatePayload([
            'asset' => 'blue',
            'amount' => 7,
            'code' => 'NEW-CODE',
            'note' => 'updated pack',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.asset', 'blue')
            ->assertJsonPath('data.asset_title', 'آبی')
            ->assertJsonPath('data.amount', 7)
            ->assertJsonPath('data.code', 'NEW-CODE')
            ->assertJsonPath('data.note', 'updated pack')
            ->assertJsonPath('data.package_price', 140)
            ->assertJsonPath('data.price_change_logs.0.changer_name', $admin->name)
            ->assertJsonPath('data.price_change_logs.0.previous_value', '2')
            ->assertJsonPath('data.price_change_logs.0.current_value', '7')
            ->assertJsonPath('data.price_change_logs.0.note', 'updated pack');

        $this->assertDatabaseHas('options', [
            'id' => $option->id,
            'asset' => 'blue',
            'amount' => 7,
            'code' => 'NEW-CODE',
            'note' => 'updated pack',
        ]);
        $this->assertDatabaseHas('variable_change_logs', [
            'changeable_type' => Option::class,
            'changeable_id' => $option->id,
            'changer_name' => $admin->name,
            'previous_value' => 2,
            'current_value' => 7,
            'note' => 'updated pack',
        ]);
    }

    public function test_update_creates_or_replaces_image(): void
    {
        $this->actingAsSuperAdmin();
        $option = $this->createOption(['code' => 'IMG-UPD', 'amount' => 1]);

        $response = $this->put($this->optionPath($option), $this->validOptionUpdatePayload([
            'code' => 'IMG-UPD',
            'amount' => 2,
            'image' => UploadedFile::fake()->image('pack.png'),
        ]), ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertNotNull($response->json('data.image_url'));
        $this->assertDatabaseHas('images', [
            'imageable_type' => Option::class,
            'imageable_id' => $option->id,
        ]);

        $oldUrl = $response->json('data.image_url');

        $response = $this->put($this->optionPath($option), $this->validOptionUpdatePayload([
            'code' => 'IMG-UPD',
            'amount' => 3,
            'image' => UploadedFile::fake()->image('pack2.png'),
        ]), ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertNotSame($oldUrl, $response->json('data.image_url'));
        $this->assertDatabaseCount('images', 1);
    }

    public function test_update_returns_404_for_missing_option(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->optionPath(99999), $this->validOptionUpdatePayload())
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::NOT_FOUND_MESSAGE);
    }

    public function test_update_rejects_missing_required_fields(): void
    {
        $this->actingAsSuperAdmin();
        $option = $this->createOption(['code' => 'VAL']);

        $this->putJson($this->optionPath($option), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount', 'code', 'asset', 'note']);
    }

    public function test_update_rejects_invalid_asset_and_amount(): void
    {
        $this->actingAsSuperAdmin();
        $option = $this->createOption(['code' => 'BAD-UPD']);

        $this->putJson($this->optionPath($option), [
            'asset' => 'effect',
            'amount' => 0,
            'code' => 'BAD-UPD',
            'note' => 'x',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['asset', 'amount']);
    }

    public function test_regular_admin_change_log_uses_authenticated_admin_name(): void
    {
        $admin = $this->actingAsRegularAdmin();
        $option = $this->createOption(['code' => 'REG-LOG', 'amount' => 1]);

        $this->putJson($this->optionPath($option), $this->validOptionUpdatePayload([
            'code' => 'REG-LOG',
            'amount' => 9,
            'note' => 'by regular',
        ]))
            ->assertOk()
            ->assertJsonPath('data.price_change_logs.0.changer_name', $admin->name);

        $this->assertDatabaseHas('variable_change_logs', [
            'changeable_id' => $option->id,
            'changer_name' => $admin->name,
        ]);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_option_image_and_change_logs(): void
    {
        $this->actingAsSuperAdmin();
        $option = $this->createOptionWithImage(['code' => 'DEL-1']);
        $log = $this->createOptionChangeLog($option);
        $imageId = $option->image->id;

        $this->deleteJson($this->optionPath($option))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('options', ['id' => $option->id]);
        $this->assertDatabaseMissing('images', ['id' => $imageId]);
        $this->assertDatabaseMissing('variable_change_logs', ['id' => $log->id]);
    }

    public function test_destroy_returns_404_for_missing_option(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson($this->optionPath(99999))
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::NOT_FOUND_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Error paths
    // -------------------------------------------------------------------------

    public function test_index_returns_500_when_query_fails(): void
    {
        $this->actingAsSuperAdmin();

        Schema::drop('options');

        $this->getJson(self::INDEX_PATH)
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بارگذاری اطلاعات');
    }

    public function test_get_variables_returns_500_when_query_fails(): void
    {
        $this->actingAsSuperAdmin();

        Schema::drop('variables');

        $this->getJson(self::VARIABLES_PATH)
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بارگذاری اطلاعات');
    }

    public function test_store_returns_500_when_create_fails(): void
    {
        $this->actingAsSuperAdmin();

        Option::creating(function () {
            throw new \RuntimeException('forced option create failure');
        });

        $this->post(self::INDEX_PATH, $this->validOptionStorePayload([
            'code' => 'FAIL-OPT',
        ]), ['Accept' => 'application/json'])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در ثبت اطلاعات');
    }

    public function test_update_returns_500_when_update_fails(): void
    {
        $this->actingAsSuperAdmin();

        $option = $this->createOption(['code' => 'UPD-FAIL']);

        Option::updating(function () {
            throw new \RuntimeException('forced option update failure');
        });

        $this->putJson($this->optionPath($option), $this->validOptionUpdatePayload([
            'code' => 'UPD-FAIL',
            'amount' => 5,
            'note' => 'fail',
        ]))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بروزرسانی اطلاعات');
    }

    public function test_destroy_returns_500_when_delete_fails(): void
    {
        $this->actingAsSuperAdmin();

        $option = $this->createOption(['code' => 'DEL-FAIL']);

        Option::deleting(function () {
            throw new \RuntimeException('forced option delete failure');
        });

        $this->deleteJson($this->optionPath($option))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در حذف پکیج');
    }
}

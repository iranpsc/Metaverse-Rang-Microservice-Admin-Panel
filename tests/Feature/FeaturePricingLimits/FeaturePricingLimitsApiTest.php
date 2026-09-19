<?php

namespace Tests\Feature\FeaturePricingLimits;

use App\Models\Feature\FeaturePricingLimit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesFeaturePricingLimitsApiSchema;
use Tests\TestCase;

class FeaturePricingLimitsApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesFeaturePricingLimitsApiSchema;

    private const INDEX_PATH = '/api/lands/feature-pricing-limits';

    private const UPDATE_PATH = '/api/lands/feature-pricing-limits';

    private const INDEX_SUCCESS_MESSAGE = 'Pricing limits retrieved successfully.';

    private const UPDATE_SUCCESS_MESSAGE = 'محدودیت‌های قیمت با موفقیت به‌روزرسانی شدند';

    /**
     * @var list<string>
     */
    private const REQUIRED_INTEGER_FIELDS = [
        'public_price_limit',
        'under_eighteen_price_limit',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFeaturePricingLimitsApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload())
            ->assertUnauthorized();
    }

    public function test_regular_admin_can_access_both_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.price_limits', null)
            ->assertJsonPath('data.activity_logs', []);

        $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload([
            'public_price_limit' => 3000,
            'under_eighteen_price_limit' => 1500,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.price_limits.public_price_limit', 3000)
            ->assertJsonPath('data.price_limits.under_eighteen_price_limit', 1500)
            ->assertJsonCount(1, 'data.activity_logs');
    }

    public function test_super_admin_can_access_both_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonCount(1, 'data.activity_logs');
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_null_price_limits_when_no_record_exists(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.price_limits', null)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'price_limits',
                    'activity_logs',
                ],
            ]);
    }

    public function test_index_returns_correct_json_structure_when_record_exists(): void
    {
        $this->actingAsSuperAdmin();

        $record = $this->createFeaturePricingLimit([
            'public_price_limit' => 4000,
            'under_eighteen_price_limit' => 2000,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'price_limits' => [
                        'id',
                        'public_price_limit',
                        'under_eighteen_price_limit',
                        'updated_at',
                    ],
                    'activity_logs' => [
                        '*' => [
                            'id',
                            'description',
                            'event',
                            'causer_name',
                            'created_at_jalali',
                            'created_at_time',
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('data.price_limits.id', $record->id);
    }

    public function test_index_returns_correct_limit_values(): void
    {
        $this->actingAsSuperAdmin();

        $this->createFeaturePricingLimit([
            'public_price_limit' => 7500,
            'under_eighteen_price_limit' => 3200,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.price_limits.public_price_limit', 7500)
            ->assertJsonPath('data.price_limits.under_eighteen_price_limit', 3200);
    }

    public function test_index_defaults_null_db_column_values_to_zero_in_response(): void
    {
        $this->actingAsSuperAdmin();
        $this->ensureFeaturePricingLimitColumnsAllowNull();

        $record = FeaturePricingLimit::factory()->create([
            'public_price_limit' => 1,
            'under_eighteen_price_limit' => 1,
        ]);

        DB::table('feature_pricing_limits')
            ->where('id', $record->id)
            ->update([
                'public_price_limit' => null,
                'under_eighteen_price_limit' => null,
            ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.price_limits.public_price_limit', 0)
            ->assertJsonPath('data.price_limits.under_eighteen_price_limit', 0);
    }

    public function test_index_includes_activity_logs_for_authenticated_admin_changes(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload([
            'public_price_limit' => 1111,
            'under_eighteen_price_limit' => 222,
        ]))->assertOk();

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $logs = $response->json('data.activity_logs');
        $this->assertIsArray($logs);
        $this->assertNotEmpty($logs);
        $this->assertSame('created', $logs[0]['event']);
        $this->assertSame($admin->name, $logs[0]['causer_name']);
    }

    public function test_index_orders_activity_logs_newest_to_oldest(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload([
            'public_price_limit' => 100,
            'under_eighteen_price_limit' => 50,
        ]))->assertOk();

        $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload([
            'public_price_limit' => 200,
            'under_eighteen_price_limit' => 75,
        ]))->assertOk();

        $logs = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->json('data.activity_logs');

        $this->assertCount(2, $logs);
        $this->assertSame('updated', $logs[0]['event']);
        $this->assertSame('created', $logs[1]['event']);
        $this->assertGreaterThan($logs[1]['id'], $logs[0]['id']);
    }

    public function test_index_includes_updated_at_when_record_exists(): void
    {
        $this->actingAsSuperAdmin();

        $record = $this->createFeaturePricingLimit();

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $this->assertNotNull($response->json('data.price_limits.updated_at'));
        $this->assertSame(
            $record->fresh()->updated_at?->toJSON(),
            $response->json('data.price_limits.updated_at')
        );
    }

    public function test_update_response_includes_activity_logs(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $response = $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload())
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'price_limits' => [
                        'id',
                        'public_price_limit',
                        'under_eighteen_price_limit',
                        'updated_at',
                    ],
                    'activity_logs',
                ],
            ]);

        $logs = $response->json('data.activity_logs');
        $this->assertNotEmpty($logs);
        $this->assertSame($admin->name, $logs[0]['causer_name']);
    }

    // -------------------------------------------------------------------------
    // Update — happy path
    // -------------------------------------------------------------------------

    public function test_update_creates_record_when_none_exists(): void
    {
        $this->actingAsSuperAdmin();

        $this->assertDatabaseCount('feature_pricing_limits', 0);

        $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload([
            'public_price_limit' => 5000,
            'under_eighteen_price_limit' => 2500,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->assertDatabaseHas('feature_pricing_limits', [
            'public_price_limit' => 5000,
            'under_eighteen_price_limit' => 2500,
        ]);
    }

    public function test_update_updates_existing_record_without_creating_duplicate(): void
    {
        $this->actingAsSuperAdmin();

        $existing = $this->createFeaturePricingLimit([
            'public_price_limit' => 100,
            'under_eighteen_price_limit' => 50,
        ]);

        $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload([
            'public_price_limit' => 9000,
            'under_eighteen_price_limit' => 4500,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->assertDatabaseCount('feature_pricing_limits', 1);
        $this->assertDatabaseHas('feature_pricing_limits', [
            'id' => $existing->id,
            'public_price_limit' => 9000,
            'under_eighteen_price_limit' => 4500,
        ]);
    }

    public function test_update_persists_both_limit_fields_correctly(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::UPDATE_PATH, [
            'public_price_limit' => 12345,
            'under_eighteen_price_limit' => 6789,
        ])->assertOk();

        $this->assertDatabaseHas('feature_pricing_limits', [
            'public_price_limit' => 12345,
            'under_eighteen_price_limit' => 6789,
        ]);
    }

    public function test_update_accepts_zero_values(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::UPDATE_PATH, [
            'public_price_limit' => 0,
            'under_eighteen_price_limit' => 0,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->assertDatabaseHas('feature_pricing_limits', [
            'public_price_limit' => 0,
            'under_eighteen_price_limit' => 0,
        ]);
    }

    public function test_update_returns_persian_success_message(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload())
            ->assertOk()
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Update — validation
    // -------------------------------------------------------------------------

    public function test_update_requires_all_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::UPDATE_PATH, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(self::REQUIRED_INTEGER_FIELDS);

        $this->assertDatabaseCount('feature_pricing_limits', 0);
    }

    public function test_update_rejects_missing_individual_fields(): void
    {
        $this->actingAsSuperAdmin();

        foreach (self::REQUIRED_INTEGER_FIELDS as $field) {
            $payload = $this->validUpdatePayload();
            unset($payload[$field]);

            $this->postJson(self::UPDATE_PATH, $payload)
                ->assertStatus(422)
                ->assertJsonValidationErrors([$field]);
        }

        $this->assertDatabaseCount('feature_pricing_limits', 0);
    }

    public function test_update_rejects_non_integer_string_values(): void
    {
        $this->actingAsSuperAdmin();

        foreach (self::REQUIRED_INTEGER_FIELDS as $field) {
            $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload([
                $field => 'not-an-integer',
            ]))
                ->assertStatus(422)
                ->assertJsonValidationErrors([$field]);
        }

        $this->assertDatabaseCount('feature_pricing_limits', 0);
    }

    public function test_update_rejects_float_values(): void
    {
        $this->actingAsSuperAdmin();

        foreach (self::REQUIRED_INTEGER_FIELDS as $field) {
            $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload([
                $field => 12.5,
            ]))
                ->assertStatus(422)
                ->assertJsonValidationErrors([$field]);
        }

        $this->assertDatabaseCount('feature_pricing_limits', 0);
    }

    public function test_update_rejects_boolean_values(): void
    {
        $this->actingAsSuperAdmin();

        foreach (self::REQUIRED_INTEGER_FIELDS as $field) {
            $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload([
                $field => false,
            ]))
                ->assertStatus(422)
                ->assertJsonValidationErrors([$field]);
        }

        $this->assertDatabaseCount('feature_pricing_limits', 0);
    }

    public function test_update_coerces_boolean_true_to_integer_one(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::UPDATE_PATH, [
            'public_price_limit' => true,
            'under_eighteen_price_limit' => 500,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('feature_pricing_limits', [
            'public_price_limit' => 1,
            'under_eighteen_price_limit' => 500,
        ]);
    }

    public function test_update_rejects_array_values(): void
    {
        $this->actingAsSuperAdmin();

        foreach (self::REQUIRED_INTEGER_FIELDS as $field) {
            $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload([
                $field => [100],
            ]))
                ->assertStatus(422)
                ->assertJsonValidationErrors([$field]);
        }

        $this->assertDatabaseCount('feature_pricing_limits', 0);
    }

    public function test_update_rejects_null_values(): void
    {
        $this->actingAsSuperAdmin();

        foreach (self::REQUIRED_INTEGER_FIELDS as $field) {
            $this->postJson(self::UPDATE_PATH, $this->validUpdatePayload([
                $field => null,
            ]))
                ->assertStatus(422)
                ->assertJsonValidationErrors([$field]);
        }

        $this->assertDatabaseCount('feature_pricing_limits', 0);
    }

    private function ensureFeaturePricingLimitColumnsAllowNull(): void
    {
        if (! Schema::hasTable('feature_pricing_limits')) {
            return;
        }

        Schema::drop('feature_pricing_limits');

        Schema::create('feature_pricing_limits', function (Blueprint $table) {
            $table->id();
            $table->integer('public_price_limit')->nullable()->default(0);
            $table->integer('under_eighteen_price_limit')->nullable()->default(0);
            $table->timestamps();
        });
    }
}

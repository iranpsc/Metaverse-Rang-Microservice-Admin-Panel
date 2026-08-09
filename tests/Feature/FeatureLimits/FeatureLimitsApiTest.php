<?php

namespace Tests\Feature\FeatureLimits;

use App\Models\FeatureLimit;
use App\Models\FeatureProperties;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesFeatureLimitsApiSchema;
use Tests\TestCase;

class FeatureLimitsApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesFeatureLimitsApiSchema;

    private const INDEX_PATH = '/api/lands/feature-limits';

    private const STORE_PATH = '/api/lands/feature-limits';

    private const INDEX_SUCCESS_MESSAGE = 'Feature limits retrieved successfully.';

    private const STORE_SUCCESS_MESSAGE = 'محدودیت املاک با موفقیت ایجاد شد';

    private const DESTROY_SUCCESS_MESSAGE = 'محدودیت املاک با موفقیت حذف شد';

    private const DESTROY_ERROR_MESSAGE = 'خطا در حذف محدودیت';

    /**
     * @var list<string>
     */
    private const REQUIRED_BOOLEAN_FIELDS = [
        'verified_kyc_limit',
        'verified_bank_account_limit',
        'not_sellable',
        'under_18_limit',
        'more_than_18_limit',
        'dynasty_owner_limit',
        'price_limit',
        'individual_buy_limit',
    ];

    /**
     * @var list<string>
     */
    private const REQUIRED_SCALAR_FIELDS = [
        'title',
        'start_date',
        'end_date',
        'start_id',
        'end_id',
        'price',
        'individual_buy_count',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFeatureLimitsApiSchema();
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
        $this->postJson(self::STORE_PATH, $this->validStorePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $this->deleteJson($this->destroyPath(1))->assertUnauthorized();
    }

    public function test_regular_admin_can_index_and_destroy(): void
    {
        $this->actingAsRegularAdmin();

        $limit = $this->createFeatureLimit();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->deleteJson($this->destroyPath($limit->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    public function test_regular_admin_cannot_store(): void
    {
        $this->actingAsRegularAdmin();
        $this->seedStoreRangeProperties();

        $this->postJson(self::STORE_PATH, $this->validStorePayload())
            ->assertForbidden();

        $this->assertDatabaseCount('feature_limits', 0);
    }

    public function test_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedStoreRangeProperties();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->postJson(self::STORE_PATH, $this->validStorePayload())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $limit = FeatureLimit::query()->firstOrFail();

        $this->deleteJson($this->destroyPath($limit->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
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
            ->assertJsonPath('data.feature_limits', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'feature_limits',
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
    }

    public function test_index_returns_limits_with_shamsi_dates_and_expired_flag(): void
    {
        $this->actingAsSuperAdmin();

        $active = $this->createFeatureLimit([
            'title' => 'Active Limit',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);

        $expired = $this->createFeatureLimit([
            'title' => 'Expired Limit',
            'start_date' => now()->subDays(30)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
        ]);

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $items = collect($response->json('data.feature_limits'))->keyBy('id');

        $this->assertSame(
            Jalalian::fromCarbon(Carbon::parse($active->start_date))->format('Y/m/d'),
            $items[$active->id]['start_date_shamsi']
        );
        $this->assertSame(
            Jalalian::fromCarbon(Carbon::parse($active->end_date))->format('Y/m/d'),
            $items[$active->id]['end_date_shamsi']
        );
        $this->assertFalse($items[$active->id]['expired']);
        $this->assertTrue($items[$expired->id]['expired']);
    }

    public function test_index_supports_pagination(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 15; $i++) {
            $this->createFeatureLimit(['title' => "Limit {$i}"]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=5&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.per_page', 5)
            ->assertJsonPath('data.pagination.total', 15)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonCount(5, 'data.feature_limits');
    }

    // -------------------------------------------------------------------------
    // Store — happy path
    // -------------------------------------------------------------------------

    public function test_store_creates_feature_limit_successfully(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedStoreRangeProperties();

        $payload = $this->validStorePayload([
            'title' => 'محدودیت جدید',
            'verified_kyc_limit' => true,
        ]);

        $this->postJson(self::STORE_PATH, $payload)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $this->assertDatabaseHas('feature_limits', [
            'title' => 'محدودیت جدید',
            'start_id' => 'FL-001',
            'end_id' => 'FL-003',
            'verified_kyc_limit' => 1,
        ]);
    }

    public function test_store_zeros_price_and_individual_buy_count_when_limits_disabled(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedStoreRangeProperties();

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'price_limit' => false,
            'price' => 5000,
            'individual_buy_limit' => false,
            'individual_buy_count' => 7,
        ]))->assertOk();

        $this->assertDatabaseHas('feature_limits', [
            'price' => 0,
            'individual_buy_count' => 0,
        ]);
    }

    public function test_store_persists_price_and_individual_buy_count_when_limits_enabled(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedStoreRangeProperties();

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'price_limit' => true,
            'price' => 2500,
            'individual_buy_limit' => true,
            'individual_buy_count' => 3,
        ]))->assertOk();

        $this->assertDatabaseHas('feature_limits', [
            'price' => 2500,
            'individual_buy_count' => 3,
        ]);
    }

    // -------------------------------------------------------------------------
    // Store — validation
    // -------------------------------------------------------------------------

    public function test_store_requires_all_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::STORE_PATH, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(array_merge(
                self::REQUIRED_BOOLEAN_FIELDS,
                self::REQUIRED_SCALAR_FIELDS
            ));

        $this->assertDatabaseCount('feature_limits', 0);
    }

    public function test_store_rejects_missing_individual_fields(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedStoreRangeProperties();

        foreach (array_merge(self::REQUIRED_BOOLEAN_FIELDS, self::REQUIRED_SCALAR_FIELDS) as $field) {
            $payload = $this->validStorePayload();
            unset($payload[$field]);

            $this->postJson(self::STORE_PATH, $payload)
                ->assertStatus(422)
                ->assertJsonValidationErrors([$field]);
        }

        $this->assertDatabaseCount('feature_limits', 0);
    }

    public function test_store_rejects_title_longer_than_255_characters(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedStoreRangeProperties();

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'title' => str_repeat('ا', 256),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_rejects_invalid_jalali_dates(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedStoreRangeProperties();

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'start_date' => 'not-a-date',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'end_date' => '1400/99/99',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_store_rejects_nonexistent_feature_property_ids(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'start_id' => 'MISSING-001',
            'end_id' => 'MISSING-002',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_id', 'end_id']);
    }

    public function test_store_rejects_prefix_mismatch_between_start_and_end_id(): void
    {
        $this->actingAsSuperAdmin();

        $start = $this->createLandWithProperties([], [
            'id' => 'AAA-001',
            'id_prefix' => 'AAA',
            'id_postfix' => 1,
        ]);
        $end = $this->createLandWithProperties([], [
            'id' => 'BBB-003',
            'id_prefix' => 'BBB',
            'id_postfix' => 3,
        ]);

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'start_id' => $start->id,
            'end_id' => $end->id,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_id']);

        $errors = $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'start_id' => $start->id,
            'end_id' => $end->id,
        ]))->json('errors.end_id');

        $this->assertContains('پیشوند شناسه های شروع و پایان باید یکسان باشند', $errors);
    }

    public function test_store_rejects_start_date_overlap_with_existing_limit(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedStoreRangeProperties();

        $existingStart = Jalalian::now()->subMonths(1);
        $existingEnd = Jalalian::now()->addMonths(1);

        $this->createFeatureLimit([
            'start_date' => $existingStart->toCarbon()->toDateString(),
            'end_date' => $existingEnd->toCarbon()->toDateString(),
        ]);

        $overlapStart = Jalalian::now()->format('Y/m/d');

        $response = $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'start_date' => $overlapStart,
            'end_date' => Jalalian::now()->addMonths(3)->format('Y/m/d'),
        ]))->assertStatus(422);

        $this->assertContains('تاریخ شروع تداخل دارد', $response->json('errors.start_date'));
        $this->assertDatabaseCount('feature_limits', 1);
    }

    public function test_store_rejects_end_date_overlap_with_existing_limit(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedStoreRangeProperties();

        $this->createFeatureLimit([
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);

        $response = $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'start_date' => Jalalian::now()->addMonths(2)->format('Y/m/d'),
            'end_date' => Jalalian::now()->format('Y/m/d'),
        ]))->assertStatus(422);

        $this->assertContains('تاریخ پایان تداخل دارد', $response->json('errors.end_date'));
    }

    public function test_store_rejects_negative_price_and_individual_buy_count(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedStoreRangeProperties();

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'price' => -1,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price']);

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'individual_buy_count' => -5,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['individual_buy_count']);
    }

    // -------------------------------------------------------------------------
    // Store — side effects on feature_properties
    // -------------------------------------------------------------------------

    public function test_store_applies_limited_rgb_to_system_owned_features_in_range(): void
    {
        $this->actingAsSuperAdmin();
        $this->createFeaturePropertyRange('FL', 1, 3, 1, ['karbari' => 'm', 'rgb' => 'd']);

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'verified_kyc_limit' => true,
        ]))->assertOk();

        foreach (['FL-001', 'FL-002', 'FL-003'] as $propertyId) {
            $this->assertDatabaseHas('feature_properties', [
                'id' => $propertyId,
                'rgb' => 'g',
            ]);
        }
    }

    public function test_store_applies_sell_limited_rgb_when_not_sellable(): void
    {
        $this->actingAsSuperAdmin();
        $this->createFeaturePropertyRange('FL', 1, 1, 1, ['karbari' => 't', 'rgb' => 'k']);

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'start_id' => 'FL-001',
            'end_id' => 'FL-001',
            'not_sellable' => true,
        ]))->assertOk();

        $this->assertDatabaseHas('feature_properties', [
            'id' => 'FL-001',
            'rgb' => 'm',
        ]);
    }

    public function test_store_sets_stability_when_price_limit_enabled(): void
    {
        $this->actingAsSuperAdmin();
        $this->createFeaturePropertyRange('FL', 1, 1, 1);

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'start_id' => 'FL-001',
            'end_id' => 'FL-001',
            'price_limit' => true,
            'price' => 1234,
        ]))->assertOk();

        $this->assertDatabaseHas('feature_properties', [
            'id' => 'FL-001',
            'stability' => 1234,
        ]);
    }

    public function test_store_does_not_update_features_outside_range(): void
    {
        $this->actingAsSuperAdmin();
        $this->createFeaturePropertyRange('FL', 1, 3, 1, ['rgb' => 'd']);
        $outside = $this->createLandWithProperties([], [
            'id' => 'FL-010',
            'id_prefix' => 'FL',
            'id_postfix' => 10,
            'rgb' => 'd',
        ]);

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'end_id' => 'FL-003',
            'verified_kyc_limit' => true,
        ]))->assertOk();

        $this->assertDatabaseHas('feature_properties', [
            'id' => $outside->id,
            'rgb' => 'd',
        ]);
    }

    public function test_store_does_not_update_non_system_owned_features(): void
    {
        $this->actingAsSuperAdmin();
        $this->createFeaturePropertyRange('FL', 1, 3, 1, ['rgb' => 'd']);
        $nonSystem = $this->seedNonSystemOwnedProperty('FL', 4);

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'verified_kyc_limit' => true,
        ]))->assertOk();

        $this->assertDatabaseHas('feature_properties', [
            'id' => $nonSystem->id,
            'rgb' => 'd',
        ]);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_feature_limit(): void
    {
        $this->actingAsSuperAdmin();

        $limit = $this->createFeatureLimit();

        $this->deleteJson($this->destroyPath($limit->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('feature_limits', ['id' => $limit->id]);
    }

    public function test_destroy_returns_error_for_missing_limit(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson($this->destroyPath(999999))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::DESTROY_ERROR_MESSAGE);
    }

    public function test_destroy_restores_rgb_and_stability_on_system_features_in_range(): void
    {
        $this->actingAsSuperAdmin();

        $properties = $this->createFeaturePropertyRange('FL', 1, 2, 1, [
            'karbari' => 'm',
            'rgb' => 'd',
            'density' => 4,
            'area' => 50,
            'stability' => 200,
        ]);

        $limit = $this->createFeatureLimit([
            'start_id' => 'FL-001',
            'end_id' => 'FL-002',
            'verified_kyc_limit' => true,
        ]);

        foreach ($properties as $property) {
            $property->update(['rgb' => 'g', 'stability' => 999]);
        }

        $this->deleteJson($this->destroyPath($limit->id))->assertOk();

        foreach (['FL-001', 'FL-002'] as $propertyId) {
            $property = FeatureProperties::query()->findOrFail($propertyId);

            $this->assertSame('d', $property->rgb);
            $this->assertSame(200.0, (float) $property->stability);
        }
    }

    private function destroyPath(int $id): string
    {
        return self::INDEX_PATH.'/'.$id;
    }

    public function test_store_returns_server_error_when_service_throws(): void
    {
        $this->actingAsSuperAdmin();
        $this->createFeaturePropertyRange('FL', 1, 3, 1);

        $this->mock(\App\Services\Lands\FeatureLimitService::class, function ($mock) {
            $mock->shouldReceive('create')
                ->once()
                ->andThrow(new \RuntimeException('db failure'));
        });

        $this->postJson(self::STORE_PATH, $this->validStorePayload([
            'end_id' => 'FL-003',
        ]))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در ایجاد محدودیت: db failure');
    }
}

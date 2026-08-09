<?php

namespace Tests\Feature\Lands;

use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesLandsMarketApiSchema;
use Tests\TestCase;

class PricingControllerTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesLandsMarketApiSchema;

    private const INDEX_PATH = '/api/lands/pricing';

    private const SUCCESS_MESSAGE = 'Pricing requests retrieved successfully.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLandsMarketApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_super_admin_can_access_index(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE);
    }

    public function test_regular_admin_can_access_index(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Index — happy path / structure
    // -------------------------------------------------------------------------

    public function test_empty_dataset_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE)
            ->assertJsonPath('data.pricings', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure_with_eager_loaded_relations(): void
    {
        $this->actingAsSuperAdmin();

        $property = $this->createLandWithProperties([], ['id' => 'PRICE-REQ-001']);
        $pricing = $this->createSellFeatureRequest([
            'feature_id' => $property->feature_id,
            'status' => 0,
            'price_psc' => 750,
            'price_irr' => 1500000,
            'note' => 'Awaiting approval',
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'pricings' => [
                        [
                            'id',
                            'seller_id',
                            'buyer_id',
                            'feature_id',
                            'status',
                            'note',
                            'price_psc',
                            'price_irr',
                            'created_at',
                            'updated_at',
                            'feature' => [
                                'id',
                                'properties' => [
                                    'id',
                                    'feature_id',
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
            ])
            ->assertJsonPath('data.pricings.0.id', $pricing->id)
            ->assertJsonPath('data.pricings.0.status', 0)
            ->assertJsonPath('data.pricings.0.feature.properties.id', 'PRICE-REQ-001')
            ->assertJsonPath('data.pricings.0.note', 'Awaiting approval');
    }

    // -------------------------------------------------------------------------
    // Index — filtering
    // -------------------------------------------------------------------------

    public function test_index_includes_only_pending_pricing_requests_with_status_zero(): void
    {
        $this->actingAsSuperAdmin();

        $pending = $this->createSellFeatureRequest(['status' => 0]);
        $this->createSellFeatureRequest(['status' => 1]);
        $this->createSellFeatureRequest(['status' => 2]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.pricings.0.id', $pending->id)
            ->assertJsonPath('data.pricings.0.status', 0);
    }

    public function test_index_excludes_non_pending_pricing_requests(): void
    {
        $this->actingAsSuperAdmin();

        $this->createSellFeatureRequest(['status' => 1]);
        $this->createSellFeatureRequest(['status' => -1]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pricings', []);
    }

    // -------------------------------------------------------------------------
    // Index — search
    // -------------------------------------------------------------------------

    public function test_search_by_property_id(): void
    {
        $this->actingAsSuperAdmin();

        $matchingProperty = $this->createLandWithProperties([], ['id' => 'PRICING-NEEDLE-001']);
        $otherProperty = $this->createLandWithProperties([], ['id' => 'PRICING-OTHER-002']);

        $matchingPricing = $this->createSellFeatureRequest([
            'feature_id' => $matchingProperty->feature_id,
            'status' => 0,
        ]);
        $this->createSellFeatureRequest([
            'feature_id' => $otherProperty->feature_id,
            'status' => 0,
        ]);

        $this->getJson(self::INDEX_PATH.'?search=NEEDLE')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.pricings.0.id', $matchingPricing->id);
    }

    public function test_empty_search_behaves_like_no_filter(): void
    {
        $this->actingAsSuperAdmin();

        $this->createSellFeatureRequest(['status' => 0]);
        $this->createSellFeatureRequest(['status' => 0]);

        $this->getJson(self::INDEX_PATH.'?search=')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);
    }

    public function test_search_does_not_return_non_pending_requests(): void
    {
        $this->actingAsSuperAdmin();

        $property = $this->createLandWithProperties([], ['id' => 'PRICING-CLOSED-001']);
        $this->createSellFeatureRequest([
            'feature_id' => $property->feature_id,
            'status' => 1,
        ]);

        $this->getJson(self::INDEX_PATH.'?search=CLOSED')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 0);
    }

    // -------------------------------------------------------------------------
    // Index — pagination / ordering
    // -------------------------------------------------------------------------

    public function test_pagination_respects_per_page_and_page(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->createSellFeatureRequest(['status' => 0]);
        }

        $this->getJson(self::INDEX_PATH.'?'.http_build_query([
            'per_page' => 2,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonCount(2, 'data.pricings');
    }

    public function test_pricing_requests_are_ordered_by_created_at_desc(): void
    {
        $this->actingAsSuperAdmin();

        $oldest = $this->createSellFeatureRequest(['status' => 0]);
        $oldest->forceFill([
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ])->save();

        $newest = $this->createSellFeatureRequest(['status' => 0]);
        $newest->forceFill([
            'created_at' => now(),
            'updated_at' => now(),
        ])->save();

        $middle = $this->createSellFeatureRequest(['status' => 0]);
        $middle->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $ids = collect($response->json('data.pricings'))->pluck('id')->all();

        $this->assertSame([$newest->id, $middle->id, $oldest->id], $ids);
    }
}

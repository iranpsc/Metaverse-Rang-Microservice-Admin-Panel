<?php

namespace Tests\Feature\Lands;

use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesLandsMarketApiSchema;
use Tests\TestCase;

class PricesControllerTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesLandsMarketApiSchema;

    private const INDEX_PATH = '/api/lands/prices';

    private const SUCCESS_MESSAGE = 'Land prices retrieved successfully.';

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
            ->assertJsonPath('data.features', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure_with_eager_loaded_properties(): void
    {
        $this->actingAsSuperAdmin();

        $owner = $this->createCitizenUser(['name' => 'Price Owner', 'code' => 'PO001']);
        $feature = $this->createFeature(['owner_id' => $owner->id]);
        $property = $this->createFeatureProperties($feature, [
            'id' => 'LAND-PRICE-001',
            'price' => 2500,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'features' => [
                        [
                            'id',
                            'map_id',
                            'type',
                            'owner_id',
                            'created_at',
                            'updated_at',
                            'properties' => [
                                'id',
                                'feature_id',
                                'price',
                                'area',
                                'density',
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
            ->assertJsonPath('data.features.0.id', $feature->id)
            ->assertJsonPath('data.features.0.properties.id', $property->id)
            ->assertJsonPath('data.features.0.properties.price', 2500);
    }

    public function test_index_includes_features_without_properties(): void
    {
        $this->actingAsSuperAdmin();

        $feature = $this->createFeature();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.features.0.id', $feature->id)
            ->assertJsonPath('data.features.0.properties', null);
    }

    // -------------------------------------------------------------------------
    // Index — search
    // -------------------------------------------------------------------------

    public function test_search_by_property_id(): void
    {
        $this->actingAsSuperAdmin();

        $matching = $this->createLandWithProperties([], ['id' => 'PRICE-NEEDLE-001']);
        $this->createLandWithProperties([], ['id' => 'PRICE-OTHER-002']);
        $this->createFeature();

        $this->getJson(self::INDEX_PATH.'?search=NEEDLE')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.features.0.id', $matching->feature_id)
            ->assertJsonPath('data.features.0.properties.id', 'PRICE-NEEDLE-001');
    }

    public function test_empty_search_behaves_like_no_filter(): void
    {
        $this->actingAsSuperAdmin();

        $this->createLandWithProperties([], ['id' => 'PRICE-A']);
        $this->createLandWithProperties([], ['id' => 'PRICE-B']);

        $this->getJson(self::INDEX_PATH.'?search=')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);
    }

    public function test_search_excludes_features_without_matching_properties(): void
    {
        $this->actingAsSuperAdmin();

        $this->createLandWithProperties([], ['id' => 'VISIBLE-001']);
        $this->createFeature();

        $this->getJson(self::INDEX_PATH.'?search=VISIBLE')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1);
    }

    // -------------------------------------------------------------------------
    // Index — pagination
    // -------------------------------------------------------------------------

    public function test_pagination_respects_per_page_and_page(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->createLandWithProperties([], ['id' => sprintf('PAGE-PRICE-%03d', $i)]);
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
            ->assertJsonCount(2, 'data.features');
    }

    public function test_pagination_defaults_to_ten_items_per_page(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 12; $i++) {
            $this->createFeature();
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonCount(10, 'data.features');
    }
}

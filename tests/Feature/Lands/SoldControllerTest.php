<?php

namespace Tests\Feature\Lands;

use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesLandsMarketApiSchema;
use Tests\TestCase;

class SoldControllerTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesLandsMarketApiSchema;

    private const INDEX_PATH = '/api/lands/sold';

    private const SUCCESS_MESSAGE = 'Sold lands retrieved successfully.';

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
            ->assertJsonPath('data.trades', [])
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

        $buyer = $this->createCitizenUser(['name' => 'Sold Buyer', 'code' => 'SB001']);
        $property = $this->createLandWithProperties([], ['id' => 'SOLD-001']);

        $trade = $this->createTrade([
            'feature_id' => $property->feature_id,
            'buyer_id' => $buyer->id,
            'seller_id' => 1,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'trades' => [
                        [
                            'id',
                            'feature_id',
                            'buyer_id',
                            'seller_id',
                            'date',
                            'created_at',
                            'updated_at',
                            'feature' => [
                                'id',
                                'properties' => [
                                    'id',
                                    'feature_id',
                                ],
                            ],
                            'buyer' => ['id', 'name', 'code'],
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
            ->assertJsonPath('data.trades.0.id', $trade->id)
            ->assertJsonPath('data.trades.0.seller_id', 1)
            ->assertJsonPath('data.trades.0.feature.properties.id', 'SOLD-001')
            ->assertJsonPath('data.trades.0.buyer.name', 'Sold Buyer')
            ->assertJsonMissingPath('data.trades.0.seller')
            ->assertJsonMissingPath('data.trades.0.commision');
    }

    // -------------------------------------------------------------------------
    // Index — filtering
    // -------------------------------------------------------------------------

    public function test_index_includes_only_trades_sold_by_system_user(): void
    {
        $this->actingAsSuperAdmin();

        $systemSold = $this->createTrade(['seller_id' => 1]);
        $peerSeller = $this->createCitizenUser(['name' => 'Peer Seller']);
        $this->createTrade(['seller_id' => $peerSeller->id]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.trades.0.id', $systemSold->id)
            ->assertJsonPath('data.trades.0.seller_id', 1);
    }

    public function test_index_excludes_peer_to_peer_trades(): void
    {
        $this->actingAsSuperAdmin();

        $seller = $this->createCitizenUser(['name' => 'Non System Seller']);
        $this->createTrade(['seller_id' => $seller->id]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.trades', []);
    }

    // -------------------------------------------------------------------------
    // Index — search
    // -------------------------------------------------------------------------

    public function test_search_by_property_id(): void
    {
        $this->actingAsSuperAdmin();

        $matchingProperty = $this->createLandWithProperties([], ['id' => 'SOLD-NEEDLE-001']);
        $otherProperty = $this->createLandWithProperties([], ['id' => 'SOLD-OTHER-002']);

        $matchingTrade = $this->createTrade([
            'feature_id' => $matchingProperty->feature_id,
            'seller_id' => 1,
        ]);
        $this->createTrade([
            'feature_id' => $otherProperty->feature_id,
            'seller_id' => 1,
        ]);

        $this->getJson(self::INDEX_PATH.'?search=NEEDLE')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.trades.0.id', $matchingTrade->id);
    }

    public function test_empty_search_behaves_like_no_filter(): void
    {
        $this->actingAsSuperAdmin();

        $this->createTrade(['seller_id' => 1]);
        $this->createTrade(['seller_id' => 1]);

        $this->getJson(self::INDEX_PATH.'?search=')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);
    }

    // -------------------------------------------------------------------------
    // Index — pagination / ordering
    // -------------------------------------------------------------------------

    public function test_pagination_respects_per_page_and_page(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->createTrade(['seller_id' => 1]);
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
            ->assertJsonCount(2, 'data.trades');
    }

    public function test_trades_are_ordered_by_created_at_desc(): void
    {
        $this->actingAsSuperAdmin();

        $oldest = $this->createTradeAt(now()->subDays(3), ['seller_id' => 1]);
        $newest = $this->createTradeAt(now(), ['seller_id' => 1]);
        $middle = $this->createTradeAt(now()->subDay(), ['seller_id' => 1]);

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $ids = collect($response->json('data.trades'))->pluck('id')->all();

        $this->assertSame([$newest->id, $middle->id, $oldest->id], $ids);
    }
}

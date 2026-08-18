<?php

namespace Tests\Feature\Lands;

use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesLandsMarketApiSchema;
use Tests\TestCase;

class TradedControllerTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesLandsMarketApiSchema;

    private const INDEX_PATH = '/api/lands/traded';

    private const SUCCESS_MESSAGE = 'Traded lands retrieved successfully.';

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

        $buyer = $this->createCitizenUser(['name' => 'Trade Buyer', 'code' => 'BUY001']);
        $seller = $this->createCitizenUser(['name' => 'Trade Seller', 'code' => 'SEL001']);
        $property = $this->createLandWithProperties([], ['id' => 'TRADED-001']);

        $trade = $this->createTrade([
            'feature_id' => $property->feature_id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->createComission($trade, ['amount' => 250.5]);

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
                            'seller' => ['id', 'name', 'code'],
                            'commision' => ['id', 'trade_id', 'amount'],
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
            ->assertJsonPath('data.trades.0.feature.properties.id', 'TRADED-001')
            ->assertJsonPath('data.trades.0.buyer.name', 'Trade Buyer')
            ->assertJsonPath('data.trades.0.seller.name', 'Trade Seller')
            ->assertJsonPath('data.trades.0.commision.amount', 250.5);
    }

    // -------------------------------------------------------------------------
    // Index — filtering
    // -------------------------------------------------------------------------

    public function test_index_includes_trades_where_seller_is_not_system_user(): void
    {
        $this->actingAsSuperAdmin();

        $seller = $this->createCitizenUser(['name' => 'Peer Seller']);
        $trade = $this->createTrade(['seller_id' => $seller->id]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.trades.0.id', $trade->id);
    }

    public function test_index_excludes_trades_sold_by_system_user(): void
    {
        $this->actingAsSuperAdmin();

        $this->createTrade(['seller_id' => 1]);
        $peerSeller = $this->createCitizenUser(['name' => 'Included Seller']);
        $included = $this->createTrade(['seller_id' => $peerSeller->id]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.trades.0.id', $included->id);
    }

    // -------------------------------------------------------------------------
    // Index — search
    // -------------------------------------------------------------------------

    public function test_search_by_property_id(): void
    {
        $this->actingAsSuperAdmin();

        $matchingProperty = $this->createLandWithProperties([], ['id' => 'TRADE-NEEDLE-001']);
        $otherProperty = $this->createLandWithProperties([], ['id' => 'TRADE-OTHER-002']);

        $matchingTrade = $this->createTrade(['feature_id' => $matchingProperty->feature_id]);
        $this->createTrade(['feature_id' => $otherProperty->feature_id]);

        $this->getJson(self::INDEX_PATH.'?search=NEEDLE')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.trades.0.id', $matchingTrade->id);
    }

    public function test_empty_search_behaves_like_no_filter(): void
    {
        $this->actingAsSuperAdmin();

        $this->createTrade();
        $this->createTrade();

        $this->getJson(self::INDEX_PATH.'?search=')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);
    }

    public function test_search_trims_whitespace(): void
    {
        $this->actingAsSuperAdmin();

        $property = $this->createLandWithProperties([], ['id' => 'TRIM-001']);
        $trade = $this->createTrade(['feature_id' => $property->feature_id]);
        $this->createTrade();

        $this->getJson(self::INDEX_PATH.'?search='.urlencode('  TRIM  '))
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.trades.0.id', $trade->id);
    }

    // -------------------------------------------------------------------------
    // Index — pagination / ordering
    // -------------------------------------------------------------------------

    public function test_pagination_respects_per_page_and_page(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->createTrade();
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

        $oldest = $this->createTradeAt(now()->subDays(3));
        $newest = $this->createTradeAt(now());
        $middle = $this->createTradeAt(now()->subDay());

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $ids = collect($response->json('data.trades'))->pluck('id')->all();

        $this->assertSame([$newest->id, $middle->id, $oldest->id], $ids);
    }
}

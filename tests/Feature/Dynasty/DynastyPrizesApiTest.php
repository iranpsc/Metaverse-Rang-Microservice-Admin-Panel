<?php

namespace Tests\Feature\Dynasty;

use App\Models\Dynasty\DynastyPrize;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesDynastyApiSchema;
use Tests\TestCase;

class DynastyPrizesApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesDynastyApiSchema;

    private const INDEX_PATH = '/api/dynasty/prizes';

    private const INDEX_SUCCESS_MESSAGE = 'جوایز سلسله با موفقیت بارگذاری شدند.';

    private const STORE_SUCCESS_MESSAGE = 'اطلاعات با موفقیت ثبت شد';

    private const UPDATE_SUCCESS_MESSAGE = 'اطلاعات با موفقیت ثبت شد';

    private const DESTROY_SUCCESS_MESSAGE = 'پاداش با موفقیت حذف شد';

    private const NOT_FOUND_MESSAGE = 'پاداش یافت نشد';

    /** @var array<string, string> */
    private const MEMBER_TITLES = [
        'father' => 'پدر',
        'mother' => 'مادر',
        'brother' => 'برادر',
        'sister' => 'خواهر',
        'offspring' => 'فرزند',
        'wife' => 'زن',
        'husband' => 'شوهر',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDynastyApiSchema();
    }

    private function prizePath(int|DynastyPrize $prize): string
    {
        $id = $prize instanceof DynastyPrize ? $prize->id : $prize;

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
        $this->postJson(self::INDEX_PATH, $this->validDynastyPrizeStorePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $this->putJson($this->prizePath(1), $this->validDynastyPrizeUpdatePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $this->deleteJson($this->prizePath(1))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $response = $this->postJson(self::INDEX_PATH, $this->validDynastyPrizeStorePayload([
            'member' => 'father',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $id = $response->json('data.id');

        $this->putJson($this->prizePath($id), $this->validDynastyPrizeUpdatePayload())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->deleteJson($this->prizePath($id))
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

        $response = $this->postJson(self::INDEX_PATH, $this->validDynastyPrizeStorePayload([
            'member' => 'mother',
        ]))->assertOk();

        $id = $response->json('data.id');

        $this->putJson($this->prizePath($id), $this->validDynastyPrizeUpdatePayload())->assertOk();
        $this->deleteJson($this->prizePath($id))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Happy path / Index
    // -------------------------------------------------------------------------

    public function test_index_returns_empty_prizes_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.prizes', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure_with_percent_fields(): void
    {
        $this->actingAsSuperAdmin();

        $prize = $this->createDynastyPrize([
            'member' => 'father',
            'satisfaction' => 50,
            'introduction_profit_increase' => 0.25,
            'accumulated_capital_reserve' => 0.10,
            'data_storage' => 0.05,
            'psc' => 100,
        ]);

        $response = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'prizes' => [
                        [
                            'id',
                            'member',
                            'member_title',
                            'satisfaction',
                            'introduction_profit_increase',
                            'introduction_profit_increase_percent',
                            'accumulated_capital_reserve',
                            'accumulated_capital_reserve_percent',
                            'data_storage',
                            'data_storage_percent',
                            'psc',
                            'created_at',
                            'updated_at',
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

        $this->assertSame($prize->id, $response->json('data.prizes.0.id'));
        $this->assertSame('father', $response->json('data.prizes.0.member'));
        $this->assertSame('پدر', $response->json('data.prizes.0.member_title'));
        $this->assertEquals(50, $response->json('data.prizes.0.satisfaction'));
        $this->assertEquals(0.25, $response->json('data.prizes.0.introduction_profit_increase'));
        $this->assertEquals(25, $response->json('data.prizes.0.introduction_profit_increase_percent'));
        $this->assertEquals(0.10, $response->json('data.prizes.0.accumulated_capital_reserve'));
        $this->assertEquals(10, $response->json('data.prizes.0.accumulated_capital_reserve_percent'));
        $this->assertEquals(0.05, $response->json('data.prizes.0.data_storage'));
        $this->assertEquals(5, $response->json('data.prizes.0.data_storage_percent'));
        $this->assertEquals(100, $response->json('data.prizes.0.psc'));
        $this->assertSame(1, $response->json('data.pagination.total'));
        $this->assertSame(1, $response->json('data.pagination.from'));
        $this->assertSame(1, $response->json('data.pagination.to'));
    }

    public function test_index_returns_member_title_for_each_valid_member(): void
    {
        $this->actingAsSuperAdmin();

        foreach (self::MEMBER_TITLES as $member => $title) {
            $this->createDynastyPrize(['member' => $member]);
        }

        $response = $this->getJson(self::INDEX_PATH.'?per_page=20')->assertOk();
        $items = collect($response->json('data.prizes'))->keyBy('member');

        foreach (self::MEMBER_TITLES as $member => $title) {
            $this->assertSame($title, $items[$member]['member_title']);
        }
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_default_per_page_is_ten(): void
    {
        $this->actingAsSuperAdmin();

        $members = array_keys(self::MEMBER_TITLES);

        for ($i = 1; $i <= 12; $i++) {
            $this->createDynastyPrize([
                'member' => $members[($i - 1) % count($members)],
                'satisfaction' => $i,
            ]);
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonCount(10, 'data.prizes');
    }

    public function test_custom_per_page_is_respected(): void
    {
        $this->actingAsSuperAdmin();

        $members = array_keys(self::MEMBER_TITLES);

        for ($i = 1; $i <= 5; $i++) {
            $this->createDynastyPrize([
                'member' => $members[$i - 1],
                'satisfaction' => $i,
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonCount(2, 'data.prizes');
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_divides_percent_fields_by_100_and_keeps_satisfaction_and_psc(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson(self::INDEX_PATH, $this->validDynastyPrizeStorePayload([
            'member' => 'brother',
            'satisfaction' => 80,
            'introduction_profit_increase' => 25,
            'accumulated_capital_reserve' => 10,
            'data_storage' => 5,
            'psc' => 150,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.member', 'brother')
            ->assertJsonPath('data.member_title', 'برادر')
            ->assertJsonPath('data.satisfaction', 80)
            ->assertJsonPath('data.psc', 150);

        $id = $response->json('data.id');

        $this->assertEquals(0.25, $response->json('data.introduction_profit_increase'));
        $this->assertEquals(25, $response->json('data.introduction_profit_increase_percent'));
        $this->assertEquals(0.10, $response->json('data.accumulated_capital_reserve'));
        $this->assertEquals(10, $response->json('data.accumulated_capital_reserve_percent'));
        $this->assertEquals(0.05, $response->json('data.data_storage'));
        $this->assertEquals(5, $response->json('data.data_storage_percent'));

        $this->assertDatabaseHas('dynasty_prizes', [
            'id' => $id,
            'member' => 'brother',
            'satisfaction' => 80,
            'psc' => 150,
        ]);

        $stored = DynastyPrize::find($id);
        $this->assertEqualsWithDelta(0.25, (float) $stored->introduction_profit_increase, 0.0001);
        $this->assertEqualsWithDelta(0.10, (float) $stored->accumulated_capital_reserve, 0.0001);
        $this->assertEqualsWithDelta(0.05, (float) $stored->data_storage, 0.0001);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_divides_percent_fields_and_does_not_change_member(): void
    {
        $this->actingAsSuperAdmin();

        $prize = $this->createDynastyPrize([
            'member' => 'sister',
            'satisfaction' => 10,
            'introduction_profit_increase' => 0.1,
            'accumulated_capital_reserve' => 0.1,
            'data_storage' => 0.1,
            'psc' => 50,
        ]);

        $this->putJson($this->prizePath($prize), $this->validDynastyPrizeUpdatePayload([
            'satisfaction' => 90,
            'introduction_profit_increase' => 40,
            'accumulated_capital_reserve' => 20,
            'data_storage' => 15,
            'psc' => 250,
            'member' => 'wife',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.member', 'sister')
            ->assertJsonPath('data.member_title', 'خواهر')
            ->assertJsonPath('data.satisfaction', 90)
            ->assertJsonPath('data.psc', 250);

        $prize->refresh();

        $this->assertSame('sister', $prize->member);
        $this->assertEquals(90, $prize->satisfaction);
        $this->assertEquals(250, $prize->psc);
        $this->assertEqualsWithDelta(0.40, (float) $prize->introduction_profit_increase, 0.0001);
        $this->assertEqualsWithDelta(0.20, (float) $prize->accumulated_capital_reserve, 0.0001);
        $this->assertEqualsWithDelta(0.15, (float) $prize->data_storage, 0.0001);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_prize(): void
    {
        $this->actingAsSuperAdmin();

        $prize = $this->createDynastyPrize(['member' => 'offspring']);

        $this->deleteJson($this->prizePath($prize))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('dynasty_prizes', [
            'id' => $prize->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_store_requires_all_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'member',
                'satisfaction',
                'introduction_profit_increase',
                'accumulated_capital_reserve',
                'data_storage',
                'psc',
            ]);
    }

    public function test_store_rejects_invalid_member(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validDynastyPrizeStorePayload([
            'member' => 'uncle',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['member']);
    }

    public function test_store_rejects_duplicate_member(): void
    {
        $this->actingAsSuperAdmin();

        $this->createDynastyPrize(['member' => 'husband']);

        $this->postJson(self::INDEX_PATH, $this->validDynastyPrizeStorePayload([
            'member' => 'husband',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['member']);
    }

    public function test_store_rejects_negative_numeric_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validDynastyPrizeStorePayload([
            'member' => 'wife',
            'satisfaction' => -1,
            'introduction_profit_increase' => -5,
            'accumulated_capital_reserve' => -2,
            'data_storage' => -3,
            'psc' => -10,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'satisfaction',
                'introduction_profit_increase',
                'accumulated_capital_reserve',
                'data_storage',
                'psc',
            ]);
    }

    public function test_store_rejects_non_numeric_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validDynastyPrizeStorePayload([
            'member' => 'wife',
            'satisfaction' => 'abc',
            'introduction_profit_increase' => 'x',
            'accumulated_capital_reserve' => 'y',
            'data_storage' => 'z',
            'psc' => 'n/a',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'satisfaction',
                'introduction_profit_increase',
                'accumulated_capital_reserve',
                'data_storage',
                'psc',
            ]);
    }

    public function test_update_requires_numeric_fields(): void
    {
        $this->actingAsSuperAdmin();

        $prize = $this->createDynastyPrize(['member' => 'wife']);

        $this->putJson($this->prizePath($prize), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'satisfaction',
                'introduction_profit_increase',
                'accumulated_capital_reserve',
                'data_storage',
                'psc',
            ]);
    }

    public function test_update_rejects_negative_numeric_fields(): void
    {
        $this->actingAsSuperAdmin();

        $prize = $this->createDynastyPrize(['member' => 'wife']);

        $this->putJson($this->prizePath($prize), $this->validDynastyPrizeUpdatePayload([
            'satisfaction' => -1,
            'introduction_profit_increase' => -1,
            'accumulated_capital_reserve' => -1,
            'data_storage' => -1,
            'psc' => -1,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'satisfaction',
                'introduction_profit_increase',
                'accumulated_capital_reserve',
                'data_storage',
                'psc',
            ]);
    }

    // -------------------------------------------------------------------------
    // Not found
    // -------------------------------------------------------------------------

    public function test_update_returns_not_found_for_missing_prize(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->prizePath(99999), $this->validDynastyPrizeUpdatePayload())
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::NOT_FOUND_MESSAGE);
    }

    public function test_destroy_returns_not_found_for_missing_prize(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson($this->prizePath(99999))
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

        Schema::drop('dynasty_prizes');

        $this->getJson(self::INDEX_PATH)
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بارگذاری جوایز سلسله');
    }

    public function test_store_returns_500_when_create_fails(): void
    {
        $this->actingAsSuperAdmin();

        DynastyPrize::creating(function () {
            throw new \RuntimeException('forced prize create failure');
        });

        $this->postJson(self::INDEX_PATH, $this->validDynastyPrizeStorePayload([
            'member' => 'father',
        ]))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در ثبت اطلاعات');
    }

    public function test_update_returns_500_when_update_fails(): void
    {
        $this->actingAsSuperAdmin();

        $prize = $this->createDynastyPrize(['member' => 'mother']);

        DynastyPrize::updating(function () {
            throw new \RuntimeException('forced prize update failure');
        });

        $this->putJson($this->prizePath($prize), $this->validDynastyPrizeUpdatePayload())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بروزرسانی اطلاعات');
    }

    public function test_destroy_returns_500_when_delete_fails(): void
    {
        $this->actingAsSuperAdmin();

        $prize = $this->createDynastyPrize(['member' => 'sister']);

        DynastyPrize::deleting(function () {
            throw new \RuntimeException('forced prize delete failure');
        });

        $this->deleteJson($this->prizePath($prize))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در حذف پاداش');
    }
}

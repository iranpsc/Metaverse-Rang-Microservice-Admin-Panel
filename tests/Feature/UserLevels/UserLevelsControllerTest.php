<?php

namespace Tests\Feature\UserLevels;

use App\Models\Level\Level;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesUserLevelsApiSchema;
use Tests\TestCase;

class UserLevelsControllerTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesUserLevelsApiSchema;

    private const INDEX_PATH = '/api/user-levels';

    private const SEARCH_PATH = '/api/users/search';

    private const PROMOTE_PATH = '/api/user-levels/promote';

    private const INDEX_SUCCESS_MESSAGE = 'لیست سطوح کاربران با موفقیت دریافت شد.';

    private const SEARCH_SUCCESS_MESSAGE = 'لیست کاربران با موفقیت دریافت شد.';

    private const PROMOTE_SUCCESS_MESSAGE = 'سطح کاربر با موفقیت ارتقاء یافت.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpUserLevelsApiSchema();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_search_users_returns_unauthorized(): void
    {
        $this->getJson(self::SEARCH_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_promote_returns_unauthorized(): void
    {
        $this->postJson(self::PROMOTE_PATH, [
            'user_id' => 1,
            'score' => 10,
        ])->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->getJson(self::SEARCH_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SEARCH_SUCCESS_MESSAGE);

        $user = User::factory()->create();

        $this->postJson(self::PROMOTE_PATH, [
            'user_id' => $user->id,
            'score' => 5,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::PROMOTE_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->getJson(self::SEARCH_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SEARCH_SUCCESS_MESSAGE);

        $user = User::factory()->create();

        $this->postJson(self::PROMOTE_PATH, [
            'user_id' => $user->id,
            'score' => 5,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::PROMOTE_SUCCESS_MESSAGE);
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
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.users', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0);
    }

    public function test_index_returns_full_json_structure(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create([
            'name' => 'Structure User',
            'code' => 'SU001',
        ]);
        $this->setUserScore($user, 250);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'users' => [
                        [
                            'id',
                            'name',
                            'code',
                            'score',
                            'current_level',
                            'achieved_levels',
                        ],
                    ],
                    'pagination' => [
                        'total',
                        'per_page',
                        'current_page',
                        'last_page',
                    ],
                ],
            ]);
    }

    public function test_index_orders_users_by_score_desc(): void
    {
        $this->actingAsSuperAdmin();

        $low = User::factory()->create(['name' => 'Low Score']);
        $mid = User::factory()->create(['name' => 'Mid Score']);
        $high = User::factory()->create(['name' => 'High Score']);

        $this->setUserScore($low, 10);
        $this->setUserScore($mid, 50);
        $this->setUserScore($high, 100);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $high->id)
            ->assertJsonPath('data.users.1.id', $mid->id)
            ->assertJsonPath('data.users.2.id', $low->id);
    }

    public function test_index_search_filters_by_name(): void
    {
        $this->actingAsSuperAdmin();

        $match = User::factory()->create(['name' => 'UniqueAlphaName', 'code' => 'X1']);
        $other = User::factory()->create(['name' => 'Other Person', 'code' => 'X2']);

        $this->setUserScore($match, 10);
        $this->setUserScore($other, 20);

        $this->getJson(self::INDEX_PATH.'?search=UniqueAlpha')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.users.0.id', $match->id);
    }

    public function test_index_search_filters_by_code(): void
    {
        $this->actingAsSuperAdmin();

        $match = User::factory()->create(['name' => 'Code Match User', 'code' => 'CODE-XYZ-99']);
        $other = User::factory()->create(['name' => 'Other User', 'code' => 'OTHER-11']);

        $this->setUserScore($match, 10);
        $this->setUserScore($other, 20);

        $this->getJson(self::INDEX_PATH.'?search=CODE-XYZ')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.users.0.id', $match->id);
    }

    public function test_index_respects_per_page_parameter(): void
    {
        $this->actingAsSuperAdmin();

        User::factory()->count(5)->create()->each(function (User $user, int $index) {
            $this->setUserScore($user, ($index + 1) * 10);
        });

        $this->getJson(self::INDEX_PATH.'?per_page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonCount(2, 'data.users');
    }

    public function test_index_falls_back_to_default_per_page_for_invalid_values(): void
    {
        $this->actingAsSuperAdmin();

        User::factory()->count(12)->create();

        $this->getJson(self::INDEX_PATH.'?per_page=0')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10);

        $this->getJson(self::INDEX_PATH.'?per_page=-5')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10);

        $this->getJson(self::INDEX_PATH.'?per_page=invalid')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10);
    }

    public function test_index_returns_current_level_and_achieved_levels_for_user_with_levels(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create(['name' => 'Leveled User', 'code' => 'LVL1']);
        $this->setUserScore($user, 300);

        $bronze = Level::factory()->withScore(100)->create([
            'name' => 'Bronze',
            'slug' => 'bronze',
        ]);
        $silver = Level::factory()->withScore(200)->create([
            'name' => 'Silver',
            'slug' => 'silver',
        ]);
        $gold = Level::factory()->withScore(300)->create([
            'name' => 'Gold',
            'slug' => 'gold',
        ]);

        $earlier = now()->subDays(2);
        $later = now()->subDay();

        $user->levels()->attach($bronze->id, [
            'created_at' => $earlier,
            'updated_at' => $earlier,
        ]);
        $user->levels()->attach($silver->id, [
            'created_at' => $later,
            'updated_at' => $later,
        ]);
        $user->levels()->attach($gold->id, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson(self::INDEX_PATH)->assertOk();
        $userPayload = collect($response->json('data.users'))->firstWhere('id', $user->id);

        $this->assertNotNull($userPayload);
        $this->assertSame(300, $userPayload['score']);
        $this->assertSame($gold->id, $userPayload['current_level']['id']);
        $this->assertSame('Gold', $userPayload['current_level']['name']);
        $this->assertSame('gold', $userPayload['current_level']['slug']);
        $this->assertSame(300, $userPayload['current_level']['score']);
        $this->assertCount(3, $userPayload['achieved_levels']);
        $this->assertSame($bronze->id, $userPayload['achieved_levels'][0]['id']);
        $this->assertSame($silver->id, $userPayload['achieved_levels'][1]['id']);
        $this->assertSame($gold->id, $userPayload['achieved_levels'][2]['id']);
        $this->assertNotNull($userPayload['achieved_levels'][0]['achieved_at']);
    }

    public function test_index_score_is_integer_in_response(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create();
        $this->setUserScore($user, 42);

        $response = $this->getJson(self::INDEX_PATH)->assertOk();
        $userPayload = $response->json('data.users.0');

        $this->assertSame(42, $userPayload['score']);
        $this->assertIsInt($userPayload['score']);
    }

    // -------------------------------------------------------------------------
    // SearchUsers
    // -------------------------------------------------------------------------

    public function test_search_users_validation_rejects_invalid_page(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::SEARCH_PATH.'?page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['page']);
    }

    public function test_search_users_validation_rejects_invalid_per_page(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::SEARCH_PATH.'?per_page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);

        $this->getJson(self::SEARCH_PATH.'?per_page=51')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_search_users_validation_rejects_search_longer_than_255_characters(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::SEARCH_PATH.'?search='.str_repeat('a', 256))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['search']);
    }

    public function test_search_users_returns_label_with_code_when_code_is_present(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create([
            'name' => 'Ali Rezaei',
            'code' => 'AR123',
        ]);

        $this->getJson(self::SEARCH_PATH.'?search=Ali')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SEARCH_SUCCESS_MESSAGE)
            ->assertJsonPath('data.options.0.value', $user->id)
            ->assertJsonPath('data.options.0.label', 'Ali Rezaei (AR123)');
    }

    public function test_search_users_returns_label_without_code_when_code_is_empty(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create([
            'name' => 'No Code User',
            'code' => null,
        ]);

        $this->getJson(self::SEARCH_PATH.'?search=No Code')
            ->assertOk()
            ->assertJsonPath('data.options.0.value', $user->id)
            ->assertJsonPath('data.options.0.label', 'No Code User');
    }

    public function test_search_users_filters_by_name_and_code(): void
    {
        $this->actingAsSuperAdmin();

        $byName = User::factory()->create(['name' => 'SearchableNameUser', 'code' => 'SN1']);
        User::factory()->create(['name' => 'Unrelated', 'code' => 'UN1']);

        $this->getJson(self::SEARCH_PATH.'?search=SearchableName')
            ->assertOk()
            ->assertJsonPath('data.pagination.more', false)
            ->assertJsonCount(1, 'data.options')
            ->assertJsonPath('data.options.0.value', $byName->id);

        $byCode = User::factory()->create(['name' => 'Another', 'code' => 'FIND-ME-42']);

        $this->getJson(self::SEARCH_PATH.'?search=FIND-ME')
            ->assertOk()
            ->assertJsonCount(1, 'data.options')
            ->assertJsonPath('data.options.0.value', $byCode->id);
    }

    public function test_search_users_pagination_more_is_true_when_additional_pages_exist(): void
    {
        $this->actingAsSuperAdmin();

        User::factory()->count(11)->create();

        $this->getJson(self::SEARCH_PATH.'?per_page=10&page=1')
            ->assertOk()
            ->assertJsonPath('data.pagination.more', true)
            ->assertJsonCount(10, 'data.options');
    }

    public function test_search_users_pagination_more_is_false_on_last_page(): void
    {
        $this->actingAsSuperAdmin();

        User::factory()->count(11)->create();

        $this->getJson(self::SEARCH_PATH.'?per_page=10&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.more', false)
            ->assertJsonCount(1, 'data.options');
    }

    public function test_search_users_page_parameter_returns_expected_slice(): void
    {
        $this->actingAsSuperAdmin();

        foreach (range(1, 15) as $number) {
            User::factory()->create(['name' => "Paged User {$number}"]);
        }

        $pageOne = $this->getJson(self::SEARCH_PATH.'?per_page=5&page=1')->assertOk();
        $pageTwo = $this->getJson(self::SEARCH_PATH.'?per_page=5&page=2')->assertOk();

        $pageOneIds = collect($pageOne->json('data.options'))->pluck('value')->all();
        $pageTwoIds = collect($pageTwo->json('data.options'))->pluck('value')->all();

        $this->assertCount(5, $pageOneIds);
        $this->assertCount(5, $pageTwoIds);
        $this->assertEmpty(array_intersect($pageOneIds, $pageTwoIds));
    }

    public function test_search_users_returns_full_json_structure(): void
    {
        $this->actingAsSuperAdmin();

        User::factory()->create(['name' => 'Structure Search User']);

        $this->getJson(self::SEARCH_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'options' => [
                        [
                            'value',
                            'label',
                        ],
                    ],
                    'pagination' => [
                        'more',
                    ],
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // Promote — validation
    // -------------------------------------------------------------------------

    public function test_promote_validation_requires_user_id(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::PROMOTE_PATH, ['score' => 10])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id']);
    }

    public function test_promote_validation_rejects_invalid_user_id(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::PROMOTE_PATH, [
            'user_id' => 999999,
            'score' => 10,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id']);
    }

    public function test_promote_validation_requires_score(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create();

        $this->postJson(self::PROMOTE_PATH, ['user_id' => $user->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['score']);
    }

    public function test_promote_validation_rejects_score_less_than_one(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create();

        $this->postJson(self::PROMOTE_PATH, [
            'user_id' => $user->id,
            'score' => 0,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['score']);
    }

    public function test_promote_validation_rejects_non_integer_score(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create();

        $this->postJson(self::PROMOTE_PATH, [
            'user_id' => $user->id,
            'score' => 'not-a-number',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['score']);
    }

    // -------------------------------------------------------------------------
    // Promote — behavior
    // -------------------------------------------------------------------------

    public function test_promote_increments_user_score(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create();
        $this->setUserScore($user, 40);

        $this->postJson(self::PROMOTE_PATH, [
            'user_id' => $user->id,
            'score' => 25,
        ])
            ->assertOk()
            ->assertJsonPath('data.user.score', 65);

        $this->assertSame(65, (int) DB::table('users')->where('id', $user->id)->value('score'));
    }

    public function test_promote_attaches_first_level_when_user_has_no_levels_and_score_qualifies(): void
    {
        $this->actingAsSuperAdmin();

        Level::factory()->withScore(200)->create(['name' => 'Too High', 'slug' => 'too-high']);
        $starter = Level::factory()->withScore(100)->create(['name' => 'Starter', 'slug' => 'starter']);

        $user = User::factory()->create();
        $this->setUserScore($user, 0);

        $this->postJson(self::PROMOTE_PATH, [
            'user_id' => $user->id,
            'score' => 100,
        ])
            ->assertOk()
            ->assertJsonPath('data.user.current_level.id', $starter->id)
            ->assertJsonPath('data.user.achieved_levels.0.id', $starter->id);

        $this->assertDatabaseHas('level_user', [
            'user_id' => $user->id,
            'level_id' => $starter->id,
        ]);
        $this->assertDatabaseCount('level_user', 1);
    }

    public function test_promote_attaches_next_level_when_promotion_crosses_threshold(): void
    {
        $this->actingAsSuperAdmin();

        $bronze = Level::factory()->withScore(100)->create(['name' => 'Bronze', 'slug' => 'bronze']);
        $silver = Level::factory()->withScore(200)->create(['name' => 'Silver', 'slug' => 'silver']);

        $user = User::factory()->create();
        $this->setUserScore($user, 100);
        $user->levels()->attach($bronze->id, [
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        $this->postJson(self::PROMOTE_PATH, [
            'user_id' => $user->id,
            'score' => 100,
        ])
            ->assertOk()
            ->assertJsonPath('data.user.score', 200)
            ->assertJsonPath('data.user.current_level.id', $silver->id);

        $this->assertDatabaseHas('level_user', [
            'user_id' => $user->id,
            'level_id' => $silver->id,
        ]);
        $this->assertSame(2, DB::table('level_user')->where('user_id', $user->id)->count());
    }

    public function test_promote_skips_duplicate_level_attachment(): void
    {
        $this->actingAsSuperAdmin();

        $bronze = Level::factory()->withScore(100)->create(['name' => 'Bronze', 'slug' => 'bronze']);
        Level::factory()->withScore(500)->create(['name' => 'Platinum', 'slug' => 'platinum']);

        $user = User::factory()->create();
        $this->setUserScore($user, 100);
        $user->levels()->attach($bronze->id);

        $response = $this->postJson(self::PROMOTE_PATH, [
            'user_id' => $user->id,
            'score' => 10,
        ])
            ->assertOk()
            ->assertJsonPath('data.user.score', 110)
            ->assertJsonPath('data.user.current_level.id', $bronze->id);

        $this->assertCount(1, $response->json('data.user.achieved_levels'));
        $this->assertSame(1, DB::table('level_user')->where('user_id', $user->id)->count());
        $this->assertDatabaseHas('level_user', [
            'user_id' => $user->id,
            'level_id' => $bronze->id,
        ]);
    }

    public function test_promote_can_attach_multiple_levels_in_one_promotion_when_score_jump_is_large(): void
    {
        $this->actingAsSuperAdmin();

        $level100 = Level::factory()->withScore(100)->create(['name' => 'L100', 'slug' => 'l100']);
        $level200 = Level::factory()->withScore(200)->create(['name' => 'L200', 'slug' => 'l200']);
        $level300 = Level::factory()->withScore(300)->create(['name' => 'L300', 'slug' => 'l300']);

        $user = User::factory()->create();
        $this->setUserScore($user, 0);

        $response = $this->postJson(self::PROMOTE_PATH, [
            'user_id' => $user->id,
            'score' => 300,
        ])
            ->assertOk()
            ->assertJsonPath('data.user.score', 300)
            ->assertJsonCount(3, 'data.user.achieved_levels');

        $achievedIds = collect($response->json('data.user.achieved_levels'))->pluck('id')->all();
        $this->assertSame([$level100->id, $level200->id, $level300->id], $achievedIds);

        $attachedLevelIds = DB::table('level_user')
            ->where('user_id', $user->id)
            ->pluck('level_id')
            ->sort()
            ->values()
            ->all();

        $this->assertSame(
            [$level100->id, $level200->id, $level300->id],
            $attachedLevelIds
        );
    }

    public function test_promote_returns_updated_user_resource_structure(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->withScore(50)->create(['name' => 'Rookie', 'slug' => 'rookie']);
        $user = User::factory()->create(['name' => 'Promoted User', 'code' => 'PU99']);
        $this->setUserScore($user, 0);

        $this->postJson(self::PROMOTE_PATH, [
            'user_id' => $user->id,
            'score' => 50,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::PROMOTE_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'code',
                        'score',
                        'current_level' => [
                            'id',
                            'name',
                            'slug',
                            'score',
                        ],
                        'achieved_levels' => [
                            [
                                'id',
                                'name',
                                'slug',
                                'score',
                                'achieved_at',
                            ],
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.name', 'Promoted User')
            ->assertJsonPath('data.user.code', 'PU99')
            ->assertJsonPath('data.user.current_level.id', $level->id);
    }

    public function test_promote_returns_500_when_persist_fails(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create(['name' => 'Fail Promote', 'code' => 'FP01']);
        $this->setUserScore($user, 5);

        User::updating(function () {
            throw new \RuntimeException('forced promote failure');
        });

        $this->postJson(self::PROMOTE_PATH, [
            'user_id' => $user->id,
            'score' => 10,
        ])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در ارتقاء سطح کاربر');
    }

    private function setUserScore(User $user, int $score): void
    {
        DB::table('users')->where('id', $user->id)->update(['score' => $score]);
    }
}

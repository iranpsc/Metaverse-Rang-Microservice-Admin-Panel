<?php

namespace Tests\Feature\Profile;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesProfileDetailsApiSchema;
use Tests\TestCase;

class ProfileDetailsApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesProfileDetailsApiSchema;

    private const INDEX_PATH = '/api/profile-details';

    private const SUCCESS_MESSAGE = 'Profile details retrieved successfully.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpProfileDetailsApiSchema();
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

    public function test_authenticated_super_admin_receives_success_envelope(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_receives_success_envelope(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Happy path / structure
    // -------------------------------------------------------------------------

    public function test_empty_dataset_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE)
            ->assertJsonPath('data.users', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Profile Detail User']);
        $this->createUserActivity($user, 100);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('message', self::SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'users' => [
                        [
                            'id',
                            'code',
                            'created_at',
                            'activities_sum_total',
                            'followers_count',
                            'payments_count',
                            'more_than_a_million_payment',
                            'score',
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
    }

    // -------------------------------------------------------------------------
    // Data mapping
    // -------------------------------------------------------------------------

    public function test_user_code_falls_back_to_dash_when_null(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['code' => null]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id)
            ->assertJsonPath('data.users.0.code', '-');
    }

    public function test_user_code_is_returned_when_present(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['code' => 'USR-1234']);

        $this->getJson(self::INDEX_PATH.'?search=USR-1234')
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id)
            ->assertJsonPath('data.users.0.code', 'USR-1234');
    }

    public function test_score_is_formatted_with_number_format(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Scored User']);
        $this->setUserScore($user, 1234567);

        $this->getJson(self::INDEX_PATH.'?search=Scored User')
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id)
            ->assertJsonPath('data.users.0.score', '1,234,567');
    }

    public function test_activities_sum_total_is_formatted_with_number_format(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Active User']);
        $this->createUserActivity($user, 1500);
        $this->createUserActivity($user, 2500);

        $this->getJson(self::INDEX_PATH.'?search=Active User')
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id)
            ->assertJsonPath('data.users.0.activities_sum_total', '4,000');
    }

    public function test_jalali_date_formatting_for_created_at(): void
    {
        $this->actingAsSuperAdmin();

        $createdAt = Carbon::parse('2024-03-20 14:30:45');
        Carbon::setTestNow($createdAt);

        $user = $this->createUser(['name' => 'Jalali User']);
        $jalali = Jalalian::fromDateTime($createdAt);

        $this->getJson(self::INDEX_PATH.'?search=Jalali User')
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id)
            ->assertJsonPath('data.users.0.created_at', $jalali->format('Y/m/d H:i:s'));
    }

    public function test_created_at_falls_back_to_dash_when_null(): void
    {
        $this->actingAsSuperAdmin();

        $userId = DB::table('users')->insertGetId([
            'name' => 'No Timestamp User',
            'email' => Str::uuid().'@example.com',
            'password' => 'secret',
            'ip' => '127.0.0.1',
            'score' => 0,
            'created_at' => null,
            'updated_at' => null,
        ]);

        $this->getJson(self::INDEX_PATH.'?search=No Timestamp User')
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $userId)
            ->assertJsonPath('data.users.0.created_at', '-');
    }

    // -------------------------------------------------------------------------
    // Aggregations
    // -------------------------------------------------------------------------

    public function test_followers_count_is_calculated_correctly(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Popular User']);
        $followerOne = $this->createUser(['name' => 'Follower One']);
        $followerTwo = $this->createUser(['name' => 'Follower Two']);

        $this->createFollow($followerOne->id, $user->id);
        $this->createFollow($followerTwo->id, $user->id);

        $this->getJson(self::INDEX_PATH.'?search=Popular User')
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id)
            ->assertJsonPath('data.users.0.followers_count', 2);
    }

    public function test_payments_count_is_calculated_correctly(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Paying User']);
        $this->createPayment($user, ['amount' => 1000]);
        $this->createPayment($user, ['amount' => 2000]);
        $this->createPayment($user, ['amount' => 3000]);

        $this->getJson(self::INDEX_PATH.'?search=Paying User')
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id)
            ->assertJsonPath('data.users.0.payments_count', 3);
    }

    public function test_more_than_a_million_payment_counts_only_amounts_above_threshold(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'High Roller']);
        $this->createPayment($user, ['amount' => 10000000, 'ref_id' => 'AT-THRESHOLD']);
        $this->createPayment($user, ['amount' => 10000001, 'ref_id' => 'ABOVE-THRESHOLD-1']);
        $this->createPayment($user, ['amount' => 15000000, 'ref_id' => 'ABOVE-THRESHOLD-2']);
        $this->createPayment($user, ['amount' => 5000000, 'ref_id' => 'BELOW-THRESHOLD']);

        $this->getJson(self::INDEX_PATH.'?search=High Roller')
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id)
            ->assertJsonPath('data.users.0.payments_count', 4)
            ->assertJsonPath('data.users.0.more_than_a_million_payment', 2);
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_default_per_page_is_ten(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 12; $i++) {
            $this->createUser(['name' => 'Paginated User '.$i]);
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 10)
            ->assertJsonCount(10, 'data.users');
    }

    public function test_custom_per_page_is_respected(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 8; $i++) {
            $this->createUser(['name' => 'Custom Page User '.$i]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=5')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 5)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.total', 8)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 5)
            ->assertJsonCount(5, 'data.users');
    }

    public function test_page_two_returns_correct_slice_and_meta(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 15; $i++) {
            $this->createUser(['name' => 'Page Two User '.$i]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=5&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 5)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 15)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonPath('data.pagination.from', 6)
            ->assertJsonPath('data.pagination.to', 10)
            ->assertJsonCount(5, 'data.users');
    }

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    public function test_search_filters_by_name_partial_match(): void
    {
        $this->actingAsSuperAdmin();

        $match = $this->createUser(['name' => 'Unique Profile Name']);
        $this->createUser(['name' => 'Other Person']);

        $this->getJson(self::INDEX_PATH.'?search=Unique Profile')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonCount(1, 'data.users')
            ->assertJsonPath('data.users.0.id', $match->id);
    }

    public function test_search_filters_by_email_partial_match(): void
    {
        $this->actingAsSuperAdmin();

        $email = 'searchable-email-'.Str::random(6).'@example.com';
        $match = $this->createUser(['email' => $email]);
        $this->createUser();

        $this->getJson(self::INDEX_PATH.'?search='.urlencode('searchable-email'))
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonCount(1, 'data.users')
            ->assertJsonPath('data.users.0.id', $match->id);
    }

    public function test_search_filters_by_code_partial_match(): void
    {
        $this->actingAsSuperAdmin();

        $match = $this->createUser(['code' => 'CODE-98765']);
        $this->createUser(['code' => 'CODE-00000']);

        $this->getJson(self::INDEX_PATH.'?search=98765')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonCount(1, 'data.users')
            ->assertJsonPath('data.users.0.id', $match->id);
    }

    public function test_empty_search_returns_all_users(): void
    {
        $this->actingAsSuperAdmin();

        $this->createUser(['name' => 'User Alpha']);
        $this->createUser(['name' => 'User Beta']);

        $this->getJson(self::INDEX_PATH.'?search=')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2)
            ->assertJsonCount(2, 'data.users');
    }

    public function test_non_matching_search_returns_empty_users(): void
    {
        $this->actingAsSuperAdmin();

        $this->createUser(['name' => 'Existing User']);

        $this->getJson(self::INDEX_PATH.'?search=DOES-NOT-EXIST')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.users', [])
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Profile User '.Str::random(5),
            'email' => Str::uuid().'@example.com',
            'code' => (string) random_int(1000, 9999),
            'password' => 'secret',
            'ip' => '127.0.0.1',
        ], $overrides));
    }

    private function setUserScore(User $user, int $score): void
    {
        DB::table('users')->where('id', $user->id)->update(['score' => $score]);
    }

    private function createUserActivity(User $user, float $total): void
    {
        DB::table('user_activities')->insert([
            'user_id' => $user->id,
            'total' => $total,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createFollow(int $followerId, int $followingId): void
    {
        DB::table('follows')->insert([
            'follower_id' => $followerId,
            'following_id' => $followingId,
        ]);
    }

    private function createPayment(User $user, array $overrides = []): Payment
    {
        return Payment::create(array_merge([
            'user_id' => $user->id,
            'ref_id' => 'REF-'.Str::upper(Str::random(8)),
            'product' => 'irr',
            'amount' => 1000,
            'status' => 'success',
        ], $overrides));
    }
}

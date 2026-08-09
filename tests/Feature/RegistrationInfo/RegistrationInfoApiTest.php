<?php

namespace Tests\Feature\RegistrationInfo;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesCitizensApiSchema;
use Tests\TestCase;

class RegistrationInfoApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesCitizensApiSchema;

    private const INDEX_PATH = '/api/registration-info';

    private const SUCCESS_MESSAGE = 'Registration information retrieved successfully.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpCitizensApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_request_returns_unauthorized(): void
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

        $this->createUser([
            'name' => 'Registered User',
            'email' => 'registered@example.com',
            'ip' => '10.0.0.1',
            'email_verified_at' => now(),
        ]);

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
                            'email',
                            'email_verified_at',
                            'ip',
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
    // Filtering & search
    // -------------------------------------------------------------------------

    public function test_returns_all_users_with_or_without_wallet(): void
    {
        $this->actingAsSuperAdmin();

        $withWallet = $this->createUser([
            'name' => 'With Wallet',
            'wallet_address' => '0xabc',
        ]);
        $withoutWallet = $this->createUser([
            'name' => 'Without Wallet',
            'email' => 'without@example.com',
            'code' => '2002',
            'wallet_address' => null,
        ]);

        $response = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);

        $ids = collect($response->json('data.users'))->pluck('id')->all();
        $this->assertContains($withWallet->id, $ids);
        $this->assertContains($withoutWallet->id, $ids);
    }

    public function test_search_by_email(): void
    {
        $this->actingAsSuperAdmin();

        $this->createUser([
            'name' => 'Email Match',
            'email' => 'unique.needle@example.com',
        ]);
        $this->createUser([
            'name' => 'Email Miss',
            'email' => 'other@example.com',
            'code' => '3003',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=unique.needle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.users.0.email', 'unique.needle@example.com');
    }

    public function test_search_by_name(): void
    {
        $this->actingAsSuperAdmin();

        $this->createUser([
            'name' => 'Searchable Name',
            'email' => 'name1@example.com',
        ]);
        $this->createUser([
            'name' => 'Different Person',
            'email' => 'name2@example.com',
            'code' => '4004',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=Searchable')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.users.0.name', 'Searchable Name');
    }

    public function test_non_matching_search_returns_empty(): void
    {
        $this->actingAsSuperAdmin();

        $this->createUser([
            'name' => 'Exists',
            'email' => 'exists@example.com',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=does-not-match-anything')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.users', [])
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_default_per_page_is_ten(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 12; $i++) {
            $this->createUser([
                'email' => "user{$i}@example.com",
                'code' => (string) (1000 + $i),
                'name' => "User {$i}",
            ]);
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

    public function test_custom_per_page_and_page_work(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 5; $i++) {
            $this->createUser([
                'email' => "page{$i}@example.com",
                'code' => (string) (2000 + $i),
                'name' => "Page {$i}",
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonPath('data.pagination.from', 3)
            ->assertJsonPath('data.pagination.to', 4)
            ->assertJsonCount(2, 'data.users');
    }

    // -------------------------------------------------------------------------
    // Resource shaping
    // -------------------------------------------------------------------------

    public function test_email_verified_at_is_null_when_unverified(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser([
            'name' => 'Unverified',
            'email' => 'unverified@example.com',
            'email_verified_at' => null,
            'ip' => '192.168.1.10',
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id)
            ->assertJsonPath('data.users.0.email_verified_at', null)
            ->assertJsonPath('data.users.0.ip', '192.168.1.10');
    }

    public function test_email_verified_at_is_jalali_when_verified(): void
    {
        $this->actingAsSuperAdmin();

        $verifiedAt = Carbon::parse('2024-06-15 10:00:00');
        $user = $this->createUser([
            'name' => 'Verified',
            'email' => 'verified@example.com',
            'email_verified_at' => $verifiedAt,
            'ip' => '10.0.0.5',
        ]);

        $expected = jdate($verifiedAt)->format('Y/m/d');

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id)
            ->assertJsonPath('data.users.0.email_verified_at', $expected)
            ->assertJsonPath('data.users.0.ip', '10.0.0.5');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Citizen '.Str::random(5),
            'email' => Str::uuid().'@example.com',
            'code' => (string) random_int(1000, 9999),
            'password' => 'secret',
            'ip' => '127.0.0.1',
        ], $overrides));
    }
}

<?php

namespace Tests\Feature\ConnectedWallet;

use App\Models\User;
use Illuminate\Support\Str;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesCitizensApiSchema;
use Tests\TestCase;

class ConnectedWalletApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesCitizensApiSchema;

    private const INDEX_PATH = '/api/connected-wallets';

    private const SUCCESS_MESSAGE = 'Connected wallet users retrieved successfully.';

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
            'name' => 'Wallet User',
            'wallet_address' => '0xabc123',
            'code' => '1001',
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
                            'code',
                            'wallet_address',
                            'registered_at',
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

    public function test_only_users_with_wallet_address_appear(): void
    {
        $this->actingAsSuperAdmin();

        $withWallet = $this->createUser([
            'name' => 'Has Wallet',
            'wallet_address' => '0xwallet1',
        ]);
        $this->createUser([
            'name' => 'No Wallet',
            'email' => 'nowallet@example.com',
            'code' => '2002',
            'wallet_address' => null,
        ]);

        $response = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonCount(1, 'data.users');

        $this->assertSame($withWallet->id, $response->json('data.users.0.id'));
        $this->assertSame('0xwallet1', $response->json('data.users.0.wallet_address'));
    }

    public function test_users_with_null_wallet_address_are_excluded(): void
    {
        $this->actingAsSuperAdmin();

        $this->createUser(['wallet_address' => null, 'name' => 'Null Wallet']);
        $this->createUser([
            'email' => 'has@example.com',
            'code' => '3003',
            'wallet_address' => '0xpresent',
        ]);

        $ids = collect($this->getJson(self::INDEX_PATH)->json('data.users'))->pluck('id');

        $this->assertCount(1, $ids);
        $this->assertFalse(
            User::whereNull('wallet_address')->pluck('id')->intersect($ids)->isNotEmpty()
        );
    }

    public function test_search_by_name(): void
    {
        $this->actingAsSuperAdmin();

        $this->createUser([
            'name' => 'Ali Wallet',
            'wallet_address' => '0xali',
        ]);
        $this->createUser([
            'name' => 'Other Person',
            'email' => 'other@example.com',
            'code' => '4004',
            'wallet_address' => '0xother',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=Ali')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.users.0.name', 'Ali Wallet');
    }

    public function test_search_by_code(): void
    {
        $this->actingAsSuperAdmin();

        $this->createUser([
            'name' => 'Code Match',
            'code' => '5555',
            'wallet_address' => '0xcode',
        ]);
        $this->createUser([
            'name' => 'Code Miss',
            'email' => 'miss@example.com',
            'code' => '6666',
            'wallet_address' => '0xmiss',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=5555')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.users.0.code', '5555');
    }

    public function test_search_by_wallet_address(): void
    {
        $this->actingAsSuperAdmin();

        $this->createUser([
            'name' => 'Wallet Match',
            'wallet_address' => '0xUniqueWalletNeedle',
        ]);
        $this->createUser([
            'name' => 'Wallet Miss',
            'email' => 'wmiss@example.com',
            'code' => '7007',
            'wallet_address' => '0xzzzz',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=UniqueWalletNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.users.0.wallet_address', '0xUniqueWalletNeedle');
    }

    public function test_empty_search_returns_all_wallet_users(): void
    {
        $this->actingAsSuperAdmin();

        $this->createUser(['wallet_address' => '0xone', 'name' => 'One']);
        $this->createUser([
            'email' => 'two@example.com',
            'code' => '8008',
            'wallet_address' => '0xtwo',
            'name' => 'Two',
        ]);
        $this->createUser([
            'email' => 'three@example.com',
            'code' => '9009',
            'wallet_address' => null,
            'name' => 'Three',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2)
            ->assertJsonCount(2, 'data.users');
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_default_per_page_is_ten(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 12; $i++) {
            $this->createUser([
                'email' => "wallet{$i}@example.com",
                'code' => (string) (1000 + $i),
                'wallet_address' => "0xwallet{$i}",
                'name' => "Wallet {$i}",
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
                'wallet_address' => "0xpage{$i}",
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

    public function test_null_code_becomes_dash_and_registered_at_is_formatted(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser([
            'name' => 'Null Code',
            'code' => null,
            'wallet_address' => '0xnullcode',
        ]);

        $response = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $user->id)
            ->assertJsonPath('data.users.0.code', '-')
            ->assertJsonPath('data.users.0.wallet_address', '0xnullcode');

        $registeredAt = $response->json('data.users.0.registered_at');
        $this->assertNotSame('-', $registeredAt);
        $this->assertMatchesRegularExpression('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}$/', $registeredAt);
    }

    // -------------------------------------------------------------------------
    // Ordering
    // -------------------------------------------------------------------------

    public function test_connected_wallets_are_ordered_by_created_at_desc(): void
    {
        $this->actingAsSuperAdmin();

        $older = $this->createUser([
            'name' => 'Older Wallet',
            'wallet_address' => '0xolder',
        ]);
        $older->forceFill(['created_at' => now()->subDays(2)])->save();

        $newer = $this->createUser([
            'name' => 'Newer Wallet',
            'email' => 'newer@example.com',
            'code' => '1112',
            'wallet_address' => '0xnewer',
        ]);
        $newer->forceFill(['created_at' => now()->subHour()])->save();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.users.0.id', $newer->id)
            ->assertJsonPath('data.users.1.id', $older->id);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Wallet Citizen '.Str::random(5),
            'email' => Str::uuid().'@example.com',
            'code' => (string) random_int(1000, 9999),
            'password' => 'secret',
            'ip' => '127.0.0.1',
            'wallet_address' => '0x'.Str::random(20),
        ], $overrides));
    }
}

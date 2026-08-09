<?php

namespace Tests\Feature\Wallet;

use App\Models\Feature;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesWalletApiSchema;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesWalletApiSchema;

    private const INDEX_PATH = '/api/assets';

    private const SUCCESS_MESSAGE = 'Assets retrieved successfully.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpWalletApiSchema();
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
            ->assertJsonPath('data.assets', [])
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

        $user = $this->createUser(['name' => 'Asset Owner']);
        $this->createWallet($user);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('message', self::SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'assets' => [
                        [
                            'id',
                            'user_name',
                            'psc',
                            'blue',
                            'red',
                            'yellow',
                            'irr',
                            'features_count',
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

    public function test_returns_correct_user_name_from_related_user(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Ali Wallet Owner']);
        $wallet = $this->createWallet($user);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.assets.0.id', $wallet->id)
            ->assertJsonPath('data.assets.0.user_name', 'Ali Wallet Owner');
    }

    public function test_missing_user_relation_returns_dash_for_user_name(): void
    {
        $this->actingAsSuperAdmin();

        $walletId = DB::table('wallets')->insertGetId([
            'user_id' => 999999,
            'psc' => 0,
            'blue' => 0,
            'red' => 0,
            'yellow' => 0,
            'irr' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.assets.0.id', $walletId)
            ->assertJsonPath('data.assets.0.user_name', '-')
            ->assertJsonPath('data.assets.0.features_count', 0);
    }

    public function test_features_count_is_zero_when_user_has_no_features(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'No Features']);
        $this->createWallet($user);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.assets.0.features_count', 0);
    }

    public function test_features_count_equals_one_when_user_has_one_feature(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'One Feature']);
        $this->createWallet($user);
        $this->createFeature($user);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.assets.0.features_count', 1);
    }

    public function test_features_count_equals_number_of_owned_features(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Multi Feature']);
        $this->createWallet($user);
        $this->createFeature($user, ['type' => 'land']);
        $this->createFeature($user, ['type' => 'building']);
        $this->createFeature($user, ['type' => 'road']);

        $other = $this->createUser([
            'name' => 'Other Owner',
            'email' => 'other-owner@example.com',
            'code' => '8888',
        ]);
        $this->createFeature($other);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.assets.0.features_count', 3);
    }

    // -------------------------------------------------------------------------
    // Formatting (number_format)
    // -------------------------------------------------------------------------

    public function test_large_numbers_are_thousand_separated_strings(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Formatted Assets']);
        $this->createWallet($user, [
            'psc' => 1000,
            'blue' => 1234567,
            'red' => 1000000,
            'yellow' => 9999,
            'irr' => 50000,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.assets.0.psc', '1,000')
            ->assertJsonPath('data.assets.0.blue', '1,234,567')
            ->assertJsonPath('data.assets.0.red', '1,000,000')
            ->assertJsonPath('data.assets.0.yellow', '9,999')
            ->assertJsonPath('data.assets.0.irr', '50,000');
    }

    public function test_null_asset_columns_format_as_zero_string(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Null Assets']);
        $this->createWallet($user, [
            'psc' => null,
            'blue' => null,
            'red' => null,
            'yellow' => null,
            'irr' => null,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.assets.0.psc', '0')
            ->assertJsonPath('data.assets.0.blue', '0')
            ->assertJsonPath('data.assets.0.red', '0')
            ->assertJsonPath('data.assets.0.yellow', '0')
            ->assertJsonPath('data.assets.0.irr', '0');
    }

    public function test_zero_asset_values_format_as_zero_string(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Zero Assets']);
        $this->createWallet($user, [
            'psc' => 0,
            'blue' => 0,
            'red' => 0,
            'yellow' => 0,
            'irr' => 0,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.assets.0.psc', '0')
            ->assertJsonPath('data.assets.0.blue', '0')
            ->assertJsonPath('data.assets.0.red', '0')
            ->assertJsonPath('data.assets.0.yellow', '0')
            ->assertJsonPath('data.assets.0.irr', '0');
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_default_per_page_is_ten(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 12; $i++) {
            $user = $this->createUser([
                'email' => "wallet{$i}@example.com",
                'code' => (string) (1000 + $i),
                'name' => "Wallet Owner {$i}",
            ]);
            $this->createWallet($user);
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 10)
            ->assertJsonCount(10, 'data.assets');
    }

    public function test_custom_per_page_is_respected(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 8; $i++) {
            $user = $this->createUser([
                'email' => "custom{$i}@example.com",
                'code' => (string) (2000 + $i),
                'name' => "Custom {$i}",
            ]);
            $this->createWallet($user);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=5')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 5)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.total', 8)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 5)
            ->assertJsonCount(5, 'data.assets');
    }

    public function test_page_two_returns_correct_slice_and_meta(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 15; $i++) {
            $user = $this->createUser([
                'email' => "page{$i}@example.com",
                'code' => (string) (3000 + $i),
                'name' => "Page Owner {$i}",
            ]);
            $this->createWallet($user);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=5&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 5)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 15)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonPath('data.pagination.from', 6)
            ->assertJsonPath('data.pagination.to', 10)
            ->assertJsonCount(5, 'data.assets');
    }

    // -------------------------------------------------------------------------
    // Isolation / correctness
    // -------------------------------------------------------------------------

    public function test_only_wallet_records_appear_not_users_without_wallets(): void
    {
        $this->actingAsSuperAdmin();

        $withWallet = $this->createUser(['name' => 'Has Wallet']);
        $wallet = $this->createWallet($withWallet);

        $this->createUser([
            'name' => 'No Wallet',
            'email' => 'nowallet@example.com',
            'code' => '4444',
        ]);

        $response = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonCount(1, 'data.assets');

        $this->assertSame($wallet->id, $response->json('data.assets.0.id'));
        $this->assertSame('Has Wallet', $response->json('data.assets.0.user_name'));
    }

    public function test_multiple_wallets_for_different_users_appear_with_correct_names_and_counts(): void
    {
        $this->actingAsSuperAdmin();

        $alice = $this->createUser(['name' => 'Alice', 'email' => 'alice@example.com', 'code' => '1111']);
        $bob = $this->createUser(['name' => 'Bob', 'email' => 'bob@example.com', 'code' => '2222']);
        $carol = $this->createUser(['name' => 'Carol', 'email' => 'carol@example.com', 'code' => '3333']);

        $aliceWallet = $this->createWallet($alice, ['psc' => 10]);
        $bobWallet = $this->createWallet($bob, ['psc' => 20]);
        $carolWallet = $this->createWallet($carol, ['psc' => 30]);

        $this->createFeature($alice);
        $this->createFeature($bob);
        $this->createFeature($bob, ['type' => 'building']);

        $assets = collect($this->getJson(self::INDEX_PATH)->assertOk()->json('data.assets'))
            ->keyBy('id');

        $this->assertCount(3, $assets);
        $this->assertSame('Alice', $assets[$aliceWallet->id]['user_name']);
        $this->assertSame(1, $assets[$aliceWallet->id]['features_count']);
        $this->assertSame('Bob', $assets[$bobWallet->id]['user_name']);
        $this->assertSame(2, $assets[$bobWallet->id]['features_count']);
        $this->assertSame('Carol', $assets[$carolWallet->id]['user_name']);
        $this->assertSame(0, $assets[$carolWallet->id]['features_count']);
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
        ], $overrides));
    }

    private function createWallet(User $user, array $overrides = []): Wallet
    {
        $attributes = array_merge([
            'user_id' => $user->id,
            'psc' => 0,
            'blue' => 0,
            'red' => 0,
            'irr' => 0,
            'green' => 0,
        ], $overrides);

        // `yellow` is read by the controller but is not currently fillable on Wallet.
        $yellow = array_key_exists('yellow', $attributes) ? $attributes['yellow'] : 0;
        unset($attributes['yellow']);

        $wallet = Wallet::create($attributes);
        $wallet->forceFill(['yellow' => $yellow])->save();

        return $wallet->fresh();
    }

    private function createFeature(User $user, array $overrides = []): Feature
    {
        return Feature::create(array_merge([
            'map_id' => 0,
            'type' => 'land',
            'owner_id' => $user->id,
        ], $overrides));
    }
}

<?php

namespace Tests\Feature\Dashboard;

use App\Models\Dynasty\Dynasty;
use App\Models\Feature;
use App\Models\Kyc;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\ReferralOrderHistory;
use App\Models\User;
use App\Models\Variable;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesDashboardSchema;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesDashboardSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDashboardSchema();
    }

    public function test_unauthenticated_request_returns_unauthorized(): void
    {
        $this->getJson('/api/dashboard')
            ->assertUnauthorized();
    }

    public function test_authenticated_admin_receives_full_json_structure(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'users' => [
                        'all',
                        'verified',
                        'verified_phone',
                        'kyc_verified',
                    ],
                    'dynasties',
                    'features' => [
                        'all',
                        'sold',
                    ],
                    'referrals',
                    'referral_amount',
                    'sold_assets' => [
                        'psc',
                        'red',
                        'blue',
                        'yellow',
                    ],
                    'deposited_rial_amount',
                ],
            ]);
    }

    public function test_empty_database_returns_zeros_with_success_envelope(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Dashboard statistics retrieved successfully.',
                'data' => [
                    'users' => [
                        'all' => 0,
                        'verified' => 0,
                        'verified_phone' => 0,
                        'kyc_verified' => 0,
                    ],
                    'dynasties' => 0,
                    'features' => [
                        'all' => 0,
                        'sold' => 0,
                    ],
                    'referrals' => 0,
                    'referral_amount' => 0.0,
                    'sold_assets' => [
                        'psc' => 0.0,
                        'red' => 0.0,
                        'blue' => 0.0,
                        'yellow' => 0.0,
                    ],
                    'deposited_rial_amount' => 0.0,
                ],
            ]);

        $data = $response->json('data');

        $this->assertIsInt($data['users']['all']);
        $this->assertIsInt($data['users']['verified']);
        $this->assertIsInt($data['users']['verified_phone']);
        $this->assertIsInt($data['users']['kyc_verified']);
        $this->assertIsInt($data['dynasties']);
        $this->assertIsInt($data['features']['all']);
        $this->assertIsInt($data['features']['sold']);
        $this->assertIsInt($data['referrals']);
        // JSON encodes whole-number floats as ints; assert numeric money fields.
        $this->assertIsNumeric($data['referral_amount']);
        $this->assertIsNumeric($data['sold_assets']['psc']);
        $this->assertIsNumeric($data['sold_assets']['red']);
        $this->assertIsNumeric($data['sold_assets']['blue']);
        $this->assertIsNumeric($data['sold_assets']['yellow']);
        $this->assertIsNumeric($data['deposited_rial_amount']);
    }

    public function test_seeded_data_returns_exact_computed_statistics(): void
    {
        $this->actingAsSuperAdmin();
        $this->seedDashboardFixtures();

        $response = $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Dashboard statistics retrieved successfully.');

        $data = $response->json('data');

        $this->assertSame(3, $data['users']['all']);
        $this->assertSame(2, $data['users']['verified']);
        $this->assertSame(1, $data['users']['verified_phone']);
        $this->assertSame(2, $data['users']['kyc_verified']);
        $this->assertSame(2, $data['dynasties']);
        $this->assertSame(3, $data['features']['all']);
        $this->assertSame(2, $data['features']['sold']);
        $this->assertSame(2, $data['referrals']);

        // referral histories 7 + 3 = 10; psc rate = 10 → 100
        $this->assertEqualsWithDelta(100.0, (float) $data['referral_amount'], 0.0001);
        // orders × prices: psc 5*10, red 10*2, blue 4*3, yellow 2.5*4
        $this->assertEqualsWithDelta(50.0, (float) $data['sold_assets']['psc'], 0.0001);
        $this->assertEqualsWithDelta(20.0, (float) $data['sold_assets']['red'], 0.0001);
        $this->assertEqualsWithDelta(12.0, (float) $data['sold_assets']['blue'], 0.0001);
        $this->assertEqualsWithDelta(10.0, (float) $data['sold_assets']['yellow'], 0.0001);
        // payments 1000 + 500
        $this->assertEqualsWithDelta(1500.0, (float) $data['deposited_rial_amount'], 0.0001);

        $this->assertIsNumeric($data['referral_amount']);
        $this->assertIsNumeric($data['sold_assets']['psc']);
        $this->assertIsNumeric($data['sold_assets']['red']);
        $this->assertIsNumeric($data['sold_assets']['blue']);
        $this->assertIsNumeric($data['sold_assets']['yellow']);
        $this->assertIsNumeric($data['deposited_rial_amount']);
    }

    public function test_success_message_is_exact(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Dashboard statistics retrieved successfully.');
    }

    private function seedDashboardFixtures(): void
    {
        $verifiedBoth = User::create([
            'name' => 'Verified Both',
            'email' => 'both@example.com',
            'code' => 'hm-1',
            'password' => 'secret',
            'ip' => '127.0.0.1',
            'email_verified_at' => now(),
        ]);
        $verifiedBoth->forceFill(['phone_verified_at' => now()])->save();

        $emailOnly = User::create([
            'name' => 'Email Only',
            'email' => 'email@example.com',
            'code' => 'hm-2',
            'password' => 'secret',
            'ip' => '127.0.0.1',
            'email_verified_at' => now(),
        ]);

        $unverified = User::create([
            'name' => 'Unverified',
            'email' => 'none@example.com',
            'code' => 'hm-3',
            'password' => 'secret',
            'ip' => '127.0.0.1',
        ]);

        Kyc::create(['user_id' => $verifiedBoth->id, 'status' => 1]);
        Kyc::create(['user_id' => $emailOnly->id, 'status' => 1]);
        Kyc::create(['user_id' => $unverified->id, 'status' => 0]);

        Dynasty::unguarded(function () {
            Dynasty::create(['name' => 'Dynasty A']);
            Dynasty::create(['name' => 'Dynasty B']);
        });

        Referral::unguarded(function () use ($verifiedBoth, $emailOnly) {
            Referral::create(['user_id' => $verifiedBoth->id, 'referrer_id' => $emailOnly->id]);
            Referral::create(['user_id' => $emailOnly->id, 'referrer_id' => $verifiedBoth->id]);
        });

        // owner_id=1 is system/unsold; avoid using user id 1 as a "sold" owner.
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => 1]);
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => $emailOnly->id]);
        Feature::create(['map_id' => 1, 'type' => 'land', 'owner_id' => $unverified->id]);

        Variable::create(['asset' => 'psc', 'price' => 10]);
        Variable::create(['asset' => 'red', 'price' => 2]);
        Variable::create(['asset' => 'blue', 'price' => 3]);
        Variable::create(['asset' => 'yellow', 'price' => 4]);

        Order::create(['asset' => 'psc', 'amount' => 5, 'user_id' => $verifiedBoth->id, 'status' => 'done']);
        Order::create(['asset' => 'red', 'amount' => 10, 'user_id' => $verifiedBoth->id, 'status' => 'done']);
        Order::create(['asset' => 'blue', 'amount' => 4, 'user_id' => $emailOnly->id, 'status' => 'done']);
        Order::create(['asset' => 'yellow', 'amount' => 2.5, 'user_id' => $emailOnly->id, 'status' => 'done']);

        Payment::create(['user_id' => $verifiedBoth->id, 'amount' => 1000, 'status' => 'paid']);
        Payment::create(['user_id' => $emailOnly->id, 'amount' => 500, 'status' => 'paid']);

        ReferralOrderHistory::create([
            'user_id' => $verifiedBoth->id,
            'referral_id' => $emailOnly->id,
            'amount' => 7,
        ]);
        ReferralOrderHistory::create([
            'user_id' => $emailOnly->id,
            'referral_id' => $verifiedBoth->id,
            'amount' => 3,
        ]);
    }
}

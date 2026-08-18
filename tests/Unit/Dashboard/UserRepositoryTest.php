<?php

namespace Tests\Unit\Dashboard;

use App\Models\Kyc;
use App\Models\User;
use App\Repositories\UserRepository;
use Tests\Concerns\CreatesDashboardSchema;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use CreatesDashboardSchema;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDashboardSchema();
        $this->repository = new UserRepository;
    }

    public function test_all_methods_return_zero_when_empty(): void
    {
        $this->assertSame(0, $this->repository->allUsers());
        $this->assertSame(0, $this->repository->verifiedEmailUsers());
        $this->assertSame(0, $this->repository->verifiedPhoneUsers());
        $this->assertSame(0, $this->repository->verifiedKycUsers());
    }

    public function test_all_users_counts_every_user(): void
    {
        $this->createUser('a@example.com', 'hm-a');
        $this->createUser('b@example.com', 'hm-b');
        $this->createUser('c@example.com', 'hm-c');

        $this->assertSame(3, $this->repository->allUsers());
    }

    public function test_verified_email_users_counts_only_non_null_email_verified_at(): void
    {
        $this->createUser('verified@example.com', 'hm-1', emailVerified: true);
        $this->createUser('also@example.com', 'hm-2', emailVerified: true);
        $this->createUser('pending@example.com', 'hm-3', emailVerified: false);

        $this->assertSame(2, $this->repository->verifiedEmailUsers());
        $this->assertSame(3, $this->repository->allUsers());
    }

    public function test_verified_phone_users_counts_only_non_null_phone_verified_at(): void
    {
        $withPhone = $this->createUser('phone@example.com', 'hm-1');
        $withPhone->forceFill(['phone_verified_at' => now()])->save();

        $this->createUser('no-phone@example.com', 'hm-2');

        $this->assertSame(1, $this->repository->verifiedPhoneUsers());
    }

    public function test_verified_kyc_users_counts_only_status_one(): void
    {
        $verifiedA = $this->createUser('kyc1@example.com', 'hm-1');
        $verifiedB = $this->createUser('kyc2@example.com', 'hm-2');
        $pending = $this->createUser('kyc3@example.com', 'hm-3');
        $rejected = $this->createUser('kyc4@example.com', 'hm-4');

        Kyc::create(['user_id' => $verifiedA->id, 'status' => 1]);
        Kyc::create(['user_id' => $verifiedB->id, 'status' => 1]);
        Kyc::create(['user_id' => $pending->id, 'status' => 0]);
        Kyc::create(['user_id' => $rejected->id, 'status' => -1]);

        $this->assertSame(2, $this->repository->verifiedKycUsers());
    }

    public function test_verified_kyc_users_ignores_users_without_kyc_rows(): void
    {
        $this->createUser('no-kyc@example.com', 'hm-1');

        $this->assertSame(0, $this->repository->verifiedKycUsers());
        $this->assertSame(1, $this->repository->allUsers());
    }

    public function test_combined_user_counts_match_seeded_mix(): void
    {
        $both = $this->createUser('both@example.com', 'hm-1', emailVerified: true);
        $both->forceFill(['phone_verified_at' => now()])->save();

        $emailOnly = $this->createUser('email@example.com', 'hm-2', emailVerified: true);
        $phoneOnly = $this->createUser('phone@example.com', 'hm-3');
        $phoneOnly->forceFill(['phone_verified_at' => now()])->save();

        $neither = $this->createUser('neither@example.com', 'hm-4');

        Kyc::create(['user_id' => $both->id, 'status' => 1]);
        Kyc::create(['user_id' => $emailOnly->id, 'status' => 0]);
        Kyc::create(['user_id' => $phoneOnly->id, 'status' => -1]);
        Kyc::create(['user_id' => $neither->id, 'status' => 1]);

        $this->assertSame(4, $this->repository->allUsers());
        $this->assertSame(2, $this->repository->verifiedEmailUsers());
        $this->assertSame(2, $this->repository->verifiedPhoneUsers());
        $this->assertSame(2, $this->repository->verifiedKycUsers());
    }

    private function createUser(string $email, string $code, bool $emailVerified = false): User
    {
        return User::create([
            'name' => $email,
            'email' => $email,
            'code' => $code,
            'password' => 'secret',
            'ip' => '127.0.0.1',
            'email_verified_at' => $emailVerified ? now() : null,
        ]);
    }
}

<?php

namespace Tests\Unit\BulkMessage;

use App\Models\Level\Level;
use App\Models\User;
use App\Services\BulkMessage\BulkMessageUserQueryService;
use Tests\Concerns\CreatesBulkMessageSchema;
use Tests\TestCase;

class UserTargetingQueryTest extends TestCase
{
    use CreatesBulkMessageSchema;

    private BulkMessageUserQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBulkMessageSchema();
        $this->service = new BulkMessageUserQueryService;
    }

    public function test_targets_all_users(): void
    {
        User::create(['name' => 'A', 'email' => 'a@test.com', 'code' => 'hm-1', 'password' => 'x', 'ip' => '1.1.1.1']);
        User::create(['name' => 'B', 'email' => 'b@test.com', 'code' => 'hm-2', 'password' => 'x', 'ip' => '1.1.1.1']);

        $count = $this->service->buildQuery('all')->count();

        $this->assertSame(2, $count);
    }

    public function test_targets_users_by_current_level(): void
    {
        $levelA = Level::create(['name' => 'A', 'slug' => 'a', 'score' => '1', 'background_image' => '']);
        $levelB = Level::create(['name' => 'B', 'slug' => 'b', 'score' => '2', 'background_image' => '']);
        $levelC = Level::create(['name' => 'C', 'slug' => 'c', 'score' => '3', 'background_image' => '']);

        $userOnC = User::create(['name' => 'On C', 'email' => 'c@test.com', 'code' => 'hm-10', 'password' => 'x', 'ip' => '1.1.1.1']);
        $userOnB = User::create(['name' => 'On B', 'email' => 'b@test.com', 'code' => 'hm-11', 'password' => 'x', 'ip' => '1.1.1.1']);

        $userOnC->levels()->attach([$levelA->id, $levelC->id]);
        $userOnB->levels()->attach([$levelB->id]);

        $ids = $this->service->buildQuery('levels', ['level_ids' => [$levelC->id]])->pluck('id')->all();

        $this->assertSame([$userOnC->id], $ids);
    }

    public function test_targets_users_in_code_range(): void
    {
        User::create(['name' => 'In', 'email' => 'in@test.com', 'code' => 'hm-2000010', 'password' => 'x', 'ip' => '1.1.1.1']);
        User::create(['name' => 'Out', 'email' => 'out@test.com', 'code' => 'hm-3000010', 'password' => 'x', 'ip' => '1.1.1.1']);

        $count = $this->service->buildQuery('code_range', [
            'code_from' => '2000001',
            'code_to' => '2000200',
        ])->count();

        $this->assertSame(1, $count);
    }

    public function test_targets_users_without_wallet_address(): void
    {
        User::create(['name' => 'No Wallet', 'email' => 'nw@test.com', 'code' => 'hm-1', 'password' => 'x', 'ip' => '1.1.1.1', 'wallet_address' => null]);
        User::create(['name' => 'Has Wallet', 'email' => 'hw@test.com', 'code' => 'hm-2', 'password' => 'x', 'ip' => '1.1.1.1', 'wallet_address' => '0xabc']);

        $count = $this->service->buildQuery('no_wallet')->count();

        $this->assertSame(1, $count);
    }

    public function test_code_range_prepends_lowercase_hm(): void
    {
        User::create(['name' => 'Match', 'email' => 'm@test.com', 'code' => 'hm-500', 'password' => 'x', 'ip' => '1.1.1.1']);

        $user = $this->service->buildQuery('code_range', [
            'code_from' => '500',
            'code_to' => '500',
        ])->first();

        $this->assertNotNull($user);
        $this->assertSame('hm-500', $user->code);
    }

    public function test_targets_selected_users_only(): void
    {
        $selected = User::create(['name' => 'Selected', 'email' => 's@test.com', 'code' => 'hm-1', 'password' => 'x', 'ip' => '1.1.1.1']);
        User::create(['name' => 'Other', 'email' => 'o@test.com', 'code' => 'hm-2', 'password' => 'x', 'ip' => '1.1.1.1']);

        $ids = $this->service->buildQuery('selected_users', ['user_ids' => [$selected->id]])->pluck('id')->all();

        $this->assertSame([$selected->id], $ids);
    }
}

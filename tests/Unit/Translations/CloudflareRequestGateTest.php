<?php

namespace Tests\Unit\Translations;

use App\Services\Translations\CloudflareRequestGate;
use Tests\TestCase;

class CloudflareRequestGateTest extends TestCase
{
    /**
     * Space requests from independent gates using the same account budget.
     */
    public function test_requests_for_the_same_account_are_paced_across_gate_instances(): void
    {
        config(['services.cloudflare.account_id' => 'account']);
        $first = new CloudflareRequestGate;
        $second = new CloudflareRequestGate;

        $started = hrtime(true);
        $first->reserve();
        $second->reserve();

        $this->assertGreaterThanOrEqual(200_000_000, hrtime(true) - $started);
    }
}

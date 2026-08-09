<?php

namespace Tests\Feature\Withdraw;

use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesCitizensApiSchema;
use Tests\TestCase;

class WithdrawApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesCitizensApiSchema;

    private const INDEX_PATH = '/api/withdraws';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpCitizensApiSchema();
    }

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_authenticated_admin_receives_empty_withdraw_payload(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Withdraws retrieved successfully.')
            ->assertJsonPath('data.withdraws', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }
}

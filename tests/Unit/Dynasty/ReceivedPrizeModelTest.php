<?php

namespace Tests\Unit\Dynasty;

use App\Models\Dynasty\DynastyPrize;
use App\Models\Dynasty\ReceivedPrize;
use App\Models\User;
use Tests\TestCase;

class ReceivedPrizeModelTest extends TestCase
{
    public function test_model_uses_expected_table_and_relations(): void
    {
        $prize = new ReceivedPrize;

        $this->assertSame('received_prizes', $prize->getTable());
        $this->assertSame(User::class, $prize->user()->getRelated()::class);
        $this->assertSame(DynastyPrize::class, $prize->prize()->getRelated()::class);
        $this->assertSame('prize_id', $prize->prize()->getForeignKeyName());
    }
}

<?php

namespace Tests\Unit\Dashboard;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Variable;
use App\Repositories\OrderRepository;
use Tests\Concerns\CreatesDashboardSchema;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use CreatesDashboardSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDashboardSchema();
    }

    public function test_asset_amounts_and_total_are_zero_when_empty(): void
    {
        $repository = new OrderRepository;

        $this->assertSame(0, $repository->pscOrderAmount());
        $this->assertSame(0, $repository->redOrderAmount());
        $this->assertSame(0, $repository->blueOrderAmount());
        $this->assertSame(0, $repository->yellowOrderAmount());
        $this->assertSame(0, $repository->irrOrderAmount());
        $this->assertSame(0, $repository->totalOrderAmount());
    }

    public function test_psc_order_amount_multiplies_orders_by_variable_price(): void
    {
        Variable::create(['asset' => 'psc', 'price' => 10]);
        Order::create(['asset' => 'psc', 'amount' => 5, 'status' => 'done']);
        Order::create(['asset' => 'psc', 'amount' => 3, 'status' => 'done']);

        $repository = new OrderRepository;

        $this->assertSame(80.0, (float) $repository->pscOrderAmount());
    }

    public function test_each_colored_asset_amount_uses_matching_variable_price(): void
    {
        Variable::create(['asset' => 'red', 'price' => 2]);
        Variable::create(['asset' => 'blue', 'price' => 3]);
        Variable::create(['asset' => 'yellow', 'price' => 4]);
        Variable::create(['asset' => 'irr', 'price' => 1]);

        Order::create(['asset' => 'red', 'amount' => 10, 'status' => 'done']);
        Order::create(['asset' => 'blue', 'amount' => 4, 'status' => 'done']);
        Order::create(['asset' => 'yellow', 'amount' => 2.5, 'status' => 'done']);
        Order::create(['asset' => 'irr', 'amount' => 100, 'status' => 'done']);

        $repository = new OrderRepository;

        $this->assertSame(20.0, (float) $repository->redOrderAmount());
        $this->assertSame(12.0, (float) $repository->blueOrderAmount());
        $this->assertSame(10.0, (float) $repository->yellowOrderAmount());
        $this->assertSame(100.0, (float) $repository->irrOrderAmount());
    }

    public function test_missing_variable_price_yields_zero_for_that_asset(): void
    {
        Order::create(['asset' => 'psc', 'amount' => 50, 'status' => 'done']);
        Order::create(['asset' => 'red', 'amount' => 50, 'status' => 'done']);
        Variable::create(['asset' => 'blue', 'price' => 3]);
        Order::create(['asset' => 'blue', 'amount' => 4, 'status' => 'done']);

        $repository = new OrderRepository;

        $this->assertSame(0.0, (float) $repository->pscOrderAmount());
        $this->assertSame(0.0, (float) $repository->redOrderAmount());
        $this->assertSame(12.0, (float) $repository->blueOrderAmount());
        $this->assertSame(0.0, (float) $repository->yellowOrderAmount());
    }

    public function test_orders_for_other_assets_do_not_affect_psc_amount(): void
    {
        Variable::create(['asset' => 'psc', 'price' => 10]);
        Order::create(['asset' => 'psc', 'amount' => 2, 'status' => 'done']);
        Order::create(['asset' => 'red', 'amount' => 999, 'status' => 'done']);

        $repository = new OrderRepository;

        $this->assertSame(20.0, (float) $repository->pscOrderAmount());
        $this->assertSame(0.0, (float) $repository->redOrderAmount());
    }

    public function test_constructor_snapshots_collections_so_later_inserts_are_ignored(): void
    {
        Variable::create(['asset' => 'psc', 'price' => 10]);
        Order::create(['asset' => 'psc', 'amount' => 1, 'status' => 'done']);

        $repository = new OrderRepository;
        $this->assertSame(10.0, (float) $repository->pscOrderAmount());

        Order::create(['asset' => 'psc', 'amount' => 100, 'status' => 'done']);

        $this->assertSame(10.0, (float) $repository->pscOrderAmount());

        $fresh = new OrderRepository;
        $this->assertSame(1010.0, (float) $fresh->pscOrderAmount());
    }

    public function test_total_order_amount_sums_payments_independently_of_orders(): void
    {
        Variable::create(['asset' => 'psc', 'price' => 10]);
        Order::create(['asset' => 'psc', 'amount' => 999, 'status' => 'done']);

        Payment::create(['amount' => 1000, 'status' => 'paid']);
        Payment::create(['amount' => 500, 'status' => 'paid']);

        $repository = new OrderRepository;

        $this->assertSame(1500.0, (float) $repository->totalOrderAmount());
        $this->assertSame(9990.0, (float) $repository->pscOrderAmount());
    }

    public function test_total_order_amount_is_zero_when_no_payments_even_with_orders(): void
    {
        Order::create(['asset' => 'psc', 'amount' => 50, 'status' => 'done']);
        Variable::create(['asset' => 'psc', 'price' => 10]);

        $repository = new OrderRepository;

        $this->assertSame(0, $repository->totalOrderAmount());
        $this->assertSame(500.0, (float) $repository->pscOrderAmount());
    }

    public function test_zero_variable_price_yields_zero_amount(): void
    {
        Variable::create(['asset' => 'yellow', 'price' => 0]);
        Order::create(['asset' => 'yellow', 'amount' => 25, 'status' => 'done']);

        $repository = new OrderRepository;

        $this->assertSame(0.0, (float) $repository->yellowOrderAmount());
    }
}

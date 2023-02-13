<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Variable;

class OrderRepository
{
    private $orders, $variable;

    public function __construct()
    {
        $this->orders = Order::select(['asset', 'amount'])->get();
        $this->variable = Variable::select(['asset', 'price'])->get();
    }

    public function pscOrderAmount(): float|int
    {
        return $this->orders->where('asset', 'psc')->sum('amount') *
            $this->variable->where('asset', 'psc')->first()->price;
    }

    public function yellowOrderAmount(): float|int
    {
        return $this->orders->where('asset', 'yellow')->sum('amount') *
            $this->variable->where('asset', 'yellow')->first()->price;
    }

    public function blueOrderAmount(): float|int
    {
        return $this->orders->where('asset', 'blue')->sum('amount') *
            $this->variable->where('asset', 'blue')->first()->price;
    }

    public function redOrderAmount(): float|int
    {
        return $this->orders->where('asset', 'red')->sum('amount') *
            $this->variable->where('asset', 'red')->first()->price;
    }

    public function irrOrderAmount(): float|int
    {
        return $this->orders->where('asset', 'irr')->sum('amount') *
            $this->variable->where('asset', 'irr')->first()->price;
    }

    public function totalOrderAmount(): float|int
    {
        return $this->orders->sum('amount');
    }
}

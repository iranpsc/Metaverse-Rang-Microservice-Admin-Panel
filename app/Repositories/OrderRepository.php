<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Variable;

class OrderRepository
{
    private $orders;

    public function __construct()
    {
        $this->orders = Order::where('status', 1)->lazy();
    }

    public function pscOrderAmount(): float|int
    {
        return $this->orders->reject(function($order) {
                return $order->asset != 'psc';
            })->sum('amount') * Variable::getRate('psc');
    }

    public function yellowOrderAmount(): float|int
    {
        return $this->orders->reject(function($order) {
                return $order->asset != 'yellow';
            })->sum('amount') * Variable::getRate('yellow');
    }

    public function blueOrderAmount(): float|int
    {
        return $this->orders->reject(function($order) {
                return $order->asset != 'blue';
            })->sum('amount') * Variable::getRate('blue');
    }

    public function redOrderAmount(): float|int
    {
        return $this->orders->reject(function($order) {
                return $order->asset != 'red';
            })->sum('amount') * Variable::getRate('red');
    }

    public function irrOrderAmount(): float|int
    {
        return $this->orders->reject(function($order) {
                return $order->asset != 'irr';
            })->sum('amount') * Variable::getRate('irr');
    }

    public function totalOrderAmount(): float|int
    {
        return $this->yellowOrderAmount() + $this->blueOrderAmount() + $this->redOrderAmount() + $this->irrOrderAmount();
    }
}

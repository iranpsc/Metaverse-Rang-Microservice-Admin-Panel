<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Variable;

class OrderRepository
{
    private $orders, $variables;

    public function __construct()
    {
        $this->orders = Order::select(['asset', 'amount'])->get();
        $this->variables = Variable::select(['asset', 'price'])->get();
    }

    public function pscOrderAmount(): float|int
    {
        return $this->variables ? $this->orders->where('asset', 'psc')->sum('amount') *
            optional($this->variables->where('asset', 'psc')->first())->price : 0;
    }

    public function yellowOrderAmount(): float|int
    {
        return $this->variables ? $this->orders->where('asset', 'yellow')->sum('amount') *
            optional($this->variables->where('asset', 'yellow')->first())->price : 0;
    }

    public function blueOrderAmount(): float|int
    {
        return $this->variables ? $this->orders->where('asset', 'blue')->sum('amount') *
            optional($this->variables->where('asset', 'blue')->first())->price : 0;
    }

    public function redOrderAmount(): float|int
    {
        return $this->variables ? $this->orders->where('asset', 'red')->sum('amount') *
            optional($this->variables->where('asset', 'red')->first())->price : 0;
    }

    public function irrOrderAmount(): float|int
    {
        return $this->variables ? $this->orders->where('asset', 'irr')->sum('amount') *
            optional($this->variables->where('asset', 'irr')->first())->price : 0;
    }

    public function totalOrderAmount(): float|int
    {
        return Payment::sum('amount');
    }
}

<?php

namespace App\Http\Livewire\Transactions;

use App\Models\Transaction;
use Livewire\Component;

class Listing extends Component
{
    private $transactions;

    public function __construct()
    {
        $this->transactions = Transaction::latest()->paginate(10);
    }


    public function render()
    {
        return view('livewire.transactions.listing', ['transactions' => $this->transactions]);
    }
}

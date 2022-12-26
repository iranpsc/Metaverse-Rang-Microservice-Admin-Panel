<?php

namespace App\Http\Livewire\Citizens;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;

class Deposits extends Component
{
    use WithPagination;

    public $searchTerm;
    private $payments;

    public function updated() {
        $this->payments = Payment::where('ref_id', 'like', '%' . $this->searchTerm . '%')
        ->paginate(10);
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.citizens.deposits', [
            'payments' => $this->payments ?? Payment::latest()->paginate(10)
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}

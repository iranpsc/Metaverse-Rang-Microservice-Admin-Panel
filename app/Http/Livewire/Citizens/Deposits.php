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

    protected $paginationTheme = 'bootstrap';

    public function updatedSearchTerm() {
        $this->payments = Payment::search($this->searchTerm)->with('user')->paginate(10);
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.citizens.deposits', [
            'payments' => $this->payments ?? Payment::latest()->with('user')->paginate(10)
        ])->extends('layouts.app')->section('content');
    }
}

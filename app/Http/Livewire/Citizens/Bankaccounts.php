<?php

namespace App\Http\Livewire\Citizens;

use App\Models\Kyc;
use App\Models\User;
use Livewire\Component;

class Bankaccounts extends Component
{
    public $kycs, $searchTerm;

    public function mount() {
        $this->kycs = Kyc::with('user')->latest()->get();
    }

    public function updated() {
        $this->kycs = Kyc::where('fname', 'like', '%' . $this->searchTerm . '%')
        ->orWhere('lname', 'like', '%' . $this->searchTerm . '%')
        ->get();
    }

    public function render()
    {
        return view('livewire.citizens.bankaccounts');
    }
}

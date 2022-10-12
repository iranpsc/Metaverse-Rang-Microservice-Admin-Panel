<?php

namespace App\Http\Livewire\Citizens;

use Livewire\Component;
use App\Models\Kyc as ModelKyc;

class Kyc extends Component
{
    public $searchTerm, $kycs;

    public function __construct()
    {
        $this->kycs = ModelKyc::latest()->get();
    }

    public function updated() {
        $this->kycs = ModelKyc::where('melli_code', 'like', '%' . $this->searchTerm . '%')
        ->get();
    }

    public function render()
    {
        return view('livewire.citizens.kyc');
    }
}

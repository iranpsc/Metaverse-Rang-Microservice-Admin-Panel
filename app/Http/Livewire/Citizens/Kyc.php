<?php

namespace App\Http\Livewire\Citizens;

use Livewire\Component;
use App\Models\Kyc as ModelKyc;

class Kyc extends Component
{
    public $searchTerm;

    private $kycs;

    public function updatedSearchTerm() {
        $this->kycs = ModelKyc::with('errors')
        ->where('melli_code', 'like', '%' . $this->searchTerm . '%')
        ->simplePaginate(10);
    }

    public function render()
    {
        return view('livewire.citizens.kyc', [
            'kycs' => $this->kycs ?? ModelKyc::with(['errors', 'user'])->simplePaginate(10)
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}

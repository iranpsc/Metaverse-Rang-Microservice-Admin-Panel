<?php

namespace App\Http\Livewire\Citizens;

use Livewire\Component;
use App\Models\Kyc as ModelKyc;
use Livewire\WithPagination;

class Kyc extends Component
{
    use WithPagination;

    public $searchTerm;

    private $kycs;

    public function updatedSearchTerm() {
        $this->kycs = ModelKyc::with(['errors', 'user'])
        ->where('melli_code', 'like', '%' . $this->searchTerm . '%')
        ->first();
    }

    public function render()
    {
        return view('livewire.citizens.kyc', [
            'kycs' => $this->kycs ?? ModelKyc::with(['errors', 'user'])
            ->orderByDesc('created_at')
            ->simplePaginate(10)
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}

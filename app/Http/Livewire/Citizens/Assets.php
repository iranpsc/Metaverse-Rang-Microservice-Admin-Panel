<?php

namespace App\Http\Livewire\Citizens;
use App\Models\Asset;

use Livewire\Component;
use Livewire\WithPagination;

class Assets extends Component
{
    use WithPagination;

    public $searchTerm;

    public function render()
    {
        return view('livewire.citizens.assets', [
            'assets' => Asset::with('user', 'user.features')->paginate(10)
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}

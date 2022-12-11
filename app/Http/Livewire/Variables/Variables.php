<?php

namespace App\Http\Livewire\Variables;

use App\Models\Option;
use App\Models\Variable;
use Livewire\Component;

class Variables extends Component
{
    public function render()
    {
        return view('livewire.variables.variables')
        ->extends('layouts.app')
        ->section('content');
    }
}

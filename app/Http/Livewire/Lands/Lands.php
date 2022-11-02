<?php

namespace App\Http\Livewire\Lands;

use Livewire\Component;

class Lands extends Component
{
    public function render()
    {
        return view('livewire.lands.lands')
        ->extends('layouts.app')
        ->section('content');
    }
}

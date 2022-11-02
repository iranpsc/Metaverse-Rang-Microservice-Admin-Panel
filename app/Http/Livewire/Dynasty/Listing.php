<?php

namespace App\Http\Livewire\Dynasty;

use Livewire\Component;

class Listing extends Component
{
    public function render()
    {
        return view('livewire.dynasty.listing')
        ->extends('layouts.app')
        ->section('content');
    }
}

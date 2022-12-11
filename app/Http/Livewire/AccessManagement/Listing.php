<?php

namespace App\Http\Livewire\AccessManagement;

use Livewire\Component;

class Listing extends Component
{
    public function render()
    {
        return view('livewire.access-management.listing')
        ->extends('layouts.app')
        ->section('content');
    }
}

<?php

namespace App\Http\Livewire\IpManagement;

use Livewire\Component;

class IpManagement extends Component
{
    public function render()
    {
        return view('livewire.ip-management.ip-management')
        ->extends('layouts.app')
        ->section('content');
    }
}

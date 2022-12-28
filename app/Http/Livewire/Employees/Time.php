<?php

namespace App\Http\Livewire\Employees;

use Livewire\Component;

class Time extends Component
{
    public function render()
    {
        return view('livewire.employees.time')
        ->extends('layouts.app')
        ->section('content');
    }
}

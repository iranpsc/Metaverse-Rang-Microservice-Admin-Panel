<?php

namespace App\Http\Livewire\Employees;

use Livewire\Component;

class Bank extends Component
{
    public function render()
    {
        return view('livewire.employees.bank')
        ->extends('layouts.app')
        ->section('content');;
    }
}

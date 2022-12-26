<?php

namespace App\Http\Livewire\Employees;

use Livewire\Component;

class Documents extends Component
{
    public function render()
    {
        return view('livewire.employees.documents')
        ->extends('layouts.app')
        ->section('content');
    }
}

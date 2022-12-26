<?php

namespace App\Http\Livewire\Employees;

use Livewire\Component;

class Salary extends Component
{
    public function render()
    {
        return view('livewire.employees.salary')
        ->extends('layouts.app')
        ->section('content');
    }
}

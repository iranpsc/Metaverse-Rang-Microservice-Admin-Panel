<?php

namespace App\Http\Livewire\Employees;

use App\Models\Employee\Employee;
use Livewire\Component;

class Employees extends Component
{
    public function render()
    {
        return view('livewire.employees.employees');
    }
}

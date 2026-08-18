<?php

namespace App\Livewire\Employees;

use Livewire\Attributes\Title;
use Livewire\Component;

class Documents extends Component
{
    public $searchTerm;

    #[Title('اسناد')]
    public function render()
    {
        return view('livewire.employees.documents');
    }
}

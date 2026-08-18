<?php

namespace App\Livewire\Citizens;

use Livewire\Component;
use Livewire\Attributes\Title;

class Withdraws extends Component
{
    #[Title('برداشت ها')]
    public function render()
    {
        return view('livewire.citizens.withdraws');
    }
}

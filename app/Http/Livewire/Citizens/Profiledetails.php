<?php

namespace App\Http\Livewire\Citizens;

use App\Models\User;
use Livewire\Component;

class Profiledetails extends Component
{
    private $users;

    public function mount() {
        $this->users = User::lazy();
    }

    public function render()
    {
        return view('livewire.citizens.profiledetails', ['users' => $this->users])
        ->extends('layouts.app')
        ->section('content');
    }
}

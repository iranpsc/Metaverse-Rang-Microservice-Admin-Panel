<?php

namespace App\Livewire\Citizens;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class RegistrationInfo extends Component
{
    use WithPagination;

    private $users;
    public $searchTerm;

    public function updatedSearchTerm() {
        $this->users = User::where('email', 'like', '%' . $this->searchTerm . '%')
        ->orWhere('name', 'like', '%' . $this->searchTerm . '%')
        ->paginate(10);
    }

    #[Title('اطلاعات ثبت نام')]
    public function render()
    {
        return view('livewire.citizens.registration-info', [
            'users' => $this->users ?? User::paginate(10)
        ]);
    }
}

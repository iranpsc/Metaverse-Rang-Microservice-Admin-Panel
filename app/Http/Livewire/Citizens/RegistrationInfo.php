<?php

namespace App\Http\Livewire\Citizens;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class RegistrationInfo extends Component
{
    use WithPagination;

    private $users;
    public $searchTerm;
    public $pageTitle = 'اطلاعات ثبت نام';

    protected $paginationTheme = 'bootstrap';

    public function updated() {
        $this->resetPage();
        $this->users = User::where('email', 'like', '%' . $this->searchTerm . '%')
        ->orWhere('name', 'like', '%' . $this->searchTerm . '%')
        ->simplePaginate(10);
    }

    public function render()
    {
        return view('livewire.citizens.registration-info', [
            'users' => $this->users ?? User::simplePaginate(10)
        ]);
    }
}

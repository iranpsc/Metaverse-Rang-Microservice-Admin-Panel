<?php

namespace App\Livewire\Citizens;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class Profiledetails extends Component
{
    use WithPagination;

    public $searchTerm;

    public function updatedSearchTerm()
    {
        //
    }

    #[Title('جزئیات پروفایل')]
    public function render()
    {
        return view('livewire.citizens.profiledetails', [
            'users' => User::withSum('activities', 'total')
                ->withCount([
                    'followers',
                    'payments',
                    'payments as more_than_a_million_payment' => function ($query) {
                        $query->where('amount', '>', 10000000);
                    }
                ])
                ->paginate(10)
        ]);
    }
}

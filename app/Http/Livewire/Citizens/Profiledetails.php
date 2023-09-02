<?php

namespace App\Http\Livewire\Citizens;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Profiledetails extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        return view('livewire.citizens.profiledetails', [
            'users' => User::withSum('activities', 'total')
                ->withCount([
                    'followers',
                    'payments' => function ($query) {
                        $query->where('amount', '>', 10000000);
                    }
                ])
                ->paginate(10)
        ]);
    }
}

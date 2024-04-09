<?php

namespace App\Livewire\Citizens;
use App\Models\Asset;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Assets extends Component
{
    use WithPagination;

    public $searchTerm;

    #[Title('دارایی های شهروندان')]
    public function render()
    {
        return view('livewire.citizens.assets', [
            'assets' => Asset::with('user:id,name', 'user.features:id,owner_id')->paginate(10)
        ]);
    }
}

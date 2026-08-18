<?php

namespace App\Livewire\Lands;

use Livewire\Component;
use App\Models\Feature;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

class Prices extends Component
{
    use WithPagination;

    private $features;

    public $search;

    public function updatedSearch() {
        //
    }

    #[Title('قیمت زمین ها')]
    public function render()
    {
        return view('livewire.lands.prices', [
            'features' => $this->features ?? Feature::with('properties')->paginate(10)
        ]);
    }
}

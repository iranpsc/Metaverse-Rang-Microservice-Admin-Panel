<?php

namespace App\Http\Livewire\Lands;

use Livewire\Component;
use App\Models\Feature;
use Livewire\WithPagination;

class LandsPrice extends Component
{
    use WithPagination;

    private $features;
    public $search;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch() {
        $this->resetPage('lands-price');
    }

    public function render()
    {
        return view('livewire.lands.lands-price', [
            'features' => $this->features ?? Feature::with([
                'properties',
                'geometry',
                'geometry.coordinates'
            ])
                ->paginate(10, '*', 'lands-price')
        ]);
    }
}

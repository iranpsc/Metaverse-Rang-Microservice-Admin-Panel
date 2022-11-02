<?php

namespace App\Http\Livewire\Lands;

use App\Models\Feature;
use Livewire\Component;
use Livewire\WithPagination;

class LandsListing extends Component
{
    use WithPagination;

    private $features;
    public $search = '';

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['featureUpdated' => '$refresh'];

    public function updatedSearch()
    {
        $this->resetPage('lands-listing');
    }

    public function render()
    {

        return view('livewire.lands.lands-listing', [
            'features' => $this->features ?? Feature::with('properties', 'geometry.coordinates')
            ->paginate(10, ['*'], 'lands-listing')
        ]);
    }
}

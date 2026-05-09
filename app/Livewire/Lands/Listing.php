<?php

namespace App\Livewire\Lands;

use App\Models\FeatureProperties;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class Listing extends Component
{
    use WithPagination;

    private $properties;

    public $search;

    public function updatedSearch()
    {
        $this->resetPage('lands-listing');
        $this->properties = FeatureProperties::with('feature', 'feature.geometry.coordinates')
            ->where('id', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'asc')
            ->simplePaginate(10);
    }

    #[Title('لیست املاک')]
    public function render()
    {
        return view('livewire.lands.listing', [
            'properties' => $this->properties ?? FeatureProperties::with('feature', 'feature.geometry.coordinates')->paginate(10)
        ]);
    }
}

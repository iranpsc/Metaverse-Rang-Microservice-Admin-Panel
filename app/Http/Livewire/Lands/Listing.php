<?php

namespace App\Http\Livewire\Lands;

use App\Models\FeatureProperties;
use Livewire\Component;
use Livewire\WithPagination;

class Listing extends Component
{
    use WithPagination;

    private $properties;
    public $search = '';

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['featureUpdated' => '$refresh'];

    public function updatedSearch()
    {
        $this->resetPage('lands-listing');
        $this->properties = FeatureProperties::with('feature', 'feature.geometry.coordinates')
            ->where('id', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'asc')
            ->simplePaginate(10);
    }

    public function render()
    {
        return view('livewire.lands.listing', [
            'properties' => is_null($this->properties) ?
                FeatureProperties::with('feature', 'feature.geometry.coordinates')
                ->orderBy('id', 'asc')
                ->simplePaginate(10)
                : $this->properties
        ])->extends('layouts.app')->section('content');
    }
}

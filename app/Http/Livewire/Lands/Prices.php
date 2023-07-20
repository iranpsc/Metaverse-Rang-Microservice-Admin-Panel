<?php

namespace App\Http\Livewire\Lands;

use Livewire\Component;
use App\Models\Feature;
use Livewire\WithPagination;

class Prices extends Component
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
        return view('livewire.lands.prices', [
            'features' => $this->features ?? Feature::with('properties')->simplePaginate(10)
        ])->extends('layouts.app')->section('content');
    }
}

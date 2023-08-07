<?php

namespace App\Http\Livewire\Lands;

use Livewire\Component;
use App\Models\SellFeatureRequest;
use Livewire\WithPagination;

class Pricing extends Component
{
    use WithPagination;

    private $pricings;
    public $search;

    public function render()
    {
        return view('livewire.lands.pricing', [
            'pricings' => SellFeatureRequest::with('feature')
            ->where('status', 0)
            ->simplePaginate(10)
        ]);
    }
}

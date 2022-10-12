<?php

namespace App\Http\Livewire\Lands;

use Livewire\Component;
use App\Models\SellFeatureRequest;
use Livewire\WithPagination;

class PricingLands extends Component
{
    use WithPagination;

    private $pricings;
    public $search;

    public function render()
    {
        return view('livewire.lands.pricing-lands', [
            'pricings' => SellFeatureRequest::with('feature')
            ->where('status', 0)
            ->paginate(10, '*', 'pricing-lands')
        ]);
    }
}

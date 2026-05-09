<?php

namespace App\Livewire\Lands;

use Livewire\Component;
use App\Models\SellFeatureRequest;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

class Pricing extends Component
{
    use WithPagination;

    private $pricings;

    public $search;

    #[Title('لیست قیمت گذاری ها')]
    public function render()
    {
        return view('livewire.lands.pricing', [
            'pricings' => SellFeatureRequest::with('feature')
            ->where('status', 0)
            ->paginate(10)
        ]);
    }
}

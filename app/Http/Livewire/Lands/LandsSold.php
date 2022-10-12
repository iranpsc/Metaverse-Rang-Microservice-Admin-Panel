<?php

namespace App\Http\Livewire\Lands;

use App\Models\Trade;
use Livewire\Component;
use Livewire\WithPagination;

class LandsSold extends Component
{
    use WithPagination;

    private $trades;
    public $search = '';

    public function updatingSearch() {
        $this->resetPage('sold-lands');
    }

    public function render()
    {
        return view('livewire.lands.lands-sold', [
            'trades' => Trade::with('feature', 'buyer')
            ->where('seller_id', 1)
            ->paginate(10, '*', 'sold-lands')
        ]);
    }
}

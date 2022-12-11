<?php

namespace App\Http\Livewire\Lands;

use App\Models\Trade;
use Livewire\Component;
use Livewire\WithPagination;

class Traded extends Component
{
    use WithPagination;

    private $trades;
    public $search;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch() {
        $this->resetPage('traded-lands');
    }
    public function render()
    {
        return view('livewire.lands.traded', [
            'trades' => Trade::with('feature', 'buyer', 'seller', 'commision')
            ->whereNot('seller_id', 1)
            ->paginate('10', '*', 'traded-lands')
        ]);
    }
}

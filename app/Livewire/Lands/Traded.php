<?php

namespace App\Livewire\Lands;

use App\Models\Trade;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class Traded extends Component
{
    use WithPagination;

    private $trades;
    public $search;

    public function updatingSearch() {
        $this->resetPage('traded-lands');
    }

    #[Title('لیست زمین های معامله شده')]
    public function render()
    {
        return view('livewire.lands.traded', [
            'trades' => Trade::with('feature', 'buyer', 'seller', 'commision')
            ->whereNot('seller_id', 1)
            ->paginate('10')
        ]);
    }
}

<?php

namespace App\Livewire\Lands;

use App\Models\Trade;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Sold extends Component
{
    use WithPagination;

    private $trades;
    public $search = '';

    public function updatingSearch() {
        $this->resetPage('sold-lands');
    }

    #[Title('لیست زمین های فروخته شده')]
    public function render()
    {
        return view('livewire.lands.sold', [
            'trades' => Trade::with('feature', 'buyer')
            ->where('seller_id', 1)
            ->paginate(10)
        ]);
    }
}

<?php

namespace App\Http\Livewire\Citizens;

use Livewire\Component;
use App\Models\Kyc as ModelKyc;
use Livewire\WithPagination;

class Kyc extends Component
{
    use WithPagination;

    public $searchTerm;

    protected $paginationTheme = 'bootstrap';

    private $kycs;

    public function updatedSearchTerm()
    {
        $this->kycs = ModelKyc::with('user')
            ->where('melli_code', 'like', '%' . $this->searchTerm . '%')
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.citizens.kyc', [
            'kycs' => $this->kycs ?? ModelKyc::with('user')->orderByDesc('created_at')->paginate(10)
        ]);
    }
}

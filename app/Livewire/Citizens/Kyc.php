<?php

namespace App\Livewire\Citizens;

use Livewire\Component;
use App\Models\Kyc as ModelKyc;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

class Kyc extends Component
{
    use WithPagination;

    public $searchTerm;

    private $kycs;

    public function updatedSearchTerm()
    {
        $this->kycs = ModelKyc::with('user')
            ->where('melli_code', 'like', '%' . $this->searchTerm . '%')
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    #[On('kycReviewed')]
    public function kycReviewed()
    {
        '$refresh';
    }

    #[Title('احراز هویت')]
    public function render()
    {
        return view('livewire.citizens.kyc', [
            'kycs' => $this->kycs ?? ModelKyc::with('user')->latest()->paginate(10)
        ]);
    }
}

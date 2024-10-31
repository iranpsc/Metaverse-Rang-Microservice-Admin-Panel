<?php

namespace App\Livewire\Citizens;

use Livewire\Component;
use App\Models\Kyc;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

class Kycs extends Component
{
    use WithPagination;

    public $search = '';

    private $kycs;

    public function updatingSearch()
    {
        $this->resetPage();

        trim($this->search);
    }

    public function updatedSearch()
    {
        $this->kycs = Kyc::where('melli_code', 'like', '%' . $this->search . '%')->paginate(10);
    }

    #[On('kycReviewed')]
    public function kycReviewed()
    {
        '$refresh';
    }

    #[Title('احراز هویت')]
    public function render()
    {
        return view('livewire.citizens.kycs', [
            'kycs' => $this->kycs ?? Kyc::latest()->paginate(10)
        ]);
    }
}

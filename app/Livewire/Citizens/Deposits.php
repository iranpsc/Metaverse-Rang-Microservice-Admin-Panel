<?php

namespace App\Livewire\Citizens;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use App\Exports\PaymentsExport;
use Livewire\Attributes\Title;

class Deposits extends Component
{
    use WithPagination;

    public $searchTerm;
    private $payments;

    public function updatedSearchTerm() {
        $this->payments = Payment::search($this->searchTerm)->with('user')->paginate(10);
        $this->resetPage();
    }

    public function export() {
        return (new PaymentsExport)->download('transactions.xlsx');
    }

    #[Title('واریزی ها')]
    public function render()
    {
        return view('livewire.citizens.deposits', [
            'payments' => $this->payments ?? Payment::with('user:id,name')->paginate(10)
        ]);
    }
}

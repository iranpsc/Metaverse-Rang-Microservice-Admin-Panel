<?php

namespace App\Livewire\Reports;

use App\Models\Report;
use Livewire\Attributes\Title;
use Livewire\Component;

class Listing extends Component
{
    public $reports;

    public function mount()
    {
        $this->reports = Report::all();
    }

    #[Title('گزارشات')]
    public function render()
    {
        return view('livewire.reports.listing');
    }
}

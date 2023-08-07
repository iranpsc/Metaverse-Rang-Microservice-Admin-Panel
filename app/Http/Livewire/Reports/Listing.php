<?php

namespace App\Http\Livewire\Reports;

use App\Models\Report;
use Livewire\Component;

class Listing extends Component
{
    public $reports;

    public function mount()
    {
        $this->reports = Report::all();
    }

    public function render()
    {
        return view('livewire.reports.listing');
    }
}

<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class CodingError extends Component
{
    public $reports, $search;

    public function mount($reports) {
        $this->reports = $reports->reject(function($report) {
            return $report->subject != 'codingError';
        });
    }

    public function render()
    {
        return view('livewire.reports.coding-error');
    }
}

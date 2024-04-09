<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class DisplayError extends Component
{
    public $reports, $search;

    public function mount($reports) {
        $this->reports = $reports->reject(function($report) {
            return $report->subject != 'displayError';
        });
    }
    public function render()
    {
        return view('livewire.reports.display-error');
    }
}

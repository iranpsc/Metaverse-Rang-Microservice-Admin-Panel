<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class SpellingError extends Component
{
    public $reports, $search;

    public function mount($reports) {
        $this->reports = $reports->reject(function($report) {
            return $report->subject != 'spellingError';
        });
    }
    public function render()
    {
        return view('livewire.reports.spelling-error');
    }
}

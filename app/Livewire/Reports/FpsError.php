<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class FpsError extends Component
{
    public $reports, $search;

    public function mount($reports) {
        $this->reports = $reports->reject(function($report) {
            return $report->subject != 'fpsError';
        });
    }
    public function render()
    {
        return view('livewire.reports.fps-error');
    }
}

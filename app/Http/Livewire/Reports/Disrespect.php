<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;

class Disrespect extends Component
{
    public $reports, $search;

    public function mount($reports) {
        $this->reports = $reports->reject(function($report) {
            return $report->subject != 'disrespect';
        });
    }
    public function render()
    {
        return view('livewire.reports.disrespect');
    }
}

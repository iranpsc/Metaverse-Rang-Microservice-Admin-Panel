<?php

namespace App\Http\Livewire\Variables;

use Livewire\Component;
use App\Models\VariableChangeLog;

class VariablesChangeLogs extends Component
{
    protected $listeners = [
        'currencyUpdated' => '$refresh',
        'currencyDeleted' => '$refresh',
    ];

    public function render()
    {
        return view('livewire.variables.variables-change-logs', [
            'changeLogs' => VariableChangeLog::where('variable_id', '!=', null)
            ->paginate(10, ['*'], 'change-log-listing')
        ]);
    }
}

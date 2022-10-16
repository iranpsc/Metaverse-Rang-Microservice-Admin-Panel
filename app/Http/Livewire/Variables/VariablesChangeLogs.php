<?php

namespace App\Http\Livewire\Variables;

use Livewire\Component;
use App\Models\VariableChangeLog;

class VariablesChangeLogs extends Component
{
    public $variables;
    protected $listeners = [
        'delete-variables-change-logs' => 'deleteVariableChangeLogs'
    ];

    public function deleteVariableChangeLogs($id)
    {
        VariableChangeLog::where('variable_id', $id)->delete();
    }

    public function render()
    {
        return view('livewire.variables.variables-change-logs');
    }
}

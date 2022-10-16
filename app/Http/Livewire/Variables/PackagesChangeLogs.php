<?php

namespace App\Http\Livewire\Variables;

use App\Models\VariableChangeLog;
use Livewire\Component;

class PackagesChangeLogs extends Component
{
    public $options;

    protected $listeners = [
        'delete-change-logs' => 'deleteChangeLogs',
    ];

    public function deleteChangeLogs($id)
    {
        VariableChangeLog::where('package_id', $id)->delete();
    }

    public function render()
    {
        return view('livewire.variables.packages-change-logs');
    }
}

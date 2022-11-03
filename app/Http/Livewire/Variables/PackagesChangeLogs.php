<?php

namespace App\Http\Livewire\Variables;

use App\Models\VariableChangeLog;
use Livewire\Component;

class PackagesChangeLogs extends Component
{
    protected $listeners = [
        'packageUpdated' => '$refresh',
        'packageDeleted' => '$refresh',
    ];

    public function render()
    {
        return view('livewire.variables.packages-change-logs', [
            'priceChagneLogs' => VariableChangeLog::where('option_id', '!=', null)
            ->paginate(10, ['*'], 'package-price-change-listing')
        ]);
    }
}

<?php

namespace App\Http\Livewire\Variables;

use App\Models\Option;
use App\Models\Variable;
use Livewire\Component;

class Variables extends Component
{
    public $options, $variables;

    public function mount()
    {
        $this->options = Option::with('priceChangeLogs')->get();
        $this->variables = Variable::with('priceChangeLogs')->get();
    }

    public function render()
    {
        return view('livewire.variables.variables');
    }
}

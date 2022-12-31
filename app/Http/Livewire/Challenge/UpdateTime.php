<?php

namespace App\Http\Livewire\Challenge;

use Livewire\Component;

class UpdateTime extends Component
{
    public $key, $value, $time;

    public function mount($time)
    {
        $this->time = $time;
        $this->key = $time->key;
        $this->value = $time->value;
    }

    public function update()
    {
        $this->validate([
            'value' => 'required|numeric|min:15'
        ]);

        $this->time->update([
            'value' => $this->value
        ]);
        session()->flash('success','زمان بروز رسانی شد');
    }

    public function render()
    {
        return view('livewire.challenge.update-time');
    }
}

<?php

namespace App\Livewire\Dynasty;

use Livewire\Component;

class EditPrize extends Component
{
    public $prize;
    public $satisfaction, $introduction_profit_increase,
        $accumulated_capital_reserve, $data_storage, $psc;

    public function mount()
    {
        $this->satisfaction = $this->prize->satisfaction;
        $this->introduction_profit_increase = $this->prize->introduction_profit_increase * 100;
        $this->accumulated_capital_reserve = $this->prize->accumulated_capital_reserve * 100;
        $this->data_storage = $this->prize->data_storage * 100;
        $this->psc = $this->prize->psc;
    }

    protected $rules = [
        'satisfaction' => 'required|numeric|min:0',
        'introduction_profit_increase' => 'required|numeric|min:0',
        'accumulated_capital_reserve' => 'required|numeric|min:0',
        'data_storage' => 'required|numeric|min:0',
        'psc' => 'required|numeric|min:0'
    ];

    public function update()
    {
        $this->validate();
        $this->prize->update([
            'satisfaction' => $this->satisfaction,
            'introduction_profit_increase' => $this->introduction_profit_increase / 100,
            'accumulated_capital_reserve' => $this->accumulated_capital_reserve / 100,
            'data_storage' => $this->data_storage / 100,
            'psc' => $this->psc
        ]);
        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }
}

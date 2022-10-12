<?php

namespace App\Http\Livewire\Dynasty;

use Livewire\Component;

class EditPrize extends Component
{
    public $prize;
    public $satisfaction, $introduction_profit_increase,
        $accumulated_capital_reserve, $data_storage, $psc;

    public function mount()
    {
        $this->satisfaction = $this->prize->satisfaction;
        $this->introduction_profit_increase = $this->prize->introduction_profit_increase;
        $this->accumulated_capital_reserve = $this->prize->accumulated_capital_reserve;
        $this->data_storage = $this->prize->data_storage;
        $this->psc = $this->prize->psc;
    }

    protected $rules = [
        'satisfaction' => 'required|min:0',
        'introduction_profit_increase' => 'required|min:0',
        'accumulated_capital_reserve' => 'required|min:0',
        'data_storage' => 'required|min:0',
        'psc' => 'required|min:0'
    ];

    protected $messages = [
        'satisfaction.required' => 'مقدار رضایت را وارد کنید',
        'introduction_profit_increase.required' => 'مقدار افزایش سود معرفی را وارد کنید',
        'accumulated_capital_reserve.required' => 'ذخیره سرمایه انباشته را وارد کنید',
        'data_storage.required' => 'مقدار ذخیره دیتا را وارد کنید',
        'psc.required' => 'میزان psc را وارد کنید'
    ];
    public function update()
    {
        $this->validate();
        $this->prize->update([
            'satisfaction' => $this->satisfaction,
            'introduction_profit_increase' => $this->introduction_profit_increase,
            'accumulated_capital_reserve' => $this->accumulated_capital_reserve,
            'data_storage' => $this->data_storage,
            'psc' => $this->psc
        ]);
        session()->flash('success', 'پاداش با موفقیت ویرایش شد');
    }
    public function render()
    {
        return view('livewire.dynasty.edit-prize');
    }
}

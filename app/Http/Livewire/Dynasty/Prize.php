<?php

namespace App\Http\Livewire\Dynasty;

use App\Models\Dynasty\DynastyPrize;
use Livewire\Component;

class Prize extends Component
{
    public DynastyPrize $prize;
    public $prizes;

    public function mount()
    {
        $this->prizes = DynastyPrize::all();
    }

    public function __construct()
    {
        $this->prize = new DynastyPrize;
    }

    protected $rules = [
        'prize.member' => 'required',
        'prize.satisfaction' => 'required|min:0',
        'prize.introduction_profit_increase' => 'required|min:0',
        'prize.accumulated_capital_reserve' => 'required|min:0',
        'prize.data_storage' => 'required|min:0',
        'prize.psc' => 'required|min:0'
    ];

    protected $messages = [
        'prize.member.required' => 'نسبت خانوادگی را مشخص کنید',
        'prize.satisfaction.required' => 'مقدار رضایت را وارد کنید',
        'prize.introduction_profit_increase.required' => 'مقدار افزایش سود معرفی را وارد کنید',
        'prize.accumulated_capital_reserve.required' => 'ذخیره سرمایه انباشته را وارد کنید',
        'prize.data_storage.required' => 'مقدار ذخیره دیتا را وارد کنید',
        'prize.psc.required' => 'میزان psc را وارد کنید'
    ];
    public function save()
    {
        $this->validate();
        $this->prize->save();
        session()->flash('success', 'پاداش با موفقیت ثبت شد');
        $this->resetExcept('prizes');
        $this->prizes = DynastyPrize::all();
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function delete(DynastyPrize $dynastyPrize)
    {
        $dynastyPrize->delete();
        $this->prizes = DynastyPrize::all();
    }

    public function render()
    {
        return view('livewire.dynasty.prize');
    }
}

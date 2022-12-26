<?php

namespace App\Http\Livewire\Dynasty;

use App\Models\Dynasty\DynastyPrize;
use Livewire\Component;
use Livewire\WithPagination;

class Prize extends Component
{
    use WithPagination;

    public $member, $satisfaction, $introduction_profit_increase, $accumulated_capital_reserve, $data_storage, $psc;

    protected $listeners = [
        'deleteDynastyPrize' => 'delete',
        'prizeCreated' => '$refresh',
        'prizeUpdated' => '$refresh',
        'prizeDeleted' => '$refresh',
    ];

    protected $rules = [
        'member' => 'required|in:father,mother,brother,offspring,sister,husband,wife|unique:dynasty_prizes',
        'satisfaction' => 'required|numeric|min:0',
        'introduction_profit_increase' => 'required|numeric|min:0',
        'accumulated_capital_reserve' => 'required|numeric|min:0',
        'data_storage' => 'required|numeric|min:0',
        'psc' => 'required|numeric|min:0'
    ];

    protected $messages = [
        'member.required' => 'نسبت خانوادگی را مشخص کنید',
        'member.in' => 'نسبت خانوادگی معتبر نمی باشد',
        'member.unique' => 'جوایز این نسبت خانوادگی قبلا تعریف شده است',
        'satisfaction.required' => 'مقدار رضایت را وارد کنید',
        'satisfaction.numeric' => 'مقدار رضایت صحیح نیست',
        'satisfaction.min' => 'کمترین مقدار رضایت 0 است',
        'introduction_profit_increase.required' => 'مقدار افزایش سود معرفی را وارد کنید',
        'introduction_profit_increase.min' => 'کمترین مقدار افزایش سود معرفی 0 می باشد',
        'introduction_profit_increase.numeric' => 'مقدار عددی وارد کنید',
        'accumulated_capital_reserve.required' => 'ذخیره سرمایه انباشته را وارد کنید',
        'accumulated_capital_reserve.min' => 'کمترین مقدار 0 می باشد',
        'accumulated_capital_reserve.numeric' => 'مقدار عددی وارد کنید',
        'data_storage.required' => 'مقدار ذخیره دیتا را وارد کنید',
        'data_storage.min' => 'کمترین مقدار 0 می باشد',
        'data_storage.numeric' => 'مقدار عددی وارد کنید',
        'psc.required' => 'میزان psc را وارد کنید',
        'psc.min' => 'کمترین مقدار 0 می باشد',
        'psc.numeric' => 'مقدار عددی وارد کنید'
    ];
    public function save()
    {
        $this->validate();

        DynastyPrize::create([
            'member' => $this->member,
            'satisfaction' => $this->satisfaction,
            'introduction_profit_increase' => $this->introduction_profit_increase / 100,
            'accumulated_capital_reserve' => $this->accumulated_capital_reserve / 100,
            'data_storage' => $this->data_storage / 100,
            'psc' => $this->psc,
        ]);

        session()->flash('success', 'پاداش با موفقیت ثبت شد');
        $this->resetExcept('prizes');
        $this->emitSelf('prizeCreated');
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function delete($id)
    {
        DynastyPrize::destroy($id);
        $this->emitSelf('prizeDeleted');
    }

    public function render()
    {
        return view('livewire.dynasty.prize', [
            'prizes' => DynastyPrize::paginate(10)
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}

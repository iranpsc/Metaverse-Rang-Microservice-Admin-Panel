<?php

namespace App\Livewire\Dynasty;

use App\Models\Dynasty\DynastyPrize;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class Prize extends Component
{
    use WithPagination;

    public $member, $satisfaction, $introduction_profit_increase, $accumulated_capital_reserve, $data_storage, $psc;

    protected $rules = [
        'member' => 'required|in:father,mother,brother,offspring,sister,husband,wife|unique:dynasty_prizes',
        'satisfaction' => 'required|numeric|min:0',
        'introduction_profit_increase' => 'required|numeric|min:0',
        'accumulated_capital_reserve' => 'required|numeric|min:0',
        'data_storage' => 'required|numeric|min:0',
        'psc' => 'required|numeric|min:0'
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

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
        $this->resetExcept('prizes');
    }

    public function delete(DynastyPrize $dynastyPrize)
    {
        $dynastyPrize->delete();
    }

    #[Title('جوایز سلسله')]
    public function render()
    {
        return view('livewire.dynasty.prize', [
            'prizes' => DynastyPrize::paginate(10)
        ]);
    }
}

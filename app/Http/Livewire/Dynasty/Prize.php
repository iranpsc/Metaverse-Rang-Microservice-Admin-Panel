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

        $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
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
        ]);
    }
}

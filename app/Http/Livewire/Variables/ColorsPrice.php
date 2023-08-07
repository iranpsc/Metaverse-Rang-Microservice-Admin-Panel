<?php

namespace App\Http\Livewire\Variables;

use Livewire\Component;
use App\Models\Variable;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;

class ColorsPrice extends Component
{
    use SendsVerificationSms;

    public $price, $asset;

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    protected $listeners = [
        'deleteCurrency' => 'delete',
        'currencyCreated' => '$refresh',
        'currencyUpdated' => '$refresh',
        'currencyDeleted' => '$refresh',
    ];

    protected $rules = [
        'price' => 'required|numeric|min:1',
        'asset' => 'required|in:red,blue,yellow,irr,psc,satisfaction|unique:variables',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function save()
    {
        $this->validate();

        Variable::create([
            'asset' => $this->asset,
            'price' => $this->price,
        ]);

        $this->dispatchBrowserEvent('resourceModified', ['message' => 'قیمت رنگ با موفقیت وارد شد']);
        $this->resetExcept('admin');
        $this->emitSelf('currencyCreated');
    }

    public function delete(Variable $variable)
    {
        $variable->priceChangeLogs()->delete();
        $variable->delete();
        $this->emitSelf('currencyDeleted');
        $this->emit('currencyDeleted');
    }

    public function render()
    {
        return view('livewire.variables.colors-price', [
            'variables' => Variable::with('priceChangeLogs')->get()
        ]);
    }
}

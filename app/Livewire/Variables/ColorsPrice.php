<?php

namespace App\Livewire\Variables;

use Livewire\Component;
use App\Models\Variable;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;

class ColorsPrice extends Component
{
    use SendsVerificationSms;

    public $price, $asset, $search;

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    protected function rules()
    {
        return [
            'price' => 'required|numeric|min:1',
            'asset' => 'required|in:red,blue,yellow,irr,psc,satisfaction|unique:variables',
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_phone_verification'
            ],
            'access_password' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_access_password'
            ],
        ];
    }

    public function save()
    {
        $this->validate();

        Variable::create([
            'asset' => $this->asset,
            'price' => $this->price,
        ]);

        $this->dispatch('notify', message: 'قیمت رنگ با موفقیت وارد شد');
        $this->resetExcept('admin');
    }

    public function delete(Variable $variable)
    {
        $variable->priceChangeLogs()->delete();
        $variable->delete();
    }

    #[Title('قیمت رنگ ها')]
    public function render()
    {
        return view('livewire.variables.colors-price', [
            'variables' => Variable::with('priceChangeLogs')->get()
        ]);
    }
}

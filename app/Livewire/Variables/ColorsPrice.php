<?php

namespace App\Livewire\Variables;

use Livewire\Component;
use App\Models\Variable;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;

class ColorsPrice extends Component
{
    use SendsVerificationSms, WithFileUploads;

    public $price, $asset, $search, $image;

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    protected function rules()
    {
        return [
            'price' => 'required|numeric|min:1',
            'asset' => 'required|in:red,blue,yellow,irr,psc,satisfaction,effect|unique:variables',
            'image' => 'required|image|max:1024',
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_verify_code'
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

        $variable = Variable::create([
            'asset' => $this->asset,
            'price' => $this->price,
        ]);

        $variable->image()->create([
            'url' => url('uploads/' . $this->image->store('variables', 'public'))
        ]);

        $this->dispatch('notify', message: 'قیمت رنگ با موفقیت وارد شد');
        $this->resetExcept('admin');
    }

    public function delete(Variable $variable)
    {
        $variable->priceChangeLogs()->delete();
        $variable->image()->delete();
        $variable->delete();
    }

    #[Title('قیمت رنگ ها')]
    public function render()
    {
        return view('livewire.variables.colors-price', [
            'variables' => Variable::with('priceChangeLogs', 'image')->get()
        ]);
    }
}

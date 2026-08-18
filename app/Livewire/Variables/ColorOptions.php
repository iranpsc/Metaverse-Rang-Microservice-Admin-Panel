<?php

namespace App\Livewire\Variables;

use App\Models\Option;
use Livewire\Component;
use App\Models\Variable;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ColorOptions extends Component
{
    use WithPagination, WithFileUploads, SendsVerificationSms;

    public $asset, $amount, $image, $code, $search;

    protected  function rules() {
        return [
            'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp,gif|max:2048',
            'amount' => 'required|integer|min:1',
            'asset' => 'required|in:red,blue,yellow,psc,irr',
            'code' => 'required|string|unique:options,code',
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

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $this->validate();

        $option = Option::create([
            'asset' => $this->asset,
            'amount' => $this->amount,
            'code' => $this->code,
        ]);

        if ($this->image) {
            $url = url('uploads/'.$this->image->store('packages', 'public'));
            $option->image()->create([
                'url' => $url,
            ]);
        }

        $this->resetExcept('admin');
        $this->dispatch('notify', message: 'پکیج رنگ وارد شد');
    }

    public function delete(Option $option)
    {
        $option->image()->delete();
        $option->priceChangeLogs()->delete();
        $option->delete();
    }

    #[Title('پکیج های رنگی')]
    public function render()
    {
        return view('livewire.variables.color-options', [
            'variables' => Variable::all('asset'),
            'options'   => Option::with('priceChangeLogs')->paginate(10)
        ]);
    }
}

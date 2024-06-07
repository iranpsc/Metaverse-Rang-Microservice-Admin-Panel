<?php

namespace App\Livewire\Variables\Edit;

use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EditColors extends Component
{
    use SendsVerificationSms;

    public $price, $note, $asset;

    public function mount($asset)
    {
        $this->asset = $asset;
        $this->price = $asset->price;
        $this->admin = Auth::guard('admin')->user();
    }

    protected function rules()
    {
        return [
            'price' => 'required|numeric|min:1',
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

    public function update()
    {
        $this->validate();

        $this->asset->priceChangeLogs()->create([
            'changer_name' => $this->admin->name,
            'previous_value' => $this->asset->price,
            'current_value' => $this->price,
            'note' => $this->note,
        ]);

        $this->asset->update([
            'price' => $this->price,
            'note' => $this->note
        ]);

        $this->dispatch('notify', message: 'ارز با موفقیت بروزرسانی شد');
    }

    public function render()
    {
        return view('livewire.variables.edit.edit-colors');
    }
}

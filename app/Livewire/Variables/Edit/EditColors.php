<?php

namespace App\Livewire\Variables\Edit;

use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class EditColors extends Component
{
    use SendsVerificationSms, WithFileUploads;

    public $price, $note, $asset, $image;

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
            'image' => 'nullable|image|max:1024',
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

        if ($this->image) {
            $this->asset->image()->delete();
            $this->asset->image()->create([
                'url' => url('uploads/' . $this->image->store('variables', 'public'))
            ]);
        }

        $this->dispatch('notify', message: 'ارز با موفقیت بروزرسانی شد');
    }

    public function render()
    {
        return view('livewire.variables.edit.edit-colors');
    }
}

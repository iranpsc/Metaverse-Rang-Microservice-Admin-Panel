<?php

namespace App\Http\Livewire\Variables\Edit;

use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;

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

    protected $rules = [
        'price' => 'required|numeric|min:1',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

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

        session()->flash('success', 'ارز بروزرسانی شد');
        $this->emitUp('currencyUpdated');
        $this->emit('currencyUpdated');
    }

    public function render()
    {
        return view('livewire.variables.edit.edit-colors');
    }
}

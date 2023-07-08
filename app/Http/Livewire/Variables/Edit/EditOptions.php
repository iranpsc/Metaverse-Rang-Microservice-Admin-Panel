<?php

namespace App\Http\Livewire\Variables\Edit;

use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class EditOptions extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $option, $code, $asset, $amount, $image, $note;

    public function mount($option)
    {
        $this->option = $option;
        $this->asset = $option->asset;
        $this->amount = $option->amount;
        $this->code = $option->code;
        $this->admin = Auth::guard('admin')->user();
    }

    protected $rules = [
        'amount' => 'required|numeric|min:1',
        'code' => 'required|string',
        'note' => 'required',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp',
        'code' => 'required|string',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function update()
    {
        $this->validate();

        $this->option->priceChangeLogs()->create([
            'changer_name' => $this->admin->name,
            'previous_value' => $this->option->amount,
            'current_value' => $this->amount,
            'note' => $this->note,
        ]);

        $this->option->update([
            'asset' => $this->asset,
            'amount' => $this->amount,
            'note' => $this->note,
            'code' => $this->code,
        ]);

        if ($this->image) {
            $url = url('uploads/'.$this->image->store('packages', 'public'));

            if ($this->option->image) {
                $this->option->image->update([
                    'url' => $url,
                ]);
            } else {
                $this->option->image()->create([
                    'url' => $url,
                ]);
            }
        }
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'بسته بروزرسانی شد']);
        $this->emitUp('packageUpdated');
        $this->emit('packageUpdated');
    }

    public function render()
    {
        return view('livewire.variables.edit.edit-options');
    }
}

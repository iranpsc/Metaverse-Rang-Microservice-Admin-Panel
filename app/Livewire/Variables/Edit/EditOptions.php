<?php

namespace App\Livewire\Variables\Edit;

use App\Models\Option;
use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use App\Models\Variable;
use Illuminate\Validation\Rule;

class EditOptions extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public Option $option;

    public $code, $asset, $amount, $image, $note;

    public function mount()
    {
        $this->asset = $this->option->asset;
        $this->amount = $this->option->amount;
        $this->code = $this->option->code;
        $this->admin = Auth::guard('admin')->user();
    }

    protected function rules()
    {
        return [
            'amount' => 'required|numeric|min:1',
            'code' => 'required|string',
            'note' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp,gif|max:2048',
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
            $url = url('uploads/' . $this->image->store('packages', 'public'));

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
        $this->dispatch('notify', message: 'بسته بروزرسانی شد');
    }

    public function render()
    {
        return view('livewire.variables.edit.edit-options', [
            'variables' => Variable::all(),
        ]);
    }
}

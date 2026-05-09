<?php

namespace App\Livewire\SystemVariables;

use App\Models\SystemVariable;
use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Update extends Component
{
    use SendsVerificationSms;

    public SystemVariable $variable;

    public $slug, $name, $value, $note;

    protected function rules()
    {
        return [
            'slug' => 'required|string',
            'name' => 'required|string',
            'value' => 'required|numeric',
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
        $this->name = $this->variable->name;
        $this->slug = $this->variable->slug;
        $this->value = $this->variable->value;
        $this->admin = Auth::guard('admin')->user();
    }

    public function update()
    {
        $this->validate();

        $this->variable->changeLogs()->create([
            'changer_name' => $this->admin->name,
            'previous_value' => $this->variable->value,
            'current_value' => $this->value,
            'note' => $this->note,
        ]);

        $this->variable->update([
            'slug' => $this->slug,
            'name' => $this->name,
            'value' => $this->value,
        ]);

        $this->dispatch('notify', message: 'متغیر سیستم ویرایش شد');
        $this->reset(['phone_verification', 'access_password']);
    }

    public function render()
    {
        return view('livewire.system-variables.update');
    }
}

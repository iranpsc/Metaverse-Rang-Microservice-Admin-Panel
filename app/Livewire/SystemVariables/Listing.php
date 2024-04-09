<?php

namespace App\Livewire\SystemVariables;

use App\Models\SystemVariable;
use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

class Listing extends Component
{
    use SendsVerificationSms, WithPagination;

    public $slug, $name, $value, $search;

    protected $rules = [
        'slug' => 'required|string',
        'name' => 'required|string|min:2',
        'value' => 'required|numeric|min:0',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function save()
    {
        $this->validate();

        SystemVariable::create([
            'slug' => $this->slug,
            'name' => $this->name,
            'value' => $this->value,
        ]);

        $this->dispatch('notify', message: 'متغیر سیستم تعریف شد');
        $this->resetExcept('admin');
    }

    public function delete(SystemVariable $systemVariable)
    {
        $systemVariable->changeLogs()->delete();
        $systemVariable->delete();
    }

    #[Title('لیست متغیرهای سیستم')]
    public function render()
    {
        return view('livewire.system-variables.listing', [
            'variables' => SystemVariable::with('changeLogs')->paginate(10)
        ]);
    }
}

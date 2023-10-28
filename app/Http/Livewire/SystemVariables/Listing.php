<?php

namespace App\Http\Livewire\SystemVariables;

use App\Models\SystemVariable;
use Livewire\Component;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Listing extends Component
{
    use SendsVerificationSms, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $slug, $name, $value, $search;

    protected $rules = [
        'slug' => 'required|string',
        'name' => 'required|string|min:2',
        'value' => 'required|numeric|min:0',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    protected $listeners = [
        'variableCreated' => '$refresh',
        'variableUpdated' => '$refresh',
        'variableDeleted' => '$refresh',
        'deleteSystemVariable' => 'delete',
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function save()
    {
        $this->validate();

        SystemVariable::create([
            'slug' => $this->slug,
            'name' => $this->name,
            'value' => $this->value,
        ]);
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'متغیر سیستم تعریف شد']);
        $this->resetExcept('admin');
        $this->emitSelf('variableCreated');
    }

    public function delete(SystemVariable $systemVariable)
    {
        $systemVariable->changeLogs()->delete();
        $systemVariable->delete();
        session()->flash('success', 'متغییر حذف شد!');
        $this->emitSelf('variableDeleted');
    }

    public function render()
    {
        return view('livewire.system-variables.listing', [
            'variables' => SystemVariable::with('changeLogs')->simplePaginate(10)
        ]);
    }
}

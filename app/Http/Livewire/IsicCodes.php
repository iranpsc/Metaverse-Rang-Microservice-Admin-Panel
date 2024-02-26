<?php

namespace App\Http\Livewire;

use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\IsicCode;
use App\Imports\IsicCodesImport;
use Maatwebsite\Excel\Facades\Excel;

class IsicCodes extends LivewireComponent
{
    use WithPagination, WithFileUploads;

    public $search = '', $code, $name, $is_editing = false, $verified = true, $import = null;
    private $isic_codes = null;

    protected $rules = [
        'code' => 'nullable|numeric',
        'name' => 'nullable|string|max:255',
        'verified' => 'nullable|boolean',
        'import' => 'nullable|file|mimes:xlsx,xls',
    ];

    public function updatedSearch()
    {
        $this->resetPage();

        $this->isic_codes = IsicCode::where('code', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->orderBy('verified')
            ->paginate(10);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function store()
    {
        $this->validate(array_merge($this->rules, $this->getVerficationRules()));

        if ($this->import) {
            $this->import->store('isic_codes', 'public');
            Excel::import(new IsicCodesImport, $this->import);
        } else {
            IsicCode::create([
                'code' => $this->code,
                'name' => $this->name,
                'verified' => $this->verified,
            ]);
        }

        $this->emitSelf('eventCreated');
        $this->dispatchBrowserEvent('closeCreateModal');
        $this->resetExcept('admin');
    }

    public function edit($id)
    {
        $isic_code = IsicCode::find($id);
        $this->code = $isic_code->code;
        $this->name = $isic_code->name;
        $this->is_editing = true;

        $this->dispatchBrowserEvent('openEditModal', [
            'id' => $isic_code->id,
        ]);
    }

    public function update($id)
    {
        array_merge($this->rules, $this->getVerficationRules());

        IsicCode::find($id)->update([
            'code' => $this->code,
            'name' => $this->name,
            'verified' => $this->verified,
        ]);

        $this->resetExcept('admin');
        $this->is_editing = false;
        $this->emitSelf('eventUpdated');
        $this->dispatchBrowserEvent('closeEditModal');
        $this->resetExcept('admin');
    }

    public function delete($id)
    {
        IsicCode::find($id)->delete();
        $this->emitSelf('eventDeleted');
    }

    public function render()
    {
        return view('livewire.isic-codes')
            ->with('isic_codes', $this->isic_codes ?? IsicCode::orderBy('verified')->paginate(10));
    }
}

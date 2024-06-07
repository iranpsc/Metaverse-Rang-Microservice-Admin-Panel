<?php

namespace App\Livewire;

use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\IsicCode;
use App\Imports\IsicCodesImport;
use App\Traits\SendsVerificationSms;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;

class IsicCodes extends Component
{
    use WithPagination, WithFileUploads, SendsVerificationSms;

    public $search = '', $code, $name, $is_editing = false, $verified = true, $import = null;
    private $isic_codes = null;

    public function updatedSearch()
    {
        $this->resetPage();

        $this->isic_codes = IsicCode::where('code', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->orderBy('verified')
            ->paginate(10);
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|numeric',
            'name' => 'required|string|max:255',
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
        ]);

        IsicCode::create([
            'code' => $this->code,
            'name' => $this->name,
            'verified' => $this->verified,
        ]);

        $this->dispatch('notify', message: 'کد جدید با موفقیت ایجاد شد.');
        $this->resetExcept('admin');
    }

    public function import()
    {
        $this->validate([
            'import' => 'required|file|mimes:xlsx,xls',
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
        ]);

        $this->import->store('isic_codes', 'public');
        Excel::import(new IsicCodesImport, $this->import);

        $this->dispatch('notify', message: 'کدهای جدید با موفقیت ایمپورت شدند.');
        $this->resetExcept('admin');
    }

    public function deny(IsicCode $isic_code)
    {
        $isic_code->update(['verified' => false]);
        $this->dispatch('notify', message: 'کد مورد نظر تایید نشد.');
    }

    public function approve(IsicCode $isic_code)
    {
        $isic_code->update([
            'code' => random_int(1000000, 9999999),
            'verified' => true
        ]);
        $this->dispatch('notify', message: 'کد مورد نظر تایید شد.');
    }

    public function delete(IsicCode $isic_code)
    {
        $isic_code->delete();
        $this->dispatch('notify', message: 'کد مورد نظر حذف شد.');
    }

    #[Title('کدهای ISIC')]
    public function render()
    {
        return view('livewire.isic-codes')->with('isic_codes', $this->isic_codes
            ?? IsicCode::orderBy('verified')->paginate(10));
    }
}

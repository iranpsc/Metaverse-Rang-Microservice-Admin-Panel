<?php

namespace App\Livewire\Employees;

use Livewire\Component;
use App\Models\BankAccount;
use App\Models\Employee\Employee;
use Illuminate\Support\Facades\Auth;
use App\Traits\SendsVerificationSms;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

class Bank extends Component
{
    use SendsVerificationSms, WithPagination;

    public $employee, $bank_name, $shaba_num, $card_num, $search;
    private $bankAccounts;  // for pagination

    protected $rules = [
        'employee' => 'required|integer|exists:admins,id',
        'bank_name' => 'required|string|max:255',
        'shaba_num' => 'required|ir_sheba',
        'card_num' => 'required|ir_bank_card_number',
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

        $employee = Employee::findOrFail($this->employee);

        $employee->bankAccounts()->create([
            'bank_name' => $this->bank_name,
            'shaba_num' => $this->shaba_num,
            'card_num' => $this->card_num,
        ]);

        $this->resetExcept('admin');
        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }

    public function delete(BankAccount $account)
    {
        $account->delete();
    }

    public function updatedSearch()
    {
        $this->bankAccounts = BankAccount::whereHas('bankable', function ($query) {
            $query->where('fname', 'like', '%' . $this->search . '%')
                ->orWhere('lname', 'like', '%' . $this->search . '%');
        })->paginate(10);
    }

    #[Title('حساب های بانکی')]
    public function render()
    {
        return view('livewire.employees.bank', [
            'employees' => Employee::all(),
            'bankAccounts' => $bankAccounts ?? Bankaccount::paginate(10)
        ]);
    }
}

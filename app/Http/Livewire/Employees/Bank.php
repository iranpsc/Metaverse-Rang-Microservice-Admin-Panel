<?php

namespace App\Http\Livewire\Employees;

use Livewire\Component;
use App\Models\BankAccount;
use App\Models\Employee\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Helpers\SMS;

class Bank extends Component
{
    public $employee, $admin, $bank_name, $shaba_num, $card_num, $code, $access_password;

    protected $rules = [
        'employee' => 'required|integer|exists:admins,id',
        'bank_name' => 'required|string|max:255',
        'shaba_num' => 'required|ir_sheba',
        'card_num' => 'required|ir_bank_card_number',
        'code' => 'required|integer',
        'access_password' => 'required'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
    }

    public function sendSMS()
    {
        $verify_code = random_int(100000, 999999);
        Cache::put('verify-code-'.$this->admin->id, Hash::make($verify_code), now()->addMinutes(5));
        $result = SMS::send($this->admin->phone, $verify_code);
        if (is_array($result)) {
            foreach ($result as $r) {
                session()->flash('success', $r->statustext);
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
        }
    }

    public function save()
    {
        $this->validate();
        if (!Hash::check($this->phoneVerification, Cache::get('verify-code-'.$this->admin->id))) {
            $this->addError('phoneVerification', 'کد تایید وارد شده صحیح نمی باشد');
        } else if (!password_verify($this->access_password, $this->admin->access_password)) {
            $this->addError('access_password', 'رمز دسترسی صحیح نمی باشد');
        } else {
            $employee = Employee::findOrFail($this->employee);

            $employee->bankAccounts()->create([
                'bank_name' => $this->bank_name,
                'shaba_num' => $this->shaba_num,
                'card_num' => $this->card_num,
            ]);

            $this->resetExcept('admin');
            Cache::forget('verify-code-'.$this->admin->id);
            session()->flash('success', 'اطلاعات با موفقیت ثبت شد');
            $this->emitSelf('accountCreated');
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        return view('livewire.employees.bank', [
            'employees' => Employee::all(),
            'bankAccounts' => Bankaccount::paginate(10)
        ])
            ->extends('layouts.app')
            ->section('content');;
    }
}

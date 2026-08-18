<?php

namespace App\Livewire\Employees\Edit;

use App\Models\BankAccount;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\SendsVerificationSms;

class Bank extends Component
{
    use SendsVerificationSms;

    public BankAccount $account;

    public $bank_name, $shaba_num, $card_num;

    protected $rules = [
        'bank_name' => 'required|string|max:255',
        'shaba_num' => 'required|ir_sheba',
        'card_num' => 'required|ir_bank_card_number',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();
        $this->bank_name = $this->account->bank_name;
        $this->shaba_num = $this->account->shaba_num;
        $this->card_num = $this->account->card_num;
    }

    public function save()
    {
        $this->validate();
        $this->account->update($this->only(['bank_name', 'shaba_num', 'card_num']));

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
    }

}

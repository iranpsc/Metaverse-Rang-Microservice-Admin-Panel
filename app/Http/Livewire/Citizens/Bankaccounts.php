<?php

namespace App\Http\Livewire\Citizens;

use App\Models\BankAccount;
use Livewire\Component;
use App\Notifications\KycDeniedNotification;

class Bankaccounts extends Component
{
    public $bankAccounts, $searchTerm;

    public $bank_name_err;
    public $shaba_num_err;
    public $card_num_err;

    public $bankAccount_errors = [];

    public function mount() {
        $this->bankAccounts = BankAccount::with('errors', 'bankable')->latest()->get();
    }

    public function updated() {
        $this->bankAccounts = BankAccount::where('card_num', 'like', '%' . $this->searchTerm . '%')
        ->orWhere('shaba_num', 'like', '%' . $this->searchTerm . '%')
        ->get();
    }

    public function save_errors($input, BankAccount $bankAccount)
    {

        switch ($input) {
            case 'bank_name_err':
                array_push($this->bankAccount_errors, ['bank_name' => $this->bank_name_err]);
                break;
            case 'card_num_err':
                array_push($this->bankAccount_errors, ['card_num' => $this->card_num_err]);
                break;
            case 'shaba_num_err':
                array_push($this->bankAccount_errors, ['shaba_num' => $this->shaba_num_err]);
                break;
            default:
        }
    }

    public function save(BankAccount $bankAccount) {
        if (!empty($this->bankAccount_errors)) {
            for ($i = 0; $i < count($this->bankAccount_errors); $i++) {
                $arr = $this->bankAccount_errors[$i];
                foreach ($arr as $key => $value) {
                    $bankAccount->errors()->create([
                        'key' => $key,
                        'value' => $value
                    ]);
                }
            }
            $bankAccount->update([
                'status' => -1
            ]);

            $user = $bankAccount->bankable;
            $message = 'حساب بانکی تایید نشد.';
            $user->notify(new KycDeniedNotification($message));
            session()->flash('error', 'حساب بانکی تایید نشد.');

        } else {
            $bankAccount->update([
                'status' => 1
            ]);

            session()->flash('success', 'حساب بانکی تایید شد.');
        }
    }

    public function render()
    {
        return view('livewire.citizens.bankaccounts')
        ->extends('layouts.app')
        ->section('content');
    }
}

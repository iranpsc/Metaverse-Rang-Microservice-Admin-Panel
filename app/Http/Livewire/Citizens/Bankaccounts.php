<?php

namespace App\Http\Livewire\Citizens;

use App\Models\BankAccount;
use Livewire\Component;
use App\Notifications\KycDeniedNotification;
use Livewire\WithPagination;

class Bankaccounts extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $searchTerm;

    public $bank_name_err;
    public $shaba_num_err;
    public $card_num_err;

    public $bankAccount_errors = [];

    private $bankAccounts = null;

    public function updated()
    {
        $this->bankAccounts = BankAccount::where('card_num', 'like', '%' . $this->searchTerm . '%')
            ->orWhere('shaba_num', 'like', '%' . $this->searchTerm . '%')
            ->paginate(10);
    }

    public function save_errors($input)
    {
        $this->bankAccount_errors[] = [
            'name' => $input,
            'message' => $this->{$input}
        ];
    }

    public function save(BankAccount $bankAccount)
    {
        if (!empty($this->bankAccount_errors)) {
            $bankAccount->update([
                'status' => -1,
                'errors' => $this->bankAccount_errors
            ]);

            $this->bankAccount_errors = [];

            $user = $bankAccount->bankable;
            $message = 'حساب بانکی تایید نشد.';
            $user->notify(new KycDeniedNotification($message));
            $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
        } else {
            $bankAccount->update([
                'status' => 1
            ]);

            $this->dispatchBrowserEvent('resourceModified', ['message' => 'اطلاعات با موفقیت ثبت شد']);
        }
    }

    public function render()
    {
        return view('livewire.citizens.bankaccounts')
            ->with('bankAccounts', $this->bankAccounts ?? BankAccount::with('bankable')->latest()->paginate(10));
    }
}

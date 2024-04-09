<?php

namespace App\Livewire\Citizens;

use App\Livewire\Citizens\Kyc as CitizensKyc;
use App\Models\Kyc;
use App\Notifications\KycDeniedNotification;
use Livewire\Component;

class KycDetails extends Component
{

    public Kyc $kyc;

    public $fname_err;
    public $lname_err;
    public $father_name_err;
    public $melli_code_err;
    public $province_err;
    public $city_err;
    public $number_err;
    public $postal_code_err;
    public $address_err;
    public $melli_card_err;
    public $prove_picture_err;
    public $resume_err;

    public $kyc_errors = [];

    public function save_errors($input)
    {
        $this->kyc_errors[] = [
            'name' => $input,
            'message' => $this->{$input}
        ];
    }


    public function save()
    {
        if (!empty($this->kyc_errors)) {

            $this->kyc->update([
                'status' => -1,
                'errors' => $this->kyc_errors
            ]);

            $this->kyc_errors = [];

            $user = $this->kyc->user;
            $message = 'احراز هویت شما تایید نشد';
            $user->notify(new KycDeniedNotification($message));
        } else {

            $this->kyc->update([
                'status' => 1,
                'errors' => null,
            ]);
        }

        $this->dispatch('notify', message: 'اطلاعات با موفقیت ثبت شد');
        $this->dispatch('kycReviewed')->to(CitizensKyc::class);
    }

    public function render()
    {
        return view('livewire.citizens.kyc-details');
    }

}

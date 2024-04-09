<?php

namespace App\Traits;

use App\Notifications\SendVerificationCode;
use Livewire\Attributes\Rule;

trait SendsVerificationSms
{
    #[Rule('required|is_valid_verify_code')]
    public $phone_verification;

    #[Rule('required|is_valid_access_password')]
    public $access_password;

    public $admin;

    public $countdownTime = 60; // in seconds

    public function sendSMS(string $id)
    {
        $this->dispatch(
            'start-countdown',
            id: $id,
            countdownTime: $this->countdownTime,
        );
        $this->admin->notify(new SendVerificationCode);
        $this->dispatch('notify', message: 'کد تایید با موفقیت ارسال گردید');
    }
}

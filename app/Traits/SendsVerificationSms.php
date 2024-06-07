<?php

namespace App\Traits;

use App\Notifications\SendVerificationCode;

trait SendsVerificationSms
{
    public $phone_verification;

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

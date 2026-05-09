<?php

namespace App\Traits;

use App\Models\Admin;
use App\Notifications\SendVerificationCode;
use Illuminate\Support\Facades\Auth;
trait SendsVerificationSms
{
    public $phone_verification;

    public $access_password;

    public Admin $admin;

    public $countdownTime = 60; // in seconds

    public function sendSMS(string $id)
    {
        $this->admin = Auth::user();

        $this->dispatch(
            'start-countdown',
            id: $id,
            countdownTime: $this->countdownTime,
        );

        $this->admin->notify(new SendVerificationCode);
        $this->dispatch('notify', message: 'کد تایید با موفقیت ارسال گردید');
    }
}

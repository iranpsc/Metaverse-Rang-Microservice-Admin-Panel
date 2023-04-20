<?php

namespace App\Traits;

use App\Notifications\SendVerificationCode;

trait SendsVerificationSms
{
    public $access_password, $phone_verification, $admin;

    public function sendSMS()
    {
        $this->admin->notify(new SendVerificationCode);
        session()->flash('success', 'کد تایید با موفقیت ارسال گردید.');
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }
}

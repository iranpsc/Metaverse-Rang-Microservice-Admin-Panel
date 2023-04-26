<?php

namespace App\Traits;

use App\Notifications\SendVerificationCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait SendsVerificationSms
{
    public $access_password, $phone_verification, $admin;

    public function sendSMS()
    {
        $this->admin->notify(new SendVerificationCode);
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'کد تایید با موفقیت ارسال گردید']);
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function clearVerificationCode()
    {
        Cache::forget('verify.code.' . Auth::guard('admin')->id());
    }
}

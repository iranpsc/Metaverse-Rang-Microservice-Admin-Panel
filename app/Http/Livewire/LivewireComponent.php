<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Notifications\SendVerificationCode;
use Illuminate\Validation\Rule;

abstract class LivewireComponent extends Component
{
    public $access_password, $phone_verification, $admin;

    protected $countdownTime = 60; // in seconds

    protected $paginationTheme = 'bootstrap';

    public function getVerficationRules()
    {
        return [
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_verify_code'
            ],
            'access_password' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_access_password'
            ]
        ];
    }

    public function sendSMS(string $id)
    {
        $this->dispatchBrowserEvent('start-countdown', [
            'id' => $id,
            'countdownTime' => $this->countdownTime,
        ]);
        $this->admin->notify(new SendVerificationCode);
        $this->dispatchBrowserEvent('resourceModified', ['message' => 'کد تایید با موفقیت ارسال گردید']);
    }

    public function clearVerificationCode()
    {
        Cache::forget('verify.code.' . Auth::guard('admin')->id());
    }

    public function mount()
    {
        $this->admin = auth()->guard('admin')->user();
    }

    public abstract function store();
    public abstract function edit($id);
    public abstract function update($id);
    public abstract function delete($id);
    public abstract function render();

    public function resetForm()
    {
        $this->resetExcept('admin');
    }
}

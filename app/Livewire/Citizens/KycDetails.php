<?php

namespace App\Livewire\Citizens;

use App\Livewire\Citizens\Kycs;
use App\Models\Kyc;
use App\Notifications\KycDeniedNotification;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Validation\Rule;

class KycDetails extends Component
{
    use SendsVerificationSms;

    public Kyc $kyc;

    public $fname_err;
    public $lname_err;
    public $melli_code_err;
    public $birthdate_err;
    public $province_err;
    public $melli_card_err;
    public $video_err;
    public $gender_err;

    public $kyc_errors = [];

    protected function rules()
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
            ],
        ];
    }

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();

        $this->kyc->load('verifyText', 'user');
    }

    public function save_errors($input)
    {
        $this->kyc_errors[] = [
            'name' => $input,
            'message' => $this->{$input}
        ];
    }


    public function save()
    {
        $this->validate();

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
        $this->dispatch('kycReviewed')->to(Kycs::class);
    }

    public function render()
    {
        return view('livewire.citizens.kyc-details');
    }
}

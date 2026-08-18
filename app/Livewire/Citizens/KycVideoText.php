<?php

namespace App\Livewire\Citizens;

use App\Models\KycVerifyText;
use App\Traits\SendsVerificationSms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Validation\Rule;

class KycVideoText extends Component
{
    use SendsVerificationSms;

    public $text;

    protected function rules()
    {
        return [
            'text' => 'required|string',
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
    }

    public function save()
    {
        $this->validate();

        KycVerifyText::create([
            'text' => $this->text,
        ]);

        $this->dispatch('notify', message: 'متن احراز ویدیویی با موفقیت ثبت شد.', type: 'success');
        $this->reset('text', 'phone_verification', 'access_password');
    }

    public function delete($id)
    {
        KycVerifyText::find($id)->delete();
        $this->dispatch('notify', message: 'متن احراز ویدیویی با موفقیت حذف شد.', type: 'success');
    }

    public function render()
    {
        return view('livewire.citizens.kyc-video-text', [
            'texts' => KycVerifyText::latest()->get()
        ]);
    }
}

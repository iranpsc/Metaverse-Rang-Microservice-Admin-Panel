<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use App\Traits\SendsVerificationSms;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;

class Profile extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $name, $email, $image, $new_access_password,
        $new_access_password_confirmation, $password, $password_confirmation;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'image' => 'nullable|image|max:1024',
            'password' => 'nullable|confirmed',
            'new_access_password' => 'nullable|confirmed',
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

        $this->name = $this->admin->name;
        $this->email = $this->admin->email;
    }

    public function save()
    {
        $this->validate();

        $url = $this->image
            ? url('uploads/' . $this->image->store('profile', 'public'))
            : $this->admin->image;

        $this->admin->update([
            'name' => $this->name,
            'email' => $this->email,
            'image' => $url,
            'access_password' => $this->new_access_password
                ? Hash::make($this->new_access_password)
                : $this->admin->access_password,
            'password' => $this->password ? Hash::make($this->password) : $this->admin->password
        ]);
        $this->dispatch('notify', ['message' => 'اطلاعات بروزرسانی شد']);
    }

    #[Title('پروفایل')]
    public function render()
    {
        return view('livewire.profile');
    }
}

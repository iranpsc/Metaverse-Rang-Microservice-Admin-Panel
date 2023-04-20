<?php

namespace App\Http\Livewire\Panel;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use App\Traits\SendsVerificationSms;

class Profile extends Component
{
    use WithFileUploads, SendsVerificationSms;

    public $name, $email, $image, $new_access_password,
        $new_access_password_confirmation, $password;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'image' => 'nullable|image|max:1024',
        'password' => 'nullable|confirmed',
        'new_access_password' => 'nullable|confirmed',
        'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
        'access_password' => 'required|is_valid_access_password'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();

        $this->name = $this->admin->name;
        $this->email = $this->admin->email;
    }

    public function save()
    {
        $this->validate();

        $url = $this->image ? url('uploads/' . $this->image->store('profile', 'public')) : $this->admin->image;

        $this->admin->update([
            'name' => $this->name,
            'email' => $this->email,
            'image' => $url,
            'access_password' => $this->new_access_password ? Hash::make($this->new_access_password) : $this->admin->access_password,
            'password' => $this->password ? Hash::make($this->password) : $this->admin->password
        ]);
        session()->flash('success', 'اطلاعات بروزرسانی شد.');
    }

    public function render()
    {
        return view('livewire.panel.profile')
            ->extends('layouts.app')
            ->section('content');
    }
}

<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Models\Admin;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends Component
{
    public $current_password,$password,$password_confirmation,$admin;

    public function __construct()
    {
        $this->admin = Admin::firstWhere('id',auth()->user()->id);
    }

    protected $rules = [
        'current_password' => 'required',
        'password' => 'required|confirmed',
    ];

    protected $messages = [
        'current_password.required' => 'رمز عبور فعلی خود را وارد کنید',
        'password.required' => 'رمز عبور جدید را وارد کنید',
        'password.confirmed' => 'رمز عبور جدید با تکرار آن تطابق ندارد',
    ];
    public function save()
    {
        $this->validate();
        $pattern = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*|''])(?=.*[0-9]).{8,}$/";
        if(! preg_match($pattern, $this->password))
        {
            throw ValidationException::withMessages([
                'password' => 'رمز عبور با شامل اعدادو حروف بزرگ و کوچک باشد و حداقل 8 کاراکتر و شامل حروف خاص باشد'
            ]);
        }

        if(! Hash::check($this->current_password, $this->admin->password))
        {
            throw ValidationException::withMessages([
                'current_password' => 'رمز عبور وارد شده صحیح نمی باشد'
            ]);
        }

        $this->admin->update([
            'password' => Hash::make($this->password)
        ]);

        session()->flash('success', 'رمز عبور با موفقیت تغییر کرد');
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }
}

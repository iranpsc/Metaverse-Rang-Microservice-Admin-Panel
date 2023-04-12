<?php

namespace App\Http\Livewire\Panel;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Helpers\SMS;

class Profile extends Component
{
    use WithFileUploads;

    public $name, $email, $image, $admin, $access_password, $new_access_password, $code,
    $new_access_password_confirmation;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'image' => 'nullable|image|max:1024',
        'access_password' => 'required',
        'code' => 'required|integer',
        'new_access_password' => 'nullable|confirmed'
    ];

    public function mount()
    {
        $this->admin = Auth::guard('admin')->user();

        $this->name = $this->admin->name;
        $this->email = $this->admin->email;
    }

    public function sendSMS()
    {
        $verifyCode = random_int(10000, 99999);
        Cache::put('verify-code-' . $this->admin->id, Hash::make($verifyCode), now()->addMinutes(2));
        $result = SMS::send($this->admin->phone, $verifyCode);

        if (is_array($result)) {
            foreach ($result as $r) {
                session()->flash('success', $r->statustext);
            }
        } else {
            session()->flash('error', explode(":", $result)[1]);
            Cache::forget('verify-code-' . $this->admin->id);
        }
    }

    public function save()
    {
        $this->validate();

        if (!Hash::check($this->code, Cache::get('verify-code-' . $this->admin->id))) {
            $this->addError('code', 'کد تایید وارد شده صحیح نیست');
        } else if (!Hash::check($this->access_password, $this->admin->access_password)) {
            $this->addError('access_password', 'رمز دسترسی صحیح نیست');
        } else {
            $url = $this->image ? url('uploads/' . $this->image->store('profile', 'public')) : $this->admin->image;

            $this->admin->update([
                'name' => $this->name,
                'email' => $this->email,
                'image' => $url,
                'access_password' => $this->new_access_password ? Hash::make($this->new_access_password) : $this->admin->access_password
            ]);
            Cache::forget('verify-code-' . $this->admin->id);
            session()->flash('success', 'اطلاعات بروزرسانی شد.');
        }
    }

    public function updated($prop)
    {
        $this->validateOnly($prop);
    }

    public function render()
    {
        return view('livewire.panel.profile')
            ->extends('layouts.app')
            ->section('content');
    }
}

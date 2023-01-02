<?php

namespace App\Traits;

use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;

trait ChangePassword
{

    public function showChangeForm()
    {
        return view('auth.passwords.change');
    }

    public function change(ChangePasswordRequest $request)
    {
        $request->user()->update([
            'password' => Hash::make($request->password)
        ]);
        return to_route('password.change')->with('status', 'رمز عبور بروزرسانی شد.');
    }
}

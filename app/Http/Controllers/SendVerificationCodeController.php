<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\SendVerificationCode;

class SendVerificationCodeController extends Controller
{
    public function send(Request $request)
    {
        $request->user()->notify(new SendVerificationCode);
        return response()->json([], 200);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
            'access_password' => 'required|is_valid_access_password'
        ]);
        return response()->json([], 200);
    }
}

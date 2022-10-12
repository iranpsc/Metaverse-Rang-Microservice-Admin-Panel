<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = Admin::where('email', $request->email)->first();

        if(! $user) {
            return back()->with('error', [
                'type' => 'danger',
                'message' => 'کاربر با این آدرس ایمیل یافت نشد'
            ]);
        }

        if($user->active == 0) {
            return back()->with('error', [
                'type' => 'danger',
                'message' => 'کاربر مسدود است'
            ]);
        }

        if (RateLimiter::remaining('login:' . $request->ip(), $perMinute = 3)) {
            RateLimiter::hit('login:' . $request->ip());
            if (Auth::guard('admin')->attempt($credentials)) {
                $request->session()->regenerate();
                return redirect()->intended('/dashboard')->with('success', 'شما وارد شدید');
            }
            return back()->with('error', [
                'type' => 'danger',
                'message' => 'ایمیل یا رمز عبور اشتباه است'
            ]);
        } else {
            $user->update(['active' => 0]);
            return back()->with('error', [
                'type' => 'danger',
                'message' => 'حساب کاربری مسدود شد'
            ]);
        }


    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}

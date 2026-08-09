<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLoggerService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    public function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    protected function resetPassword($user, $password)
    {
        // Cannot call parent::resetPassword — Controller::__call intercepts trait methods.
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));
        $user->save();

        event(new PasswordReset($user));

        $this->guard()->login($user);

        Auth::guard('admin')->setUser($user);

        ActivityLoggerService::logAuth('password_reset', 'رمز عبور با موفقیت تغییر یافت', [
            'email' => $user->email,
        ]);
    }
}

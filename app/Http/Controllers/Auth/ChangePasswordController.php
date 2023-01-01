<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ChangePassword;

class ChangePasswordController extends Controller
{

    use ChangePassword;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:admin', 'password.confirm']);
    }

}

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::controller(AuthController::class)->middleware('check.ip')->group(function () {
    Route::get('/login', 'showLoginForm')->name('showLoginForm');
    Route::post('/login', 'login')->name('login');
    Route::get('/logout', 'logout')->name('logout');
});

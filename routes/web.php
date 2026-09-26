<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

Route::get('/lang/{file}', function ($file) {
    return response()->file(public_path('lang/' . $file));
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*')->name('home');

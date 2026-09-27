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

Route::get('/lang/{file}', function (string $file) {
    $path = storage_path('app/lang/'.basename($file));

    abort_unless(is_file($path), 404);

    return response()->file($path);
})->where('file', '[A-Za-z0-9._-]+\.json');

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*')->name('home');

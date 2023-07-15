<?php

use App\Http\Controllers\Api\V1\TranslationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(TranslationController::class)->group(function() {
    Route::get('/translations', 'index');
    Route::get('/translations/{translation}/modals', 'getModals');
    Route::get('/modals/{modal}/tabs', 'getTabs');
    Route::get('/tabs/{tab}/fields', 'getFields');
})->name('api.translations');

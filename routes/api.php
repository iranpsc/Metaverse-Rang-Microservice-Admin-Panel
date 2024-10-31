<?php

use App\Http\Controllers\Api\V1\TranslationController;
use App\Models\KycVerifyText;
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

Route::controller(TranslationController::class)->prefix('translations')->group(function () {
    Route::get('/', 'index');
    Route::get('/{translation}/modals', 'getModals');
    Route::get('/{translation}/modals/{modal}/tabs', 'getTabs');
    Route::get('/{translation}/modals/{modal}/tabs/{tab}/fields', 'getFields');
})->name('api.translations');

Route::get('/kyc-verify-text', function () {
    $verifyText = KycVerifyText::inRandomOrder()->first();

    return response()->json([
        'id' => $verifyText->id,
        'text' => $verifyText->text,
    ]);
});

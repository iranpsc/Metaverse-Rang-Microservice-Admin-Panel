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

Route::controller(TranslationController::class)->prefix('translations')->group(function () {
    Route::get('/', 'index');
    Route::get('/{translation}/modals', 'getModals');
    Route::get('/{translation}/modals/{modal}/tabs', 'getTabs');
    Route::get('/{translation}/modals/{modal}/tabs/{tab}/fields', 'getFields');
})->name('api.translations');

Route::get('/kyc-video-text', function () {
    $file = storage_path('app/public/kyc_video_text.json');

    // Check if the file exists
    if (file_exists($file)) {
        // Read the existing JSON data from the file
        $jsonData = file_get_contents($file);

        // Decode the JSON data into an associative array
        $existingData = json_decode($jsonData, true);

        if ($existingData === null) {
            // If the existing data is not valid JSON, initialize with an empty array
            $existingData = ['texts' => []];
        }
    } else {
        // If the file doesn't exist, create a new JSON file with the initial data
        $existingData = ['texts' => []];
    }

    return response()->json($existingData);
})->name('api.kyctext');

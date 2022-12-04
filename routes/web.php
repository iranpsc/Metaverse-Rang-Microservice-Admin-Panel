<?php

use App\Http\Controllers\Dynasty\DynastyMessageController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\DashboardController;
use App\Http\Livewire\Challenge\Questions;
use App\Http\Livewire\Challenge\QuestionsList;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DynastyController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TransactionsController;
use Illuminate\Support\Facades\DB;
use App\Http\Livewire\Citizens\Citizens;
use App\Http\Livewire\Employees\Employees;
use App\Http\Livewire\Lands\Lands;
use App\Http\Livewire\Level\Listing;
use App\Http\Livewire\Support\Support;
use App\Http\Livewire\Variables\Variables;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::redirect('/', '/dashboard');

Route::middleware('auth:admin')->group(function() {
    Route::controller(DashboardController::class)->group(function() {
        Route::get('/dashboard', 'index')->name('dashboard');
    });


    Route::get('/citizens', Citizens::class)->name('citizens');

    Route::get('/lands', Lands::class)->name('lands');
    Route::get('/variables', Variables::class)->name('variables');
    Route::get('/support', Support::class)->name('support');
    Route::get('/level', Listing::class)->name('level');
    Route::get('/maps', \App\Http\Livewire\Maps\Listing::class)->name('map-files');

    Route::get('/shop', [ShopController::class, 'index'])->name('shop');
    Route::get('/transactions', [TransactionsController::class, 'index'])->name('transactions');
    Route::get('/employees', Employees::class)->name('employees');
    Route::get('/import-maps', [MapController::class, 'readAndCreateFromJsFileUsingName'])
    ->name('import-maps');
    Route::get('/dynasty', [DynastyController::class, 'index'])->name('dynasty');
    Route::get('/challenge', QuestionsList::class)->name('challenge');
    Route::controller(DynastyMessageController::class)->prefix('/dynasty-messages')->group(function (){
        Route::get('/',App\Http\Livewire\Dynasty\DynastyMessages::class)->name('dynasty.messages');
    });

});


Route::controller(AuthController::class)->group(function() {
    Route::get('/login', 'showLoginForm')->name('showLoginForm');
    Route::post('/login', 'login')->name('login');
    Route::get('/logout', 'logout')->name('logout');
});

Route::get('truncate', function () {
    \App\Models\Coordinate::truncate();
    DB::table('coordinates')->delete();
    DB::table('geometries')->delete();
    DB::table('feature_properties')->delete();
    DB::table('features')->delete();
    DB::table('crs_properties')->delete();
    DB::table('crs')->delete();
    DB::table('maps')->delete();
    DB::table('polygons')->truncate();
    return redirect()->back()->with('success', 'دیتابیس با موفقیت ریست شد');
})->name('empty-and-reset-database');

<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Livewire\Citizens\Citizens;
use App\Http\Livewire\Dashboard\Dashboard;
use App\Http\Livewire\Employees\Employees;
use App\Http\Livewire\Lands\Lands;
use App\Http\Livewire\Level\Listing as LevelListing;
use App\Http\Livewire\Support\Support;
use App\Http\Livewire\Variables\Variables;
use App\Http\Livewire\Dynasty\Listing as DynastyListing;
use App\Http\Livewire\Maps\Listing as MapListing;

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

Route::middleware('auth:admin')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/citizens', Citizens::class)->name('citizens');
    Route::get('/lands', Lands::class)->name('lands');
    Route::get('/variables', Variables::class)->name('variables');
    Route::get('/support', Support::class)->name('support');
    Route::get('/level', LevelListing::class)->name('level');
    Route::get('/maps', MapListing::class)->name('map-management');
    Route::get('/employees', Employees::class)->name('employees');
    Route::get('/dynasty', DynastyListing::class)->name('dynasty');
});

require_once(__DIR__ . '/auth.php');

Route::get('truncate', function () {
    \App\Models\Coordinate::truncate();
    DB::table('coordinates')->delete();
    DB::table('geometries')->delete();
    DB::table('feature_properties')->delete();
    DB::table('features')->delete();
    DB::table('crs_properties')->delete();
    DB::table('crs')->delete();
    DB::table('maps')->delete();
    DB::table('maps')->truncate();
    return redirect()->back()->with('success', 'دیتابیس با موفقیت ریست شد');
})->name('empty-and-reset-database');

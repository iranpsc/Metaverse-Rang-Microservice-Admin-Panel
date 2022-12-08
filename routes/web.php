<?php

use App\Http\Controllers\IpManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Citizens\Citizens;
use App\Http\Livewire\Dashboard\Dashboard;
use App\Http\Livewire\Employees\Employees;
use App\Http\Livewire\Lands\Lands;
use App\Http\Livewire\Level\Listing as LevelListing;
use App\Http\Livewire\Support\Support;
use App\Http\Livewire\Variables\Variables;
use App\Http\Livewire\Dynasty\Listing as DynastyListing;
use App\Http\Livewire\IpManagement\IpManagement;
use App\Http\Livewire\Maps\Listing as MapListing;
use App\Http\Livewire\Calendar\Listing as CalendarListing;
use App\Http\Livewire\Reports\Listing as ReportsListing;
use App\Http\Livewire\SystemVariables\Listing as SystemVariablesListing;
use Illuminate\Http\Request;

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
    Route::get('/calendar', CalendarListing::class)->name('calendar');
    Route::get('/ip-management', IpManagement::class)->name('ip-management');
    Route::get('/reports', ReportsListing::class)->name('reports');
    Route::get('/system-variables', SystemVariablesListing::class)->name('system-variables');
});

require_once(__DIR__ . '/auth.php');




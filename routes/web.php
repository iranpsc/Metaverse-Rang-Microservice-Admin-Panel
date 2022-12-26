<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboard\Dashboard;
use App\Http\Livewire\Employees\Listing as EmployeesInfo;
use App\Http\Livewire\Employees\Bank as EmployeesBankInfo;
use App\Http\Livewire\Employees\Documents as EmployeesDocuments;
use App\Http\Livewire\Employees\Salary as EmployeesSalary;
use App\Http\Livewire\Employees\Time as EmployeesTimeCard;
use App\Http\Livewire\Employees\Tasks as EmployeesTasks;
use App\Http\Livewire\Lands\Listing as AllFeatures;
use App\Http\Livewire\Lands\Prices as FeaturesPrices;
use App\Http\Livewire\Lands\Sold as SoldFeatures;
use App\Http\Livewire\Lands\Pricing as PricedFeatures;
use App\Http\Livewire\Lands\Traded as TradedFeatures;
use App\Http\Livewire\Level\Listing as LevelListing;
use App\Http\Livewire\Support\CitizensSafety as SupportCitizensSafety;
use App\Http\Livewire\Support\TechnicalSupport as SupportTechnicalSupport;
use App\Http\Livewire\Support\Investment as SupportInvestment;
use App\Http\Livewire\Support\Inspection as SupportInspection;
use App\Http\Livewire\Support\Protection as SupportProtection;
use App\Http\Livewire\Support\ZTB as SupportZTBManagement;
use App\Http\Livewire\Variables\ColorOptions as StorePackages;
use App\Http\Livewire\Variables\ColorsPrice as StoreCurrencies;
use App\Http\Livewire\Dynasty\Prize as DynastyPrizes;
use App\Http\Livewire\Dynasty\DynastyMessages;
use App\Http\Livewire\Dynasty\Permissions as DynastyPermissions;
use App\Http\Livewire\IpManagement\IpManagement;
use App\Http\Livewire\Maps\Listing as MapListing;
use App\Http\Livewire\Calendar\Listing as CalendarListing;
use App\Http\Livewire\Reports\Listing as ReportsListing;
use App\Http\Livewire\SystemVariables\Listing as SystemVariablesListing;
use App\Http\Livewire\AccessManagement\EmployeeRolePermission as EmployeesAccessManagement;
use App\Http\Livewire\AccessManagement\Roles as EmployeesRoles;
use App\Http\Livewire\AccessManagement\Permissions as EmployeesPermissions;
use App\Http\Livewire\Citizens\Assets;
use App\Http\Livewire\Citizens\Bankaccounts;
use App\Http\Livewire\Citizens\Deposits;
use App\Http\Livewire\Citizens\Kyc;
use App\Http\Livewire\Citizens\Profiledetails;
use App\Http\Livewire\Citizens\RegistrationInfo;
use App\Http\Livewire\Citizens\Withdraws;
use App\Http\Livewire\Lands\FeaturePricingLimits;

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
    Route::prefix('citizens')->as('citizens.')->group(function() {
        Route::get('/registration-info', RegistrationInfo::class)->name('registration-info');
        Route::get('/kyc', Kyc::class)->name('kyc');
        Route::get('/bank-accounts', Bankaccounts::class)->name('bank-accounts');
        Route::get('/deposits', Deposits::class)->name('deposits');
        Route::get('/withdraws', Withdraws::class)->name('withdraws');
        Route::get('/profile-details', Profiledetails::class)->name('profile-details');
        Route::get('/assets', Assets::class)->name('assets');
    });
    Route::prefix('features')->as('features.')->group(function() {
        Route::get('/', AllFeatures::class)->name('all');
        Route::get('/prices', FeaturesPrices::class)->name('prices');
        Route::get('/sold', SoldFeatures::class)->name('sold');
        Route::get('/trades', TradedFeatures::class)->name('trades');
        Route::get('/priced', PricedFeatures::class)->name('priced');
        Route::get('/pricing-limits', FeaturePricingLimits::class)->name('pricing-limits');
    });
    Route::prefix('access-management')->as('access-management.')->group(function() {
        Route::get('/', EmployeesAccessManagement::class)->name('employees');
        Route::get('/roles', EmployeesRoles::class)->name('roles');
        Route::get('/permissions', EmployeesPermissions::class)->name('permissions');
    });
    Route::prefix('employees')->as('employees.')->group(function() {
        Route::get('/info', EmployeesInfo::class)->name('info');
        Route::get('/bank-info', EmployeesBankInfo::class)->name('bank-info');
        Route::get('/documents', EmployeesDocuments::class)->name('documents');
        Route::get('/salary', EmployeesSalary::class)->name('salary');
        Route::get('/time-card', EmployeesTimeCard::class)->name('time-card');
        Route::get('/tasks', EmployeesTasks::class)->name('tasks');
    });
    Route::prefix('support')->as('support.')->group(function() {
        Route::get('/citizens-safety', SupportCitizensSafety::class)->name('citizens-safety');
        Route::get('/technical-support', SupportTechnicalSupport::class)->name('technical-support');
        Route::get('/investment', SupportInvestment::class)->name('investment');
        Route::get('/inspection', SupportInspection::class)->name('inspection');
        Route::get('/protection', SupportProtection::class)->name('protection');
        Route::get('/ztb-management', SupportZTBManagement::class)->name('ztb-management');
    });
    Route::prefix('store')->as('store.')->group(function() {
        Route::get('/packages', StorePackages::class)->name('packages');
        Route::get('/currencies', StoreCurrencies::class)->name('currencies');
    });
    Route::prefix('dynasty')->as('dynasty.')->group(function() {
        Route::get('/prizes', DynastyPrizes::class)->name('prizes');
        Route::get('/messages', DynastyMessages::class)->name('messages');
        Route::get('/permissions', DynastyPermissions::class)->name('permissions');
    });
    Route::get('/level', LevelListing::class)->name('level');
    Route::get('/maps', MapListing::class)->name('map-management');
    Route::get('/calendar', CalendarListing::class)->name('calendar');
    Route::get('/ip-management', IpManagement::class)->name('ip-management');
    Route::get('/reports', ReportsListing::class)->name('reports');
    Route::get('/system-variables', SystemVariablesListing::class)->name('system-variables');
});

require_once(__DIR__ . '/auth.php');

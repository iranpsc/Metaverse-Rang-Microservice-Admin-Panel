<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
use App\Http\Livewire\Maps\Listing as MapListing;
use App\Http\Livewire\Calendar\Listing as CalendarListing;
use App\Http\Livewire\Reports\Listing as ReportsListing;
use App\Http\Livewire\SystemVariables\Listing as SystemVariablesListing;
use App\Http\Livewire\AccessManagement\EmployeeRolePermission as EmployeesAccessManagement;
use App\Http\Livewire\AccessManagement\Roles as EmployeesRoles;
use App\Http\Livewire\AccessManagement\Permissions as EmployeesPermissions;
use App\Http\Livewire\Challenge\QuestionsList;
use App\Http\Livewire\Citizens\Assets;
use App\Http\Livewire\Citizens\Bankaccounts;
use App\Http\Livewire\Citizens\Deposits;
use App\Http\Livewire\Citizens\Kyc;
use App\Http\Livewire\Citizens\Profiledetails;
use App\Http\Livewire\Citizens\RegistrationInfo;
use App\Http\Livewire\Citizens\Withdraws;
use App\Http\Livewire\Lands\FeaturePricingLimits;
use App\Http\Livewire\IpManagement\ApiAllowedIps;
use App\Http\Livewire\IpManagement\AdminAllowedIps;
use App\Http\Livewire\IpManagement\ApiIpRanges;
use App\Http\Livewire\Lands\Limits;
use App\Http\Livewire\Music\Listing as MusicListing;
use App\Http\Livewire\Music\Categories as MusicCategories;
use App\Http\Livewire\Panel\Profile;
use App\Http\Livewire\Videos\Listing as VideoListing;
use App\Http\Livewire\Videos\Categories as VideoCategories;
use App\Notifications\SendVerificationCode;
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

    Route::prefix('citizens')->as('citizens.')->group(function () {
        Route::get('/registration-info', RegistrationInfo::class)
            ->middleware('can:view-registration-info')
            ->name('registration-info');
        Route::get('/kyc', Kyc::class)
            ->middleware('can:verify-kyc')
            ->name('kyc');
        Route::get('/bank-accounts', Bankaccounts::class)
            ->middleware('can:verify-bank-accounts')
            ->name('bank-accounts');
        Route::get('/deposits', Deposits::class)
            ->middleware('can:view-deposits')
            ->name('deposits');
        Route::get('/withdraws', Withdraws::class)
            ->middleware('can:view-withdraws')
            ->name('withdraws');
        Route::get('/profile-details', Profiledetails::class)
            ->middleware('can:view-profile-details')
            ->name('profile-details');
        Route::get('/assets', Assets::class)
            ->middleware('can:view-assets')
            ->name('assets');
    });
    Route::prefix('features')->as('features.')->group(function () {
        Route::get('/', AllFeatures::class)
            ->middleware('can:edit-features-info')
            ->name('all');
        Route::get('/prices', FeaturesPrices::class)
            ->middleware('can:view-features-prices')
            ->name('prices');
        Route::get('/sold', SoldFeatures::class)
            ->middleware('can:view-sold-features')
            ->name('sold');
        Route::get('/trades', TradedFeatures::class)
            ->middleware('can:view-features-trades')
            ->name('trades');
        Route::get('/priced', PricedFeatures::class)
            ->middleware('can:view-priced-features')
            ->name('priced');
        Route::get('/pricing-limits', FeaturePricingLimits::class)
            ->middleware('can:edit-pricing-limits')
            ->name('pricing-limits');
        Route::get('/limits', Limits::class)
            ->middleware('can:manage-feature-limits')
            ->name('limits');
    });
    Route::prefix('access-management')->middleware('can:manage-access')
        ->as('access-management.')->group(function () {
            Route::get('/', EmployeesAccessManagement::class)->name('employees');
            Route::get('/roles', EmployeesRoles::class)->name('roles');
            Route::get('/permissions', EmployeesPermissions::class)->name('permissions');
        });
    Route::prefix('employees')->as('employees.')->group(function () {
        Route::get('/info', EmployeesInfo::class)
            ->middleware('can:manage-employee-info')
            ->name('info');
        Route::get('/bank-info', EmployeesBankInfo::class)
            ->middleware('can:manage-employee-bank-accounts')
            ->name('bank-info');
        Route::get('/documents', EmployeesDocuments::class)
            ->middleware('can:manage-employee-documents')
            ->name('documents');
        Route::get('/salary', EmployeesSalary::class)
            ->middleware('can:manage-employee-salary')
            ->name('salary');
        Route::get('/time-card', EmployeesTimeCard::class)
            ->middleware('can:manage-employee-time-card')
            ->name('time-card');
        Route::get('/tasks', EmployeesTasks::class)
            ->middleware('can:manage-employee-tasks')
            ->name('tasks');
    });
    Route::prefix('support')->as('support.')->group(function () {
        Route::get('/citizens-safety', SupportCitizensSafety::class)
            ->middleware('can:respond-to-citziens-safety-tickets')
            ->name('citizens-safety');
        Route::get('/technical-support', SupportTechnicalSupport::class)
            ->middleware('can:respond-to-technical-support-tickets')
            ->name('technical-support');
        Route::get('/investment', SupportInvestment::class)
            ->middleware('can:respond-to-investment-tickets')
            ->name('investment');
        Route::get('/inspection', SupportInspection::class)
            ->middleware('can:respond-to-inspection-tickets')
            ->name('inspection');
        Route::get('/protection', SupportProtection::class)
            ->middleware('can:respond-to-protection-tickets')
            ->name('protection');
        Route::get('/ztb-management', SupportZTBManagement::class)
            ->middleware('can:respond-to-ztb-management-tickets')
            ->name('ztb-management');
    });
    Route::prefix('store')->as('store.')->group(function () {
        Route::get('/packages', StorePackages::class)
            ->middleware('can:manage-packages')
            ->name('packages');
        Route::get('/currencies', StoreCurrencies::class)
            ->middleware('can:manage-currencies')
            ->name('currencies');
    });
    Route::prefix('dynasty')->as('dynasty.')->group(function () {
        Route::get('/prizes', DynastyPrizes::class)
            ->middleware('can:manage-dynasty-prizes')
            ->name('prizes');
        Route::get('/messages', DynastyMessages::class)
            ->middleware('can:manage-dynasty-messages')
            ->name('messages');
        Route::get('/permissions', DynastyPermissions::class)
            ->middleware('can:manage-dynasty-permissions')
            ->name('permissions');
    });

    Route::prefix('ip')->as('ip.')->group(function () {
        Route::get('/ranges', ApiIpRanges::class)->middleware('can:manage-ip-ranges')->name('ranges');
        Route::get('/api', ApiAllowedIps::class)->middleware('can:manage-api-allowed-ips')->name('api');
        Route::get('/admin', AdminAllowedIps::class)->middleware('can:manage-admin-allowed-ips')->name('admin');
    });

    Route::get('/levels', LevelListing::class)->middleware('can:manage-level')->name('level');
    Route::get('/maps', MapListing::class)->middleware('can:manage-maps')->name('map-management');
    Route::get('/calendar', CalendarListing::class)->middleware('can:manage-calendar')->name('calendar');
    Route::get('/reports', ReportsListing::class)->middleware('can:manage-repots')->name('reports');
    Route::get('/system-variables', SystemVariablesListing::class)->middleware('can:manage-system-variables')
        ->name('system-variables');

    Route::prefix('music')->middleware('can:manage-musics')->group(function () {
        Route::get('/', MusicListing::class)->name('music');
        Route::get('/categories', MusicCategories::class)->name('music.categories');
    });

    Route::get('/videos', VideoListing::class)->middleware('can:manage-tutorials')->name('videos');
    Route::get('/video-categories', VideoCategories::class)->middleware('can:manage-tutorials')->name('video.categories');

    Route::get('challenge', QuestionsList::class)->middleware('can:manage-challenge')->name('challenge');

    Route::get('profile', Profile::class)->name('profile');
});

Auth::routes([
    'register' => false,
]);

Route::middleware('auth:admin')->group(function () {
    Route::get('/code/send', function (Request $request) {
        $request->user()->notify(new SendVerificationCode);
        return response()->json();
    });

    Route::post('/code/verify', function (Request $request) {
        $request->validate([
            'phone_verification' => 'required|integer|digits:6|is_valid_verify_code',
            'access_password' => 'required|is_valid_access_password'
        ]);
        return response()->json();
    });
});

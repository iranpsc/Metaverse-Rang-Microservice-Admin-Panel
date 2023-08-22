<?php

use App\Http\Controllers\SendVerificationCodeController;
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
use App\Http\Livewire\IpManagement\ApiAllowedIps;
use App\Http\Livewire\IpManagement\AdminAllowedIps;
use App\Http\Livewire\IpManagement\ApiIpRanges;
use App\Http\Livewire\IpManagement\WhiteListRequests;
use App\Http\Livewire\Panel\Profile;
use App\Http\Livewire\Translations\Field;
use App\Http\Livewire\Videos\Listing as VideoListing;
use App\Http\Livewire\Videos\Categories as VideoCategories;
use App\Http\Livewire\Videos\EditVideo;
use App\Http\Livewire\Translations\Listing as TranslationsListing;
use App\Http\Livewire\Translations\Modal;
use App\Http\Livewire\Translations\Tab;
use App\Http\Livewire\Versions;
use App\Http\Livewire\Calendar;

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

Auth::routes(['register' => false]);

Route::middleware('auth:admin')->group(function () {

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::prefix('citizens')->as('citizens.')->group(function () {
        Route::get('registration-info', RegistrationInfo::class)->can('view-registration-info')->name('registration-info');
        Route::get('kyc', Kyc::class)->can('verify-kyc')->name('kyc');
        Route::get('bank-accounts', Bankaccounts::class)->can('verify-bank-accounts')->name('bank-accounts');
        Route::get('deposits', Deposits::class)->can('view-deposits')->name('deposits');
        Route::get('withdraws', Withdraws::class)->can('view-withdraws')->name('withdraws');
        Route::get('profile-details', Profiledetails::class)->can('view-profile-details')->name('profile-details');
        Route::get('assets', Assets::class)->can('view-assets')->name('assets');
    });

    Route::prefix('features')->as('features.')->group(function () {
        Route::get('/', AllFeatures::class)->can('edit-features-info')->name('all');
        Route::get('/prices', FeaturesPrices::class)->can('view-features-prices')->name('prices');
        Route::get('/sold', SoldFeatures::class)->can('view-sold-features')->name('sold');
        Route::get('/trades', TradedFeatures::class)->can('view-features-trades')->name('trades');
        Route::get('/priced', PricedFeatures::class)->can('view-priced-features')->name('priced');
    });

    Route::prefix('access-management')->middleware('can:manage-access')->as('access-management.')->group(function () {
        Route::get('/', EmployeesAccessManagement::class)->name('employees');
        Route::get('/roles', EmployeesRoles::class)->name('roles');
        Route::get('/permissions', EmployeesPermissions::class)->name('permissions');
    });

    Route::prefix('employees')->as('employees.')->group(function () {
        Route::get('/info', EmployeesInfo::class)->can('manage-employee-info')->name('info');
        Route::get('/bank-info', EmployeesBankInfo::class)->can('manage-employee-bank-accounts')->name('bank-info');
        Route::get('/documents', EmployeesDocuments::class)->can('manage-employee-documents')->name('documents');
        Route::get('/salary', EmployeesSalary::class)->can('manage-employee-salary')->name('salary');
        Route::get('/time-card', EmployeesTimeCard::class)->can('manage-employee-time-card')->name('time-card');
        Route::get('/tasks', EmployeesTasks::class)->can('manage-employee-tasks')->name('tasks');
    });

    Route::prefix('support')->as('support.')->group(function () {
        Route::get('/citizens-safety', SupportCitizensSafety::class)->can('respond-to-citziens-safety-tickets')->name('citizens-safety');
        Route::get('/technical-support', SupportTechnicalSupport::class)->can('respond-to-technical-support-tickets')->name('technical-support');
        Route::get('/investment', SupportInvestment::class)->can('respond-to-investment-tickets')->name('investment');
        Route::get('/inspection', SupportInspection::class)->can('respond-to-inspection-tickets')->name('inspection');
        Route::get('/protection', SupportProtection::class)->can('respond-to-protection-tickets')->name('protection');
        Route::get('/ztb-management', SupportZTBManagement::class)->can('respond-to-ztb-management-tickets')->name('ztb-management');
    });

    Route::prefix('store')->as('store.')->group(function () {
        Route::get('/packages', StorePackages::class)->can('manage-packages')->name('packages');
        Route::get('/currencies', StoreCurrencies::class)->can('manage-currencies')->name('currencies');
    });

    Route::prefix('dynasty')->as('dynasty.')->group(function () {
        Route::get('/prizes', DynastyPrizes::class)->can('manage-dynasty-prizes')->name('prizes');
        Route::get('/messages', DynastyMessages::class)->can('manage-dynasty-messages')->name('messages');
        Route::get('/permissions', DynastyPermissions::class)->can('manage-dynasty-permissions')->name('permissions');
    });

    Route::prefix('ip')->as('ip.')->group(function () {
        Route::get('ranges', ApiIpRanges::class)->can('manage-ip-ranges')->name('ranges');
        Route::get('api', ApiAllowedIps::class)->can('manage-api-allowed-ips')->name('api');
        Route::get('admin', AdminAllowedIps::class)->can('manage-admin-allowed-ips')->name('admin');
        Route::get('white-listing', WhiteListRequests::class)->can('manage-white-list-requests')->name('white-listing');
    });

    Route::get('levels', LevelListing::class)->can('manage-level')->name('level');
    Route::get('maps', MapListing::class)->can('manage-maps')->name('map-management');
    Route::get('calendar', Calendar::class)->can('manage-calendar')->name('calendar');
    Route::get('versions', Versions::class)->can('manage-versions')->name('versions');
    Route::get('reports', ReportsListing::class)->can('manage-reports')->name('reports');
    Route::get('system-variables', SystemVariablesListing::class)->can('manage-system-variables')->name('system-variables');

    Route::get('videos', VideoListing::class)->can('manage-tutorials')->name('videos');
    Route::post('videos', [VideoListing::class, 'upload'])->can('manage-tutorials')->name('videos.upload');
    Route::post('videos/edit', [EditVideo::class, 'upload'])->can('manage-tutorials')->name('videos.edit.upload');

    Route::get('video-categories', VideoCategories::class)->can('manage-tutorials')->name('video.categories');

    Route::get('challenge', QuestionsList::class)->can('manage-challenge')->name('challenge');

    Route::get('profile', Profile::class)->name('profile');

    Route::controller(SendVerificationCodeController::class)->prefix('code')->group(function () {
        Route::get('/send', [SendVerificationCodeController::class, 'send']);
        Route::post('/verify', [SendVerificationCodeController::class, 'verify']);
    });

    Route::prefix('translations')->group(function () {
        Route::get('/', TranslationsListing::class)->can('manage-translations')->name('translations');
        Route::get('/{translation}/modals', Modal::class)->can('manage-translations')->name('modals');
        Route::get('/{translation}/modals/{modal}/tabs', Tab::class)->can('manage-translations')->name('tabs');
        Route::get('/{translation}/modals/{modal}/tabs/{tab}/fields', Field::class)->can('manage-translations')->name('fields');
    });
});

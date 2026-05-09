<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Api\AdminsController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\ChallengeQuestionsController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\DynastyMessagesController;
use App\Http\Controllers\Api\DynastyPermissionsController;
use App\Http\Controllers\Api\DynastyPrizesController;
use App\Http\Controllers\Api\FeatureLimitsController;
use App\Http\Controllers\Api\FeaturePricingLimitsController;
use App\Http\Controllers\Api\IsicCodeController;
use App\Http\Controllers\Api\KycController;
use App\Http\Controllers\Api\KycVideoTextController;
use App\Http\Controllers\Api\LandsController;
use App\Http\Controllers\Api\LevelPrizeController;
use App\Http\Controllers\Api\LevelGiftController;
use App\Http\Controllers\Api\LevelLicenseController;
use App\Http\Controllers\Api\LevelGeneralInfoController;
use App\Http\Controllers\Api\LevelGemController;
use App\Http\Controllers\Api\LevelsController;
use App\Http\Controllers\Api\MapsController;
use App\Http\Controllers\Api\OptionsController;
use App\Http\Controllers\Api\PermissionsController;
use App\Http\Controllers\Api\PricesController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProfileDetailsController;
use App\Http\Controllers\Api\RegistrationInfoController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\SoldController;
use App\Http\Controllers\Api\TicketsController;
use App\Http\Controllers\Api\TradedController;
use App\Http\Controllers\Api\VariablesController;
use App\Http\Controllers\Api\VideoCategoriesController;
use App\Http\Controllers\Api\VideoSubCategoriesController;
use App\Http\Controllers\Api\VideoUploadController;
use App\Http\Controllers\Api\VideosController;
use App\Http\Controllers\Api\SystemVariablesController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WithdrawController;
use App\Http\Controllers\Api\VersionController;
use App\Http\Controllers\Api\V1\Translations\FieldController as TranslationFieldController;
use App\Http\Controllers\Api\V1\Translations\ModalController as TranslationModalController;
use App\Http\Controllers\Api\V1\Translations\TabController as TranslationTabController;
use App\Http\Controllers\Api\V1\Translations\TranslationController;
use App\Http\Middleware\EnsureAdminSanctumAuth;
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

Route::get('/kyc-verify-text', function () {
    $verifyText = KycVerifyText::inRandomOrder()->first();

    return response()->json([
        'id' => $verifyText->id,
        'text' => $verifyText->text,
    ]);
});

// Authentication routes (guest only)
Route::middleware(['guest'])->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('/password/reset', [ResetPasswordController::class, 'reset']);
});

// Protected auth routes
// Use sanctum for token-based authentication with admin guard support
Route::middleware(['auth:sanctum', EnsureAdminSanctumAuth::class])->group(function () {
    Route::get('/me', [LoginController::class, 'me']);
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/registration-info', [RegistrationInfoController::class, 'index']);
    Route::get('/reports', [ReportController::class, 'index']);

    // Challenge routes
    Route::get('/challenge/questions', [ChallengeQuestionsController::class, 'index']);
    Route::post('/challenge/questions/import', [ChallengeQuestionsController::class, 'import']);
    Route::delete('/challenge/questions/{question}', [ChallengeQuestionsController::class, 'destroy']);

    // Calendar routes
    Route::get('/calendars', [CalendarController::class, 'index']);
    Route::post('/calendars', [CalendarController::class, 'store']);
    Route::put('/calendars/{calendar}', [CalendarController::class, 'update']);
    Route::delete('/calendars/{calendar}', [CalendarController::class, 'destroy']);

    // Versions routes
    Route::get('/versions', [VersionController::class, 'index']);
    Route::post('/versions', [VersionController::class, 'store']);
    Route::delete('/versions/{version}', [VersionController::class, 'destroy']);

    // KYC routes
    Route::get('/kycs', [KycController::class, 'index']);
    Route::get('/kycs/{id}', [KycController::class, 'show']);
    Route::put('/kycs/{id}', [KycController::class, 'update']);

    // Bank Account routes
    Route::get('/bank-accounts', [BankAccountController::class, 'index']);
    Route::get('/bank-accounts/{id}', [BankAccountController::class, 'show']);
    Route::put('/bank-accounts/{id}', [BankAccountController::class, 'update']);

    // KYC Video Text routes
    Route::get('/kyc-video-texts', [KycVideoTextController::class, 'index']);
    Route::post('/kyc-video-texts', [KycVideoTextController::class, 'store']);
    Route::put('/kyc-video-texts/{id}', [KycVideoTextController::class, 'update']);
    Route::delete('/kyc-video-texts/{id}', [KycVideoTextController::class, 'destroy']);

    // Verification routes
    Route::post('/send-verification-sms', [VerificationController::class, 'sendSMS']);

    // Wallets routes
    Route::get('/assets', [WalletController::class, 'index']);

    // Deposits routes
    Route::get('/deposits', [DepositController::class, 'index']);
    Route::get('/deposits/export', [DepositController::class, 'export']);

    // Profile Details routes
    Route::get('/profile-details', [ProfileDetailsController::class, 'index']);

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile/info', [ProfileController::class, 'updateInfo']);
    Route::post('/profile/password/request', [ProfileController::class, 'requestPasswordChange']);
    Route::post('/profile/password/verify', [ProfileController::class, 'verifyPasswordChange']);

    // Withdraws routes
    Route::get('/withdraws', [WithdrawController::class, 'index']);

    // Lands routes
    Route::get('/lands', [LandsController::class, 'index']);
    Route::put('/lands/features/{id}/properties', [LandsController::class, 'updateProperties']);
    Route::put('/lands/features/{id}/coordinates', [LandsController::class, 'updateCoordinates']);

    // Levels routes
    Route::apiResource('levels', LevelsController::class)->except(['show']);
    Route::get('/levels/{level}/prize', [LevelPrizeController::class, 'show']);
    Route::post('/levels/{level}/prize', [LevelPrizeController::class, 'store']);
    Route::put('/levels/{level}/prize', [LevelPrizeController::class, 'update']);
    Route::get('/levels/{level}/gift', [LevelGiftController::class, 'show']);
    Route::post('/levels/{level}/gift', [LevelGiftController::class, 'store']);
    Route::put('/levels/{level}/gift', [LevelGiftController::class, 'update']);
    Route::get('/levels/{level}/licenses', [LevelLicenseController::class, 'show']);
    Route::post('/levels/{level}/licenses', [LevelLicenseController::class, 'store']);
    Route::put('/levels/{level}/licenses', [LevelLicenseController::class, 'update']);
    Route::get('/levels/{level}/gem', [LevelGemController::class, 'show']);
    Route::post('/levels/{level}/gem', [LevelGemController::class, 'store']);
    Route::put('/levels/{level}/gem', [LevelGemController::class, 'update']);
    Route::get('/levels/{level}/general-info', [LevelGeneralInfoController::class, 'show']);
    Route::post('/levels/{level}/general-info', [LevelGeneralInfoController::class, 'store']);
    Route::put('/levels/{level}/general-info', [LevelGeneralInfoController::class, 'update']);

    // Feature Limits routes
    Route::get('/lands/feature-limits', [FeatureLimitsController::class, 'index']);
    Route::post('/lands/feature-limits', [FeatureLimitsController::class, 'store']);
    Route::delete('/lands/feature-limits/{id}', [FeatureLimitsController::class, 'destroy']);

    // Feature Pricing Limits routes
    Route::get('/lands/feature-pricing-limits', [FeaturePricingLimitsController::class, 'index']);
    Route::post('/lands/feature-pricing-limits', [FeaturePricingLimitsController::class, 'update']);

    // Prices routes
    Route::get('/lands/prices', [PricesController::class, 'index']);

    // Pricing routes
    Route::get('/lands/pricing', [PricingController::class, 'index']);

    // Sold routes
    Route::get('/lands/sold', [SoldController::class, 'index']);

    // Traded routes
    Route::get('/lands/traded', [TradedController::class, 'index']);

    // Access Management - Roles routes
    Route::get('/roles', [RolesController::class, 'index']);
    Route::get('/roles/permissions', [RolesController::class, 'getPermissions']);
    Route::get('/roles/{id}', [RolesController::class, 'show']);
    Route::post('/roles', [RolesController::class, 'store']);
    Route::put('/roles/{id}', [RolesController::class, 'update']);
    Route::delete('/roles/{id}', [RolesController::class, 'destroy']);
    Route::delete('/roles/{roleId}/permissions/{permissionId}', [RolesController::class, 'removePermission']);

    // Access Management - Permissions routes
    Route::get('/permissions', [PermissionsController::class, 'index']);
    Route::get('/permissions/roles', [PermissionsController::class, 'getRoles']);
    Route::get('/permissions/{id}', [PermissionsController::class, 'show']);
    Route::post('/permissions', [PermissionsController::class, 'store']);
    Route::put('/permissions/{id}', [PermissionsController::class, 'update']);
    Route::delete('/permissions/{id}', [PermissionsController::class, 'destroy']);
    Route::delete('/permissions/{permissionId}/roles/{roleId}', [PermissionsController::class, 'removeRole']);

    // Access Management - Admins routes
    Route::get('/admins', [AdminsController::class, 'index']);
    Route::get('/admins/employees', [AdminsController::class, 'getEmployees']);
    Route::get('/admins/roles', [AdminsController::class, 'getRoles']);
    Route::get('/admins/{id}', [AdminsController::class, 'show']);
    Route::post('/admins', [AdminsController::class, 'store']);
    Route::put('/admins/{id}', [AdminsController::class, 'update']);
    Route::delete('/admins/{id}', [AdminsController::class, 'destroy']);
    Route::delete('/admins/{adminId}/roles/{roleId}', [AdminsController::class, 'removeRole']);
    Route::delete('/admins/{adminId}/permissions/{permissionId}', [AdminsController::class, 'removePermission']);

    // Support - Tickets routes
    Route::get('/tickets', [TicketsController::class, 'index']);
    Route::get('/tickets/departments', [TicketsController::class, 'getDepartments']);
    Route::post('/tickets/{id}/response', [TicketsController::class, 'sendResponse']);
    Route::post('/tickets/{id}/transfer', [TicketsController::class, 'transfer']);

    // Variables routes
    Route::get('/variables', [VariablesController::class, 'index']);
    Route::post('/variables', [VariablesController::class, 'store']);
    Route::put('/variables/{id}', [VariablesController::class, 'update']);
    Route::delete('/variables/{id}', [VariablesController::class, 'destroy']);

    // System variables routes
    Route::get('/system-variables', [SystemVariablesController::class, 'index']);
    Route::post('/system-variables', [SystemVariablesController::class, 'store']);
    Route::put('/system-variables/{system_variable}', [SystemVariablesController::class, 'update']);
    Route::delete('/system-variables/{system_variable}', [SystemVariablesController::class, 'destroy']);

    // Options routes
    Route::get('/options', [OptionsController::class, 'index']);
    Route::get('/options/variables', [OptionsController::class, 'getVariables']);
    Route::post('/options', [OptionsController::class, 'store']);
    Route::put('/options/{id}', [OptionsController::class, 'update']);
    Route::delete('/options/{id}', [OptionsController::class, 'destroy']);

    // Video categories routes
    Route::apiResource('video-categories', VideoCategoriesController::class)->except(['show']);

    // Video sub categories routes
    Route::apiResource('video-sub-categories', VideoSubCategoriesController::class)->except(['show']);

    // Videos routes
    Route::get('/videos/meta', [VideosController::class, 'meta']);
    Route::post('/videos/chunk', VideoUploadController::class);
    Route::apiResource('videos', VideosController::class)->except(['show']);

    // Dynasty - Messages routes
    Route::get('/dynasty/messages', [DynastyMessagesController::class, 'index']);
    Route::post('/dynasty/messages', [DynastyMessagesController::class, 'store']);
    Route::put('/dynasty/messages/{id}', [DynastyMessagesController::class, 'update']);
    Route::delete('/dynasty/messages/{id}', [DynastyMessagesController::class, 'destroy']);

    // Dynasty - Prizes routes
    Route::get('/dynasty/prizes', [DynastyPrizesController::class, 'index']);
    Route::post('/dynasty/prizes', [DynastyPrizesController::class, 'store']);
    Route::put('/dynasty/prizes/{id}', [DynastyPrizesController::class, 'update']);
    Route::delete('/dynasty/prizes/{id}', [DynastyPrizesController::class, 'destroy']);

    // Dynasty - Permissions routes
    Route::get('/dynasty/permissions', [DynastyPermissionsController::class, 'show']);
    Route::put('/dynasty/permissions', [DynastyPermissionsController::class, 'update']);

    // Maps routes
    Route::get('/maps', [MapsController::class, 'index']);
    Route::post('/maps', [MapsController::class, 'store']);
    Route::put('/maps/{id}', [MapsController::class, 'update']);
    Route::delete('/maps/{id}', [MapsController::class, 'destroy']);
    Route::post('/maps/{id}/insert-into-database', [MapsController::class, 'insertIntoDatabase']);

    // ISIC Codes routes
    Route::get('/isic-codes', [IsicCodeController::class, 'index']);
    Route::post('/isic-codes', [IsicCodeController::class, 'store']);
    Route::post('/isic-codes/import', [IsicCodeController::class, 'import']);
    Route::post('/isic-codes/{isicCode}/approve', [IsicCodeController::class, 'approve']);
    Route::post('/isic-codes/{isicCode}/deny', [IsicCodeController::class, 'deny']);
    Route::delete('/isic-codes/{isicCode}', [IsicCodeController::class, 'destroy']);

    Route::prefix('v1')->group(function () {
        Route::get('/translations/languages', [TranslationController::class, 'languages']);
        Route::get('/translations', [TranslationController::class, 'index']);
        Route::get('/translations/{translation}', [TranslationController::class, 'show']);
        Route::post('/translations', [TranslationController::class, 'store']);
        Route::delete('/translations/{translation}', [TranslationController::class, 'destroy']);
        Route::patch('/translations/{translation}/status', [TranslationController::class, 'toggleStatus']);
        Route::post('/translations/{translation}/export', [TranslationController::class, 'export']);

        Route::get('/translations/{translation}/modals', [TranslationModalController::class, 'index']);
        Route::get('/translations/{translation}/modals/{modal}', [TranslationModalController::class, 'show']);
        Route::post('/translations/{translation}/modals', [TranslationModalController::class, 'store']);
        Route::patch('/translations/{translation}/modals/{modal}', [TranslationModalController::class, 'update']);
        Route::delete('/translations/{translation}/modals/{modal}', [TranslationModalController::class, 'destroy']);

        Route::get('/translations/{translation}/modals/{modal}/tabs', [TranslationTabController::class, 'index']);
        Route::get('/translations/{translation}/modals/{modal}/tabs/{tab}', [TranslationTabController::class, 'show']);
        Route::post('/translations/{translation}/modals/{modal}/tabs', [TranslationTabController::class, 'store']);
        Route::patch('/translations/{translation}/modals/{modal}/tabs/{tab}', [TranslationTabController::class, 'update']);
        Route::delete('/translations/{translation}/modals/{modal}/tabs/{tab}', [TranslationTabController::class, 'destroy']);

        Route::get('/translations/{translation}/modals/{modal}/tabs/{tab}/fields', [TranslationFieldController::class, 'index']);
        Route::post('/translations/{translation}/modals/{modal}/tabs/{tab}/fields', [TranslationFieldController::class, 'store']);
        Route::patch('/translations/{translation}/modals/{modal}/tabs/{tab}/fields/{field}', [TranslationFieldController::class, 'update']);
        Route::delete('/translations/{translation}/modals/{modal}/tabs/{tab}/fields/{field}', [TranslationFieldController::class, 'destroy']);
    });
});


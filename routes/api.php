<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AdminsController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\BulkMessageController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\ChallengeQuestionsController;
use App\Http\Controllers\Api\ConnectedWalletController;
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
use App\Http\Controllers\Api\LevelGemController;
use App\Http\Controllers\Api\LevelGeneralInfoController;
use App\Http\Controllers\Api\LevelGiftController;
use App\Http\Controllers\Api\LevelLicenseController;
use App\Http\Controllers\Api\LevelPrizeController;
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
use App\Http\Controllers\Api\SystemVariablesController;
use App\Http\Controllers\Api\TicketsController;
use App\Http\Controllers\Api\TradedController;
use App\Http\Controllers\Api\UserLevelsController;
use App\Http\Controllers\Api\V1\Translations\FieldController as TranslationFieldController;
use App\Http\Controllers\Api\V1\Translations\ModalController as TranslationModalController;
use App\Http\Controllers\Api\V1\Translations\TabController as TranslationTabController;
use App\Http\Controllers\Api\V1\Translations\TranslationController;
use App\Http\Controllers\Api\VariablesController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\VersionController;
use App\Http\Controllers\Api\VideoCategoriesController;
use App\Http\Controllers\Api\VideosController;
use App\Http\Controllers\Api\VideoSubCategoriesController;
use App\Http\Controllers\Api\VideoUploadController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WithdrawController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\FileUploadController;
use App\Http\Middleware\EnsureAdminSanctumAuth;
use App\Http\Middleware\RequirePhoneVerification;
use App\Models\KycVerifyText;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
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

    Route::prefix('password')->group(function () {
        Route::post('email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
        Route::post('reset', [ResetPasswordController::class, 'reset']);
    });
});

Route::middleware(['auth:sanctum', EnsureAdminSanctumAuth::class])->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('/me', 'me');
        Route::post('/logout', 'logout');
    });

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/registration-info', [RegistrationInfoController::class, 'index']);
    Route::get('/connected-wallets', [ConnectedWalletController::class, 'index']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/assets', [WalletController::class, 'index']);
    Route::get('/profile-details', [ProfileDetailsController::class, 'index']);
    Route::get('/withdraws', [WithdrawController::class, 'index']);

    // Activity logs
    Route::prefix('activity-logs')->controller(ActivityLogController::class)->group(function () {
        Route::get('categories', 'categories');
        Route::get('/', 'index');
        Route::get('{id}', 'show');
    });

    // Challenge routes
    Route::prefix('challenge/questions')->controller(ChallengeQuestionsController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('import', 'import');
        Route::delete('{question}', 'destroy');
    });

    // Calendar routes
    Route::prefix('calendars')->controller(CalendarController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('{calendar}', 'update');
        Route::delete('{calendar}', 'destroy');
    });

    // Versions routes
    Route::prefix('versions')->controller(VersionController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::delete('{version}', 'destroy');
    });

    // KYC routes
    Route::prefix('kycs')->controller(KycController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
        Route::put('{id}', 'update');
    });

    // Bank Account routes
    Route::prefix('bank-accounts')->controller(BankAccountController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
        Route::put('{id}', 'update');
    });

    // KYC Video Text routes
    Route::prefix('kyc-video-texts')->controller(KycVideoTextController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
    });

    // Phone verification session routes (excluded from RequirePhoneVerification middleware)
    Route::withoutMiddleware([RequirePhoneVerification::class])
        ->controller(VerificationController::class)
        ->group(function () {
            Route::post('/send-verification-sms', 'sendSMS');
            Route::post('/verify-verification-sms', 'verify');

            Route::prefix('phone-verification')->group(function () {
                Route::get('status', 'status');
                Route::post('confirm', 'confirm');
            });
        });

    // Deposits routes
    Route::prefix('deposits')->controller(DepositController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('export', 'export');
    });

    // Profile routes
    Route::prefix('profile')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'show');
        Route::put('info', 'updateInfo');
        Route::post('password/request', 'requestPasswordChange');
        Route::post('password/verify', 'verifyPasswordChange');
    });

    // Lands routes
    Route::prefix('lands')->group(function () {
        Route::controller(LandsController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('owner-transfer/options', 'ownerTransferOptions');
            Route::post('owner-transfer', 'transferOwner');
            Route::put('features/{id}/properties', 'updateProperties');
            Route::put('features/{id}/coordinates', 'updateCoordinates');
        });

        Route::prefix('feature-limits')->controller(FeatureLimitsController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::delete('{id}', 'destroy');
        });

        Route::prefix('feature-pricing-limits')->controller(FeaturePricingLimitsController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'update');
        });

        Route::get('prices', [PricesController::class, 'index']);
        Route::get('pricing', [PricingController::class, 'index']);
        Route::get('sold', [SoldController::class, 'index']);
        Route::get('traded', [TradedController::class, 'index']);
    });

    // File Upload Controller
    Route::post('/upload/chunk', [FileUploadController::class, 'upload'])->withoutMiddleware('throttle:api');

    // Levels Controller
    Route::apiResource('levels', LevelsController::class)->except(['show']);

    // Level Prize Controller
    Route::prefix('levels/{level}/prize')->controller(LevelPrizeController::class)->group(function () {
        Route::get('/', 'show');
        Route::post('/', 'store');
        Route::put('/', 'update');
    });

    // Level Gift Controller
    Route::prefix('levels/{level}/gift')->controller(LevelGiftController::class)->group(function () {
        Route::get('/', 'show');
        Route::post('/', 'store');
        Route::put('/', 'update');
        Route::delete('files', 'destroyFile');
    });

    // Level License Controller
    Route::prefix('levels/{level}/licenses')->controller(LevelLicenseController::class)->group(function () {
        Route::get('/', 'show');
        Route::post('/', 'store');
        Route::put('/', 'update');
    });

    // Level Gem Controller
    Route::prefix('levels/{level}/gem')->controller(LevelGemController::class)->group(function () {
        Route::get('/', 'show');
        Route::post('/', 'store');
        Route::put('/', 'update');
        Route::delete('files', 'destroyFile');
    });

    // Level General Info Controller
    Route::prefix('levels/{level}/general-info')->controller(LevelGeneralInfoController::class)->group(function () {
        Route::get('/', 'show');
        Route::post('/', 'store');
        Route::put('/', 'update');
        Route::delete('files', 'destroyFile');
    });

    // User levels routes
    Route::controller(UserLevelsController::class)->group(function () {
        Route::prefix('user-levels')->group(function () {
            Route::get('/', 'index');
            Route::post('promote', 'promote');
        });
        Route::get('users/search', 'searchUsers');
    });

    // Access Management - Roles routes
    Route::prefix('roles')->controller(RolesController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('permissions', 'getPermissions');
        Route::get('{id}', 'show');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
        Route::delete('{roleId}/permissions/{permissionId}', 'removePermission');
    });

    // Access Management - Permissions routes
    Route::prefix('permissions')->controller(PermissionsController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('roles', 'getRoles');
        Route::get('{id}', 'show');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
        Route::delete('{permissionId}/roles/{roleId}', 'removeRole');
    });

    // Access Management - Admins routes
    Route::prefix('admins')->controller(AdminsController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('employees', 'getEmployees');
        Route::get('roles', 'getRoles');
        Route::get('{id}', 'show');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
        Route::delete('{adminId}/roles/{roleId}', 'removeRole');
        Route::delete('{adminId}/permissions/{permissionId}', 'removePermission');
    });

    // Support - Tickets routes
    Route::prefix('tickets')->controller(TicketsController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('departments', 'getDepartments');
        Route::post('{id}/response', 'sendResponse');
        Route::post('{id}/transfer', 'transfer');
    });

    // VariablesController routes
    Route::prefix('variables')->controller(VariablesController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
    });

    // System variables routes
    Route::prefix('system-variables')->controller(SystemVariablesController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('{system_variable}', 'update');
        Route::delete('{system_variable}', 'destroy');
    });

    // OptionsController routes
    Route::prefix('options')->controller(OptionsController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('variables', 'getVariables');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
    });

    // Video categories routes
    Route::apiResource('video-categories', VideoCategoriesController::class)->except(['show']);

    // Video sub categories routes
    Route::apiResource('video-sub-categories', VideoSubCategoriesController::class)->except(['show']);

    // Videos routes
    Route::prefix('videos')->group(function () {
        Route::get('meta', [VideosController::class, 'meta']);
        Route::post('chunk', VideoUploadController::class)->withoutMiddleware('throttle:api');
    });
    Route::apiResource('videos', VideosController::class)->except(['show']);

    // Dynasty routes group
    Route::prefix('dynasty')->group(function () {
        Route::prefix('messages')->controller(DynastyMessagesController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::put('{id}', 'update');
            Route::delete('{id}', 'destroy');
        });

        Route::prefix('prizes')->controller(DynastyPrizesController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::put('{id}', 'update');
            Route::delete('{id}', 'destroy');
        });

        Route::prefix('permissions')->controller(DynastyPermissionsController::class)->group(function () {
            Route::get('/', 'show');
            Route::put('/', 'update');
        });
    });

    // Maps routes
    Route::prefix('maps')->controller(MapsController::class)->group(function () {
        Route::post('{map}/insert-into-database', 'insertIntoDatabase');
    });
    Route::apiResource('maps', MapsController::class)->except(['show']);

    // ISIC Codes routes
    Route::prefix('isic-codes')->controller(IsicCodeController::class)->group(function () {
        Route::post('import', 'import');
        Route::post('{isicCode}/approve', 'approve');
        Route::post('{isicCode}/deny', 'deny');
    });
    Route::apiResource('isic-codes', IsicCodeController::class)->except(['show', 'update']);

    // Translations routes
    Route::prefix('translations')->group(function () {
        Route::controller(TranslationController::class)->group(function () {
            Route::get('languages', 'languages');
            Route::get('/', 'index')->withoutMiddleware('auth:sanctum');
            Route::get('{translation}', 'show');
            Route::post('/', 'store');
            Route::delete('{translation}', 'destroy');
            Route::patch('{translation}/status', 'toggleStatus');
            Route::post('{translation}/export', 'export');
        });

        Route::prefix('{translation}/modals')->controller(TranslationModalController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('{modal}', 'show');
            Route::post('/', 'store');
            Route::patch('{modal}', 'update');
            Route::delete('{modal}', 'destroy');

            Route::prefix('{modal}/tabs')->controller(TranslationTabController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('{tab}', 'show');
                Route::post('/', 'store');
                Route::patch('{tab}', 'update');
                Route::delete('{tab}', 'destroy');

                Route::prefix('{tab}/fields')->controller(TranslationFieldController::class)->group(function () {
                    Route::get('/', 'index');
                    Route::post('/', 'store');
                    Route::patch('{field}', 'update');
                    Route::delete('{field}', 'destroy');
                });
            });
        });
    });

    // Bulk messaging routes (super-admin only)
    Route::prefix('bulk-messages')->controller(BulkMessageController::class)->group(function () {
        Route::get('users/search', 'searchUsers');
        Route::post('send', 'send');
    });
});

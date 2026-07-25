<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\SendVerificationCode;
use App\Services\PhoneVerificationSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class VerificationController extends Controller
{
    public function __construct(
        private readonly PhoneVerificationSessionService $phoneVerificationSession
    ) {
    }

    /**
     * Send SMS verification code to authenticated admin.
     */
    public function sendSMS(): JsonResponse
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $cooldownSeconds = (int) config('phone_verification.sms_resend_cooldown_seconds', 60);
        $cooldownKey = 'verify.sms.cooldown.'.$admin->id;
        $cooldownUntil = Cache::get($cooldownKey);

        if (is_int($cooldownUntil) && $cooldownUntil > now()->timestamp) {
            $remaining = $cooldownUntil - now()->timestamp;

            return response()->json([
                'success' => false,
                'message' => 'لطفاً قبل از درخواست مجدد کمی صبر کنید.',
                'data' => [
                    'resend_available_in' => $remaining,
                ],
            ], 429);
        }

        try {
            $admin->notify(new SendVerificationCode);

            Cache::put(
                $cooldownKey,
                now()->addSeconds($cooldownSeconds)->timestamp,
                $cooldownSeconds
            );

            return response()->json([
                'success' => true,
                'message' => 'کد تایید با موفقیت ارسال گردید',
                'data' => [
                    'resend_available_in' => $cooldownSeconds,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال کد تایید',
            ], 500);
        }
    }

    /**
     * @deprecated Use confirm() to extend the phone verification session.
     */
    public function verify(Request $request): JsonResponse
    {
        if (! $this->phoneVerificationSession->isEnabled()) {
            return response()->json([
                'success' => true,
                'message' => 'کد تایید با موفقیت تایید شد',
            ]);
        }

        $validated = $request->validate([
            'phone_verification' => ['required', 'integer', 'digits:6', 'is_valid_verify_code'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'کد تایید با موفقیت تایید شد',
            'data' => [
                'phone_verification' => $validated['phone_verification'],
            ],
        ]);
    }

    /**
     * Get the current phone verification session status.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->phoneVerificationSession->getStatus(),
        ]);
    }

    /**
     * Confirm SMS code and extend the phone verification session.
     */
    public function confirm(Request $request): JsonResponse
    {
        if (! $this->phoneVerificationSession->isEnabled()) {
            $duration = $this->phoneVerificationSession->clampDuration(
                (int) $request->input('duration_minutes', config('phone_verification.default_duration_minutes', 15))
            );

            $this->phoneVerificationSession->confirm($duration);

            return response()->json([
                'success' => true,
                'message' => 'جلسه تایید شماره موبایل با موفقیت تمدید شد.',
                'data' => $this->phoneVerificationSession->getStatus(),
            ]);
        }

        $minDuration = (int) config('phone_verification.min_duration_minutes', 5);
        $maxDuration = (int) config('phone_verification.max_duration_minutes', 50);

        $validated = $request->validate([
            'phone_verification' => ['required', 'integer', 'digits:6', 'is_valid_verify_code'],
            'duration_minutes' => [
                'required',
                'integer',
                "min:{$minDuration}",
                "max:{$maxDuration}",
            ],
        ]);

        $adminId = Auth::guard('admin')->id();
        Cache::forget('verify.code.'.$adminId);

        $duration = $this->phoneVerificationSession->clampDuration((int) $validated['duration_minutes']);
        $this->phoneVerificationSession->confirm($duration);

        return response()->json([
            'success' => true,
            'message' => 'جلسه تایید شماره موبایل با موفقیت تمدید شد.',
            'data' => $this->phoneVerificationSession->getStatus(),
        ]);
    }
}

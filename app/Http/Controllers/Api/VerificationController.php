<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\SendVerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    /**
     * Send SMS verification code to authenticated admin
     *
     * @return JsonResponse
     */
    public function sendSMS(): JsonResponse
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        try {
            $admin->notify(new SendVerificationCode);

            return response()->json([
                'success' => true,
                'message' => 'کد تایید با موفقیت ارسال گردید',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال کد تایید',
            ], 500);
        }
    }

    /**
     * Validate SMS verification code without consuming it.
     */
    public function verify(Request $request): JsonResponse
    {
        if (!app()->environment('production')) {
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
}


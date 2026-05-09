<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\SendVerificationCode;
use Illuminate\Http\JsonResponse;
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
}


<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AdminTabSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AdminTabSessionController extends Controller
{
    public function __construct(
        private readonly AdminTabSessionService $tabSessionService,
    ) {}

    public function claim(Request $request): JsonResponse
    {
        $token = $this->tabSessionService->tokenFromRequest($request);
        $tabId = $request->header('X-Admin-Tab-Id');

        if (! $token instanceof PersonalAccessToken || ! $this->tabSessionService->isValidTabId($tabId)) {
            return response()->json([
                'success' => false,
                'code' => 'admin_tab_id_required',
                'message' => 'شناسه تب معتبر ارسال نشده است',
            ], 400);
        }

        if (! $this->tabSessionService->belongsToUser($token, $request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر احراز هویت نشده است',
            ], 401);
        }

        $this->tabSessionService->claim($token, $tabId);

        return response()->json([
            'success' => true,
            'message' => 'این تب فعال شد',
        ]);
    }
}

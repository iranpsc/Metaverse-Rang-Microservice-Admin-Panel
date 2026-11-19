<?php

namespace App\Http\Middleware;

use App\Services\Auth\AdminTabSessionService;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAdminTab
{
    public function __construct(
        private readonly AdminTabSessionService $tabSessionService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->tabSessionService->tokenFromRequest($request);

        // Sanctum's in-memory test token does not represent a browser session.
        if (! $token instanceof PersonalAccessToken) {
            return $next($request);
        }

        if (! $this->tabSessionService->belongsToUser($token, $request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر احراز هویت نشده است',
            ], 401);
        }

        $tabId = $request->header('X-Admin-Tab-Id');

        if (! $this->tabSessionService->isValidTabId($tabId)) {
            return response()->json([
                'success' => false,
                'code' => 'admin_tab_id_required',
                'message' => 'شناسه تب معتبر ارسال نشده است',
            ], 400);
        }

        // The first authenticated request claims an unowned token. Subsequent
        // ownership changes happen explicitly through the claim endpoint.
        if ($token->active_tab_id === null) {
            $this->tabSessionService->claim($token, $tabId);

            return $next($request);
        }

        if (! $this->tabSessionService->ownsTab($token, $tabId)) {
            return response()->json([
                'success' => false,
                'code' => 'admin_tab_conflict',
                'message' => 'این نشست در تب دیگری فعال است',
            ], 409);
        }

        return $next($request);
    }
}

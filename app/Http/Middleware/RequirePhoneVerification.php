<?php

namespace App\Http\Middleware;

use App\Services\PhoneVerificationSessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePhoneVerification
{
    private const EXCLUDED_PATHS = [
        'api/send-verification-sms',
        'api/verify-verification-sms',
        'api/phone-verification/status',
        'api/phone-verification/confirm',
        'api/login',
        'api/logout',
        'api/password/email',
        'api/password/reset',
    ];

    public function __construct(
        private readonly PhoneVerificationSessionService $phoneVerificationSession
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            return $next($request);
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        if ($this->isExcludedPath($request)) {
            return $next($request);
        }

        if ($this->phoneVerificationSession->isVerified()) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'تایید شماره موبایل منقضی شده است. لطفاً مجدداً تایید کنید.',
            'requires_phone_verification' => true,
        ], 423);
    }

    private function isExcludedPath(Request $request): bool
    {
        $path = trim($request->path(), '/');

        return in_array($path, self::EXCLUDED_PATHS, true);
    }
}

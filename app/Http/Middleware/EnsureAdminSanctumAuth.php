<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSanctumAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated via Sanctum token, set admin guard
        if ($request->user()) {
            // Spatie role middleware reads the default guard. API admins authenticate on admin.
            Auth::guard('admin')->setUser($request->user());
            Auth::shouldUse('admin');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Ip;
use Closure;
use Illuminate\Http\Request;

class FilterIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('api.translations') || app()->environment('local')) {
            return $next($request);
        }

        return Ip::admin()->where('from', ip2long($request->ip()))->doesntExist()
            ? abort(403, 'Unauthorized IP address.')
            : $next($request);
    }
}

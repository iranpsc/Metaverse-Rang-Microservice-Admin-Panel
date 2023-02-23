<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Ip;

class BlockIpMiddleware
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
        $ipWhiteList = [
            '2.187.99.104',
            '2.187.98.25',
            '127.0.0.1'
        ];
        return !in_array($request->ip(), $ipWhiteList) ? abort(401, 'UnAuthorize') : $next($request);
    }
}

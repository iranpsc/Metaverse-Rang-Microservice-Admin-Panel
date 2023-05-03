<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Ip;
use Illuminate\Support\Facades\App;

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
            '2.187.98.75',
            '2.187.99.118',
            '2.187.99.119',
            '89.199.177.2'
        ];
        return !in_array($request->ip(), $ipWhiteList) && App::isProduction()
            ? redirect()->back()
            : $next($request);
    }
}

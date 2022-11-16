<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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
        $allowedIps = [];
        if (file_exists(storage_path('/ip-management/ips.json'))) {
            $ips = file_get_contents(storage_path('/ip-management/ips.json'));
            $ips = json_decode($ips, true);
            if (array_key_exists('admin-allowed-ips', $ips)) {
                $allowedIps = $ips['admin-allowed-ips'];
            }
        }
        if (count($allowedIps) == 0) {
            abort(403, 'Access Denied');
        } else {
            for ($i = 0; $i < count($allowedIps); $i++) {
                if (! strcmp($request->ip(), $allowedIps[$i]['ip'])) {
                    abort(403, 'Access Denied');
                }
            }
            return $next($request);
        }
    }
}

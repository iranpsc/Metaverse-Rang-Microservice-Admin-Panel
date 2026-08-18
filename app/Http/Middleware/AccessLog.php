<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $log = sprintf(
            "%s - %s [%s] \"%s %s\" %d",
            $request->ip(),
            $request->userAgent(),
            date('d/M/Y:H:i:s O'),
            $request->method(),
            $request->path(),
            $response->getStatusCode()
        );

        file_put_contents(storage_path('logs/access.log'), $log . PHP_EOL, FILE_APPEND);

        return $response;
    }
}

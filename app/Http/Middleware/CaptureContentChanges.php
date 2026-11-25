<?php

namespace App\Http\Middleware;

use App\Services\ContentWebhooks\ChangeRecorder;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CaptureContentChanges
{
    public function handle(Request $request, Closure $next)
    {
        $tracked = preg_match('#^api/(levels|calendars|versions|translations|videos|video-categories|video-sub-categories)(/|$)#', $request->path());
        if (! $tracked || $request->isMethodSafe()) {
            return $next($request);
        }
        $translation = $request->is('api/translations', 'api/translations/*');
        $connection = $translation ? 'sqlite' : config('database.default');
        $db = Model::resolveConnection($connection);
        if (! $db->getSchemaBuilder()->hasTable('content_change_outbox')) {
            return $next($request);
        }
        $db->beginTransaction();
        try {
            $response = $next($request);
            if ($response->getStatusCode() >= 400) {
                $db->rollBack();

                return $response;
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
        if ($translation && ! $request->routeIs('translations.export', 'translations.fields.*')) {
            return $response;
        }

        // A projection outage must not report a committed edit as failed. The
        // durable source entry is retried by the scheduler and before dispatch.
        try {
            app(ChangeRecorder::class)->reconcile();
        } catch (\Throwable $e) {
            report($e);
        }

        return $response;
    }
}

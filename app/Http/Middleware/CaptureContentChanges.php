<?php

namespace App\Http\Middleware;

use App\Services\ContentWebhooks\ChangeRecorder;
use App\Services\ContentWebhooks\TranslationPublisher;
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
            $before = $translation ? app(TranslationPublisher::class)->snapshot() : [];
            $response = $next($request);
            if ($response->getStatusCode() >= 400) {
                $db->rollBack();

                return $response;
            }
            if ($translation) {
                $operation = $request->isMethod('DELETE') ? 'deleted' :
                    ($request->isMethod('POST') && ! $request->is('*/import', '*/export') ? 'created' : 'updated');
                app(TranslationPublisher::class)->record($before, $operation);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
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

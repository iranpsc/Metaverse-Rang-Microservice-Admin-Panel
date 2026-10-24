<?php

namespace App\Services\ContentWebhooks;

use Illuminate\Database\Eloquent\Model;

class DeletionContext
{
    private static ?\WeakMap $snapshots = null;

    public function remember(Model $model): void
    {
        self::$snapshots ??= new \WeakMap;
        self::$snapshots[$model] ??= app(RouteMapper::class)->map($model, 'deleted');
    }

    public function get(Model $model): ?array
    {
        return self::$snapshots[$model] ?? null;
    }
}

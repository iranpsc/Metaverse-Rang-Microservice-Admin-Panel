<?php

namespace App\Observers;

use App\Services\ActivityLoggerService;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class ModelActivityObserver
{
    /** @var array<int, class-string<Model>> */
    protected static array $excluded = [
        Activity::class,
    ];

    public function created(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            return;
        }

        ActivityLoggerService::logModelEvent($model, 'created', $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        if ($this->shouldSkip($model) || ! $model->wasChanged()) {
            return;
        }

        $changes = $model->getChanges();
        $original = array_intersect_key($model->getOriginal(), $changes);

        ActivityLoggerService::logModelEvent($model, 'updated', $changes, $original);
    }

    public function deleted(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            return;
        }

        ActivityLoggerService::logModelEvent($model, 'deleted');
    }

    protected function shouldSkip(Model $model): bool
    {
        if (! config('activitylog.enabled', true)) {
            return true;
        }

        return in_array($model::class, self::$excluded, true);
    }
}

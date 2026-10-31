<?php

namespace App\Observers;

use App\Services\ActivityLoggerService;
use App\Services\ContentWebhooks\DeletionContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
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

    /** Cache deletion targets before cascading relationships disappear. */
    public function deleting(Model $model): void
    {
        if (Schema::connection($model->getConnectionName())->hasTable('content_change_outbox')) {
            app(DeletionContext::class)->remember($model);
        }
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

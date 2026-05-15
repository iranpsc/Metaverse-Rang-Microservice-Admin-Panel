<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLoggerService
{
    public static function causer(): ?Admin
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();

        return $user instanceof Admin ? $user : null;
    }

    public static function logAuth(string $event, string $description, array $properties = []): void
    {
        $builder = activity(ActivityLogCategoryResolver::CATEGORY_AUTH)
            ->event($event)
            ->withProperties(array_merge($properties, ['category' => ActivityLogCategoryResolver::CATEGORY_AUTH]));

        $causer = self::causer();
        if ($causer) {
            $builder->causedBy($causer);
        }

        $builder->log($description);
    }

    public static function logModelEvent(Model $model, string $event, ?array $changes = null, ?array $original = null): void
    {
        $category = ActivityLogCategoryResolver::resolveForModel($model);
        $description = self::buildModelDescription($model, $event);

        $properties = [
            'category' => $category,
            'model' => $model::class,
            'model_id' => $model->getKey(),
        ];

        if ($changes !== null) {
            $properties['attributes'] = self::filterSensitive($changes);
        }

        if ($original !== null) {
            $properties['old'] = self::filterSensitive($original);
        }

        $builder = activity($category)
            ->performedOn($model)
            ->event($event)
            ->withProperties($properties);

        $causer = self::causer();
        if ($causer) {
            $builder->causedBy($causer);
        }

        $builder->log($description);
    }

    protected static function buildModelDescription(Model $model, string $event): string
    {
        $modelName = class_basename($model);
        $id = $model->getKey();

        return match ($event) {
            'created' => "{$modelName} #{$id} created",
            'updated' => "{$modelName} #{$id} updated",
            'deleted' => "{$modelName} #{$id} deleted",
            default => "{$modelName} #{$id} {$event}",
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function filterSensitive(array $data): array
    {
        $hidden = ['password', 'remember_token', 'token', 'secret'];

        return collect($data)
            ->reject(fn ($value, $key) => in_array($key, $hidden, true))
            ->map(fn ($value) => is_string($value) && strlen($value) > 500 ? '[truncated]' : $value)
            ->all();
    }
}

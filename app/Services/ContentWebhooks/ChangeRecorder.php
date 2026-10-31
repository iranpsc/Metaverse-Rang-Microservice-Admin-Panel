<?php

namespace App\Services\ContentWebhooks;

use App\Models\Admin;
use App\Services\ActivityLoggerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ChangeRecorder
{
    public static function actor(): ?array
    {
        $actor = ActivityLoggerService::causer();

        return $actor ? ['id' => (string) $actor->id, 'name' => $actor->name, 'roles' => $actor->getRoleNames()->all()] : null;
    }

    public function model(Model $model, string $event, ?array $changes, ?array $original): bool
    {
        if (! Schema::connection($model->getConnectionName())->hasTable('content_change_outbox')) {
            return false;
        }
        $mapping = ($event === 'deleted' ? app(DeletionContext::class)->get($model) : null)
            ?? app(RouteMapper::class)->map($model, $event);
        if (! $mapping) {
            return false;
        }
        $fields = array_diff_key($changes ?? [], array_flip(['created_at', 'updated_at']));
        if ($event === 'updated' && ! $fields) {
            return true;
        }
        $labels = implode(', ', array_map(fn ($key) => str_replace('_', ' ', $key), array_keys($fields)));
        $subject = match ($mapping['feature']) {
            'citizen_levels', 'citizen_levels_details' => 'citizen level',
            'video_categories' => 'video category', 'video_sub_categories' => 'video subcategory',
            'videos' => 'video', 'versions' => 'version', default => 'calendar entry',
        };
        $part = $mapping['resource'] === 'record' ? '' : ' '.str_replace('-', ' ', $mapping['resource']);
        $detail = $event === 'updated' && $mapping['resource'] !== 'image' ? ' '.$labels : '';
        $this->write($model->getConnectionName(), [...$mapping,
            'event' => $event, 'action' => match ($event) {
                'created' => 'CREATE', 'deleted' => 'DELETE', default => 'UPDATE'
            },
            'description' => "The {$subject} '{$mapping['name']}'{$part}{$detail} has been {$event}.",
            'model' => $model::class, 'model_id' => (string) $model->getKey(),
            'attributes' => $fields, 'old' => $original ?? [],
        ]);

        return true;
    }

    public function write(?string $connection, array $data): void
    {
        DB::connection($connection)->table('content_change_outbox')->insert([
            'id' => (string) Str::uuid(), 'data' => json_encode([...$data, 'modifier' => self::actor()], JSON_THROW_ON_ERROR),
            'created_at' => now('UTC'),
        ]);
    }

    public function reconcile(): void
    {
        foreach (array_unique([config('database.default'), 'sqlite']) as $connection) {
            if (! Schema::connection($connection)->hasTable('content_change_outbox')) {
                continue;
            }
            DB::connection($connection)->table('content_change_outbox')->whereNull('projected_at')->orderBy('created_at')->get()->each(function ($source) use ($connection) {
                DB::transaction(function () use ($source) {
                    // Serialize projectors without waiting for outbound HTTP.
                    // SQLite has one writer and no row locks; reserve it before reading.
                    if (DB::connection()->getDriverName() === 'sqlite') {
                        DB::table('content_delivery_state')->where('id', 2)->update(['next_patch' => DB::raw('next_patch')]);
                    }
                    DB::table('content_delivery_state')->where('id', 2)->lockForUpdate()->first();
                    if (DB::table('content_change_receipts')->where('source_id', $source->id)->exists()) {
                        return;
                    }
                    $data = json_decode($source->data, true, flags: JSON_THROW_ON_ERROR);
                    $activity = new Activity;
                    $activity->log_name = $data['category'];
                    $activity->event = $data['event'];
                    $activity->description = $data['description'];
                    $activity->properties = collect($data);
                    $activity->causer_type = $data['modifier'] ? Admin::class : null;
                    $activity->causer_id = $data['modifier']['id'] ?? null;
                    $activity->created_at = $source->created_at;
                    $activity->save();
                    DB::table('content_change_receipts')->insert(['source_id' => $source->id, 'activity_id' => $activity->id]);
                    if (empty($data['affected_routes'])) {
                        return;
                    }
                    DB::table('content_changes')->insert(['source_id' => $source->id, 'activity_id' => $activity->id,
                        'data' => $source->data, 'created_at' => $source->created_at, 'updated_at' => now('UTC')]);
                });
                DB::connection($connection)->table('content_change_outbox')->where('id', $source->id)->update(['projected_at' => now('UTC')]);
            });
        }
    }
}

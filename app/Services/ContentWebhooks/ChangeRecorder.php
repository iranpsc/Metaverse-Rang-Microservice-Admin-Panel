<?php

namespace App\Services\ContentWebhooks;

use App\Models\Admin;
use App\Models\Translations\Field;
use App\Models\Translations\Translation;
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
        $localizedSubject = match ($subject) {
            'citizen level' => __('content_webhooks.subjects.citizen_level'),
            'video category' => __('content_webhooks.subjects.video_category'),
            'video subcategory' => __('content_webhooks.subjects.video_subcategory'),
            'video' => __('content_webhooks.subjects.video'),
            'version' => __('content_webhooks.subjects.version'),
            default => __('content_webhooks.subjects.calendar_entry'),
        };
        $localizedEvent = __('content_webhooks.events.'.$event);
        $this->write($model->getConnectionName(), [...$mapping,
            'event' => $event, 'action' => match ($event) {
                'created' => 'CREATE', 'deleted' => 'DELETE', default => 'UPDATE'
            },
            'description' => __('content_webhooks.activity_description', [
                'subject' => $localizedSubject, 'name' => $mapping['name'], 'part' => $part,
                'detail' => $detail, 'event' => $localizedEvent,
            ]),
            'model' => $model::class, 'model_id' => (string) $model->getKey(),
            'attributes' => $fields, 'old' => $original ?? [],
        ]);

        return true;
    }

    /**
     * Record one manual field mutation for audit without any invalidation target.
     *
     * @param  Translation  $translation  Locale selected by the admin.
     * @param  Field  $field  Field row, including its shared unique ID.
     * @param  'created'|'updated'|'deleted'  $event  Successful manual mutation.
     * @return void Queues the activity log on the translation SQLite connection.
     */
    public function translationField(Translation $translation, Field $field, string $event): void
    {
        if (! Schema::connection('sqlite')->hasTable('content_change_outbox')) {
            return;
        }

        $this->write('sqlite', [
            'feature' => 'translations',
            'category' => 'translations',
            'entity_id' => (string) $translation->id,
            'locale' => $translation->code,
            'resource' => 'field',
            'model' => Field::class,
            'model_id' => (string) $field->id,
            'event' => $event,
            'action' => match ($event) {
                'created' => 'CREATE', 'updated' => 'UPDATE', 'deleted' => 'DELETE',
            },
            'description' => __('content_webhooks.translation_field_activity', [
                'id' => $field->unique_id ?? $field->id,
                'locale' => $translation->code,
                'event' => __('content_webhooks.events.'.$event),
            ]),
            'attributes' => ['unique_id' => $field->unique_id, 'tab_id' => $field->tab_id],
            'affected_routes' => [],
        ]);
    }

    /**
     * Write the change to its source outbox using the supplied actor or the current admin.
     *
     * @param  string|null  $connection  Source database connection, with null selecting the default.
     * @param  array<string, mixed>  $data  Change event to serialize into the outbox.
     * @param  array{id: string, name: string, roles: list<string>}|null  $actor  Admin captured when the work was queued.
     * @return void The event is available for later projection.
     */
    public function write(?string $connection, array $data, ?array $actor = null): void
    {
        DB::connection($connection)->table('content_change_outbox')->insert([
            'id' => (string) Str::uuid(), 'data' => json_encode([...$data, 'modifier' => $actor ?? self::actor()], JSON_THROW_ON_ERROR),
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
                    if (empty($data['affected_routes']) || ($data['feature'] === 'translations' && $data['event'] !== 'exported')) {
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

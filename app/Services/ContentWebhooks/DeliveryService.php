<?php

namespace App\Services\ContentWebhooks;

use App\Jobs\DeliverContentWebhook;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeliveryService
{
    public function oldestQueuedBatch(array $columns = ['*']): ?object
    {
        return DB::table('content_webhook_batches')
            ->whereIn('status', ['pending', 'failed'])
            ->oldest()
            ->first($columns);
    }

    public function cancel(string $cycleId): void
    {
        DB::transaction(function () use ($cycleId) {
            $this->reserveSqliteWriter();
            $state = DB::table('content_delivery_state')->where('id', 1)->lockForUpdate()->first();
            abort_unless($state && $state->active_cycle === $cycleId, 409, 'This delivery cycle is no longer active.');
            $this->finish($cycleId, 'cancelled');
        });
    }

    public function trigger(string $trigger = 'manual', ?array $actor = null, ?string $retry = null): string
    {
        abort_unless(config('content_webhooks.enabled'), 409, 'Content webhook delivery is disabled.');
        app(ChangeRecorder::class)->reconcile();

        return DB::transaction(function () use ($trigger, $actor, $retry) {
            $this->reserveSqliteWriter();
            $state = DB::table('content_delivery_state')->where('id', 1)->lockForUpdate()->first();
            $oldest = $this->oldestQueuedBatch();
            if ($retry) {
                abort_unless($oldest && $oldest->id === $retry && $oldest->status === 'failed', 409, 'Only the oldest failed pack can be retried.');
            } elseif ($oldest?->status === 'failed') {
                abort(409, 'The oldest failed pack must be retried.');
            }
            if ($state->active_cycle) {
                return $state->active_cycle;
            }
            if ($oldest) {
                DB::table('content_webhook_batches')->where('id', $oldest->id)->update(['status' => 'pending', 'retry_attempts' => 0, 'available_at' => null]);
            }
            $id = (string) Str::uuid();
            DB::table('content_delivery_cycles')->insert(['id' => $id, 'cutoff' => DB::table('content_changes')->max('id') ?? 0,
                'trigger' => $trigger, 'actor' => $actor ? json_encode($actor, JSON_THROW_ON_ERROR) : null,
                'status' => 'pending', 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
            DB::table('content_delivery_state')->where('id', 1)->update(['active_cycle' => $id]);
            DeliverContentWebhook::dispatch($id)->afterCommit();

            return $id;
        });
    }

    /** Returns a retry delay, or null when this cycle is finished. */
    public function deliver(string $cycleId): ?int
    {
        // Serialize workers through the HTTP acknowledgement. An interrupted
        // transaction rolls back and the immutable batch is safely retried.
        return DB::transaction(function () use ($cycleId) {
            $this->reserveSqliteWriter();
            $state = DB::table('content_delivery_state')->where('id', 1)->lockForUpdate()->first();
            if ($state->active_cycle !== $cycleId) {
                return null;
            }
            if (! config('content_webhooks.enabled')) {
                // Leave the durable cycle active, but remove this queue message.
                // Recovery resumes it when enabled, without endless releases.
                return null;
            }
            $cycle = DB::table('content_delivery_cycles')->where('id', $cycleId)->first();
            $batch = $this->oldestQueuedBatch();
            if (! $batch) {
                $changes = DB::table('content_changes')->whereNull('batch_id')->where('id', '<=', $cycle->cutoff)->orderBy('id')->get();
                if ($changes->isEmpty()) {
                    $this->finish($cycleId, 'delivered');

                    return null;
                }
                $this->pack($changes, $cycle, $state->next_patch);

                // Commit membership/payload BEFORE any network I/O. A timeout or
                // crash must never result in a different retry body or batch ID.
                return 0;
            }
            if ($batch->status === 'failed') {
                $this->finish($cycleId, 'failed');

                return null;
            }
            if ($batch->available_at && now('UTC')->lt($batch->available_at)) {
                return max(1, (int) now('UTC')->diffInSeconds($batch->available_at));
            }
            $status = null;
            $responseBody = null;
            $error = null;
            $retryable = true;
            try {
                $url = config('content_webhooks.url');
                $secret = config('content_webhooks.secret');
                if (! $url || ! $secret) {
                    throw new \RuntimeException('Webhook URL and secret must be configured.');
                }
                $response = Http::connectTimeout(5)->timeout(15)->withoutRedirecting()
                    ->withHeaders(['x-webhook-secret' => $secret])->withBody($batch->payload, 'application/json')->post($url);
                $status = $response->status();
                $responseBody = substr($response->body(), 0, 60000);
                $receiverResponse = $response->json();
                $receiverData = is_array($receiverResponse) ? ($receiverResponse['data'] ?? null) : null;
                $acknowledgement = is_array($receiverData)
                    ? $receiverData
                    : $receiverResponse;
                $isFlatAcknowledgement = is_array($acknowledgement)
                    && ($acknowledgement['batch_id'] ?? null) === $batch->id
                    && ($acknowledgement['status'] ?? null) === 'accepted';
                $isSuccessEnvelope = is_array($receiverResponse)
                    && ($receiverResponse['success'] ?? false) === true
                    && is_array($acknowledgement)
                    && ($acknowledgement['batch_id'] ?? null) === $batch->id;
                if ($status !== 200 || (! $isFlatAcknowledgement && ! $isSuccessEnvelope)) {
                    $error = 'Receiver did not acknowledge this batch.';
                    $retryable = in_array($status, [408, 429]) || $status >= 500;
                }
            } catch (\Throwable $e) {
                $error = 'Delivery failed: '.class_basename($e);
                report($e);
            }
            $attempt = $batch->retry_attempts + 1;
            DB::table('content_webhook_attempts')->insert(['batch_id' => $batch->id, 'number' => $batch->attempts + 1,
                'http_status' => $status, 'error' => $error, 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
            DB::table('content_delivery_cycles')->where('id', $cycleId)->update([
                'last_batch_id' => $batch->id,
                'last_http_status' => $status,
                'last_response_body' => $responseBody,
                'last_error' => $error,
                'updated_at' => now('UTC'),
            ]);
            $updates = ['attempts' => $batch->attempts + 1, 'retry_attempts' => $attempt, 'http_status' => $status,
                'error' => $error, 'updated_at' => now('UTC')];
            if (! $error) {
                DB::table('content_webhook_batches')->where('id', $batch->id)->update([...$updates, 'status' => 'delivered', 'delivered_at' => now('UTC')]);
                DB::table('content_delivery_state')->where('id', 1)->increment('next_patch');
                Log::info('Content webhook delivered', ['batch_id' => $batch->id, 'version' => $batch->version]);

                return 0;
            }
            $failed = ! $retryable || $attempt >= 5;
            $delay = [10, 30, 120, 300][$attempt - 1] ?? 300;
            DB::table('content_webhook_batches')->where('id', $batch->id)->update([...$updates,
                'status' => $failed ? 'failed' : 'pending', 'available_at' => now('UTC')->addSeconds($delay)]);
            if ($failed) {
                $this->finish($cycleId, 'failed');
                Log::error('Content webhook failed', ['batch_id' => $batch->id, 'http_status' => $status]);

                return null;
            }

            return $delay;
        });
    }

    private function reserveSqliteWriter(): void
    {
        // Reserve before the first read: upgrading a stale SQLite read snapshot
        // to a writer fails immediately even when busy_timeout is configured.
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::table('content_delivery_state')->where('id', 1)->update(['next_patch' => DB::raw('next_patch')]);
        }
    }

    private function pack(Collection $changes, object $cycle, int $patch): void
    {
        $id = (string) Str::uuid();
        $groups = [];
        foreach ($changes as $change) {
            $data = json_decode($change->data, true, flags: JSON_THROW_ON_ERROR);
            $key = json_encode([$data['feature'], $data['entity_id'], $data['resource'], $data['locale'] ?? null], JSON_THROW_ON_ERROR);
            $groups[$key] ??= array_intersect_key($data, array_flip(['feature', 'entity_id', 'resource', 'locale'])) + ['actions' => [], 'modifiers' => [], 'affected_routes' => []];
            $groups[$key]['actions'][] = $data['action'];
            if ($data['feature'] === 'translations' && array_key_exists('unique_ids', $data)) {
                $groups[$key]['unique_ids'] ??= [];
                foreach ($data['unique_ids'] as $uniqueId) {
                    $groups[$key]['unique_ids'][$uniqueId] = $uniqueId;
                }
            }
            if ($data['modifier']) {
                $modifier = $this->payloadActor($data['modifier']);
                $groups[$key]['modifiers'][json_encode($modifier, JSON_THROW_ON_ERROR)] = $modifier;
            }
            foreach ($data['affected_routes'] as $route) {
                $groups[$key]['affected_routes'][$route] = $route;
            }
        }
        $groupedChanges = array_map(static function (array $group): array {
            $group['actions'] = array_values(array_unique($group['actions']));
            $group['modifiers'] = array_values($group['modifiers']);
            $group['affected_routes'] = array_values($group['affected_routes']);
            if (isset($group['unique_ids'])) {
                $group['unique_ids'] = array_values($group['unique_ids']);
                sort($group['unique_ids'], SORT_NUMERIC);
            }

            return $group;
        }, array_values($groups));
        $version = 'V1.1.'.$patch;
        $payload = json_encode(['event' => 'admin.content.batch', 'batch_id' => $id, 'version' => $version,
            'timestamp' => now('UTC')->toISOString(), 'trigger' => $cycle->trigger,
            'triggered_by' => $cycle->actor
                ? $this->payloadActor(json_decode($cycle->actor, true, flags: JSON_THROW_ON_ERROR))
                : null,
            'changes' => $groupedChanges], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        DB::table('content_webhook_batches')->insert(['id' => $id, 'version' => $version, 'payload' => $payload,
            'status' => 'pending', 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
        DB::table('content_changes')->whereIn('id', $changes->pluck('id'))->update(['batch_id' => $id]);
    }

    private function payloadActor(array $actor): array
    {
        return ['id' => $actor['id'], 'name' => $actor['name']];
    }

    private function finish(string $cycleId, string $status): void
    {
        DB::table('content_delivery_cycles')->where('id', $cycleId)->update(['status' => $status, 'updated_at' => now('UTC')]);
        DB::table('content_delivery_state')->where('id', 1)->update(['active_cycle' => null]);
    }
}

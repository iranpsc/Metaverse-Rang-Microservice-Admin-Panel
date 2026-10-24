<?php

namespace Tests\Feature\ContentWebhooks;

use App\Jobs\DeliverContentWebhook;
use App\Models\Image;
use App\Models\Level\Level;
use App\Models\Level\LevelGift;
use App\Services\ContentWebhooks\ChangeRecorder;
use App\Services\ContentWebhooks\DeliveryService;
use App\Services\ContentWebhooks\RouteMapper;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Factory;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class ContentWebhookTest extends TestCase
{
    use ActsAsSuperAdmin, CreatesAuthApiSchema;

    private $realQueue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthApiSchema();
        Schema::create('levels', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug');
            $t->string('score');
            $t->string('background_image')->nullable();
            $t->timestamps();
        });
        (require database_path('migrations/2026_10_22_000001_create_content_webhook_tables.php'))->up();
        config(['content_webhooks.enabled' => true, 'content_webhooks.url' => 'https://receiver.test/webhook', 'content_webhooks.secret' => 'test-secret']);
        $this->realQueue = Queue::getFacadeRoot();
        Queue::fake();
        Http::preventStrayRequests();
    }

    private function change(string $route = '/api/levels/12/gift', ?array $actor = null): void
    {
        app(ChangeRecorder::class)->write(null, ['feature' => 'citizen_levels_details', 'entity_id' => '12',
            'resource' => 'gift', 'category' => 'levels', 'action' => 'UPDATE', 'event' => 'updated',
            'description' => 'Gift changed', 'affected_routes' => [$route]]);
    }

    private function accept(): void
    {
        Http::swap(new Factory);
        Http::fake(fn ($request) => Http::response(['batch_id' => $request['batch_id'], 'status' => 'accepted']));
    }

    public function test_repeated_changes_are_one_signed_pack_and_later_changes_stay_pending(): void
    {
        $this->change();
        $this->change();
        $service = app(DeliveryService::class);
        $cycle = $service->trigger();
        $this->assertSame($cycle, $service->trigger('scheduled'));
        $this->assertSame(0, $service->deliver($cycle)); // Freeze before transport.
        $batch = DB::table('content_webhook_batches')->first();
        $payload = json_decode($batch->payload, true);
        $this->assertSame('V1.1.1', $payload['version']);
        $this->assertCount(1, $payload['changes']);
        $this->assertSame(['/api/levels/12/gift'], $payload['affected_routes']);
        $this->change('/api/levels/12/gem');
        app(ChangeRecorder::class)->reconcile();
        $this->accept();
        $this->assertSame(0, $service->deliver($cycle));
        $this->assertNull($service->deliver($cycle));
        Http::assertSent(fn ($request) => $request->body() === $batch->payload &&
            $request->header('Authorization') === ['Bearer test-secret'] &&
            ! $request->hasHeader('X-Metarang-Version') && ! $request->hasHeader('X-Metarang-Batch-Id') &&
            ! $request->hasHeader('X-Metarang-Signature'));
        Http::assertSentCount(1);
        $this->assertSame(1, DB::table('content_changes')->whereNull('batch_id')->count());
        $this->assertEquals(2, DB::table('content_delivery_state')->value('next_patch'));
        $this->assertSame(3, DB::table('content_changes')->count());
    }

    public function test_failed_pack_reuses_payload_and_blocks_newer_changes_until_retry(): void
    {
        $this->change();
        $service = app(DeliveryService::class);
        $cycle = $service->trigger();
        $service->deliver($cycle);
        $batch = DB::table('content_webhook_batches')->first();
        Http::fake(fn () => Http::response('Unavailable', 503));
        foreach ([10, 30, 120, 300] as $delay) {
            $this->assertSame($delay, $service->deliver($cycle));
            $this->travel($delay + 1)->seconds();
        }
        $this->assertNull($service->deliver($cycle));
        $this->assertSame('failed', DB::table('content_webhook_batches')->value('status'));
        $this->assertEquals(1, DB::table('content_delivery_state')->value('next_patch'));
        $this->change('/api/levels/12/gem');
        $retryCycle = $service->trigger('manual', null, $batch->id);
        $this->accept();
        $service->deliver($retryCycle);
        $this->assertSame($batch->payload, DB::table('content_webhook_batches')->where('id', $batch->id)->value('payload'));
        $service->deliver($retryCycle);
        $next = DB::table('content_webhook_batches')->where('version', 'V1.1.2')->first();
        $this->assertNotNull($next);
        $service->deliver($retryCycle);
        $service->deliver($retryCycle);
        $this->assertEquals(3, DB::table('content_delivery_state')->value('next_patch'));
    }

    public function test_empty_cycle_and_disabled_delivery_do_not_consume_versions(): void
    {
        $service = app(DeliveryService::class);
        $cycle = $service->trigger();
        $this->assertNull($service->deliver($cycle));
        $this->change();
        $cycle = $service->trigger();
        config(['content_webhooks.enabled' => false]);
        $this->assertNull($service->deliver($cycle));
        $this->assertSame($cycle, DB::table('content_delivery_state')->where('id', 1)->value('active_cycle'));
        $this->assertEquals(1, DB::table('content_delivery_state')->value('next_patch'));
        Http::assertNothingSent();
    }

    public function test_invalid_acknowledgement_is_failed_without_advancing_version(): void
    {
        $this->change();
        $service = app(DeliveryService::class);
        $cycle = $service->trigger();
        $service->deliver($cycle);
        Http::fake(fn () => Http::response(['status' => 'accepted', 'batch_id' => 'wrong']));
        $this->assertNull($service->deliver($cycle));
        $this->assertSame('failed', DB::table('content_webhook_batches')->value('status'));
        $this->assertEquals(1, DB::table('content_delivery_state')->value('next_patch'));
    }

    public function test_source_projection_is_idempotent_and_rollbacks_do_not_publish(): void
    {
        DB::beginTransaction();
        Level::create(['name' => 'Diamond', 'slug' => 'diamond', 'score' => 1]);
        DB::rollBack();
        $this->assertSame(0, DB::table('content_change_outbox')->count());
        $level = Level::create(['name' => 'Diamond', 'slug' => 'diamond', 'score' => 1]);
        $level->update(['background_image' => 'new.png']);
        app(ChangeRecorder::class)->reconcile();
        $this->assertSame(2, DB::table('content_changes')->count());
        DB::table('content_change_outbox')->update(['projected_at' => null]);
        app(ChangeRecorder::class)->reconcile();
        $this->assertSame(2, DB::table('content_changes')->count());
        $this->assertStringContainsString("'Diamond' background image", Activity::latest('id')->first()->description);
    }

    public function test_level_details_map_without_invalidating_level_list(): void
    {
        $level = Level::create(['name' => 'Diamond', 'slug' => 'diamond', 'score' => 1]);
        $gift = new LevelGift(['level_id' => $level->id]);
        $mapping = app(RouteMapper::class)->map($gift, 'updated');
        $this->assertSame(["/api/levels/{$level->id}", "/api/levels/{$level->id}/gift"], $mapping['affected_routes']);
    }

    public function test_dispatch_authorization_and_delivery_details(): void
    {
        $this->getJson('/api/activity-logs/webhook-summary')->assertUnauthorized();
        $this->actingAsRegularAdmin();
        $this->postJson('/api/activity-logs/webhook-dispatch')->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->actingAsSuperAdmin();
        $this->change();
        app(ChangeRecorder::class)->reconcile();
        $this->getJson('/api/activity-logs/webhook-summary')->assertOk()->assertJsonPath('data.pending_count', 1);
        $id = DB::table('content_changes')->value('activity_id');
        $this->getJson('/api/activity-logs/'.$id)->assertOk()->assertJsonPath('data.activity.delivery.status', 'pending');
        $this->postJson('/api/activity-logs/webhook-dispatch')->assertStatus(202);
        Queue::assertPushed(DeliverContentWebhook::class);
    }

    public function test_every_member_exposes_failed_pending_and_delivered_states_through_api(): void
    {
        $this->actingAsSuperAdmin();
        $this->change();
        $this->change();
        $service = app(DeliveryService::class);
        $cycle = $service->trigger();
        $service->deliver($cycle);
        $ids = DB::table('content_changes')->pluck('activity_id');
        $batch = DB::table('content_webhook_batches')->first();
        Http::fake(fn () => Http::response([], 401));
        $service->deliver($cycle);
        foreach ($ids as $id) {
            $this->getJson('/api/activity-logs/'.$id)->assertOk()->assertJsonPath('data.activity.delivery.status', 'failed')->assertJsonPath('data.activity.delivery.attempts', 1);
        }
        $retry = $this->postJson('/api/activity-logs/webhook-batches/'.$batch->id.'/retry')->assertStatus(202)->json('data.cycle_id');
        foreach ($ids as $id) {
            $this->getJson('/api/activity-logs/'.$id)->assertJsonPath('data.activity.delivery.status', 'pending');
        }
        $this->accept();
        $service->deliver($retry);
        foreach ($ids as $id) {
            $this->getJson('/api/activity-logs/'.$id)->assertJsonPath('data.activity.delivery.status', 'delivered')->assertJsonPath('data.activity.delivery.attempts', 2)->assertJsonPath('data.activity.delivery.version', 'V1.1.1');
        }
        $this->getJson('/api/activity-logs/webhook-summary')->assertJsonPath('data.pending_count', 0)->assertJsonPath('data.next_version', 'V1.1.2');
    }

    public function test_real_database_queue_against_local_postman_receiver(): void
    {
        if (! getenv('CONTENT_WEBHOOK_SIMULATION')) {
            $this->markTestSkipped('Run with the local Postman simulation and CONTENT_WEBHOOK_SIMULATION=1.');
        }
        Schema::create('jobs', function (Blueprint $t) {
            $t->id();
            $t->string('queue')->index();
            $t->longText('payload');
            $t->unsignedTinyInteger('attempts');
            $t->unsignedInteger('reserved_at')->nullable();
            $t->unsignedInteger('available_at');
            $t->unsignedInteger('created_at');
        });
        Queue::swap($this->realQueue);
        Http::swap(new Factory);
        config(['content_webhooks.url' => 'http://127.0.0.1:4900/nextjs/api/webhooks/cache-revalidate',
            'content_webhooks.secret' => 'local-webhook-test-secret']);
        $work = fn () => $this->app->make('queue.worker')->runNextJob('content-webhooks', 'content-webhooks', new WorkerOptions(sleep: 0, maxTries: 0));
        $this->change();
        $this->change();
        app(DeliveryService::class)->trigger();
        $work();
        $work();
        $work();
        $this->assertSame('delivered', DB::table('content_webhook_batches')->value('status'));
        $this->assertSame(1, DB::table('content_webhook_attempts')->count());
        $this->assertSame(0, DB::table('jobs')->count());
        // A real duplicate acknowledgement from the stand-in is idempotent.
        $batch = DB::table('content_webhook_batches')->first();
        $ack = Http::withToken('local-webhook-test-secret')
            ->withBody($batch->payload, 'application/json')->post(config('content_webhooks.url'));
        $this->assertSame(200, $ack->status());
        $this->assertSame($batch->id, $ack->json('batch_id'));
        $this->change();
        config(['content_webhooks.url' => config('content_webhooks.url').'?failure=503']);
        app(DeliveryService::class)->trigger();
        $work();
        $work();
        $failedAttempt = DB::table('content_webhook_batches')->where('version', 'V1.1.2')->first();
        $this->assertSame('pending', $failedAttempt->status);
        $this->assertEquals(503, $failedAttempt->http_status);
        $this->assertSame(1, DB::table('jobs')->count());
        config(['content_webhooks.url' => 'http://127.0.0.1:4900/nextjs/api/webhooks/cache-revalidate?failure=timeout']);
        $this->travel(11)->seconds();
        $work();
        $timedOut = DB::table('content_webhook_batches')->where('id', $failedAttempt->id)->first();
        $this->assertEquals(2, $timedOut->attempts);
        $this->assertNull($timedOut->http_status);
        $this->assertSame('pending', $timedOut->status);
        config(['content_webhooks.url' => 'http://127.0.0.1:4900/nextjs/api/webhooks/cache-revalidate?failure=401']);
        $this->travel(31)->seconds();
        $work();
        $this->assertSame('failed', DB::table('content_webhook_batches')->where('id', $failedAttempt->id)->value('status'));
        config(['content_webhooks.url' => 'http://127.0.0.1:4900/nextjs/api/webhooks/cache-revalidate']);
        app(DeliveryService::class)->trigger('manual', null, $failedAttempt->id);
        $work();
        $work();
        $this->assertSame('delivered', DB::table('content_webhook_batches')->where('id', $failedAttempt->id)->value('status'));
        $this->assertEquals(3, DB::table('content_delivery_state')->where('id', 1)->value('next_patch'));
    }

    public function test_multiple_admins_share_pack_and_names_are_historical(): void
    {
        $first = $this->actingAsSuperAdmin();
        $first->name = 'First editor';
        $first->save();
        $this->change();
        $this->app['auth']->forgetGuards();
        $second = $this->actingAsSuperAdmin();
        $second->name = 'Second editor';
        $second->save();
        $this->change();
        $first->name = 'Renamed later';
        $first->save();
        $service = app(DeliveryService::class);
        $cycle = $service->trigger();
        $service->deliver($cycle);
        $payload = json_decode(DB::table('content_webhook_batches')->value('payload'), true);
        $this->assertSame(['First editor', 'Second editor'], array_column($payload['changes'][0]['modifiers'], 'name'));
        $id = DB::table('content_changes')->orderBy('id')->value('activity_id');
        $this->getJson('/api/activity-logs/'.$id)->assertOk()->assertJsonPath('data.activity.causer.name', 'First editor');
    }

    public function test_log_view_permission_does_not_grant_dispatch_and_disabled_delivery_is_rejected(): void
    {
        $admin = $this->actingAsRegularAdmin();
        $permission = Permission::create(['name' => 'view-activity-logs', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);
        $this->getJson('/api/activity-logs/webhook-summary')->assertOk()->assertJsonPath('data.can_send', false);
        $this->postJson('/api/activity-logs/webhook-dispatch')->assertForbidden();
        $admin->givePermissionTo(Permission::where('name', 'send-content-webhooks')->first());
        config(['content_webhooks.enabled' => false]);
        $this->postJson('/api/activity-logs/webhook-dispatch')->assertStatus(409);
        Queue::assertNothingPushed();
    }

    public function test_retry_endpoint_rejects_an_unrelated_batch_even_during_active_delivery(): void
    {
        $this->actingAsSuperAdmin();
        $this->change();
        app(DeliveryService::class)->trigger();
        $this->postJson('/api/activity-logs/webhook-batches/00000000-0000-4000-8000-000000000000/retry')->assertStatus(409);
    }

    public function test_removing_only_a_level_image_does_not_invalidate_unrelated_subresources(): void
    {
        $level = new Level(['id' => 12, 'name' => 'Diamond']);
        $image = new Image(['url' => 'diamond.png']);
        $image->setRelation('imageable', $level);
        $routes = app(RouteMapper::class)->map($image, 'deleted')['affected_routes'];
        $this->assertEqualsCanonicalizing(['/api/levels', '/api/levels/12'], $routes);
    }

    public function test_midnight_schedule_and_recovery_resume_only_authorized_cycles(): void
    {
        $this->change();
        Artisan::call('content-webhooks:dispatch');
        $cycle = DB::table('content_delivery_cycles')->first();
        $this->assertSame('scheduled', $cycle->trigger);
        $this->assertNull($cycle->actor);
        $events = collect(app(Schedule::class)->events());
        $midnight = $events->first(fn ($event) => str_contains($event->command ?? '', 'content-webhooks:dispatch'));
        $this->assertNotNull($midnight);
        $this->travelTo(Carbon::parse('2026-10-22T20:30:00Z'));
        $this->assertTrue($midnight->isDue($this->app));
        $this->travelTo(Carbon::parse('2026-10-22T20:29:00Z'));
        $this->assertFalse($midnight->isDue($this->app));
        $recovery = $events->first(fn ($event) => $event->description === 'recover-content-webhooks');
        Queue::fake();
        $recovery->run($this->app);
        Queue::assertPushed(DeliverContentWebhook::class, fn ($job) => $job->cycleId === $cycle->id);
        DB::table('content_delivery_state')->where('id', 1)->update(['active_cycle' => null]);
        Queue::fake();
        $recovery->run($this->app);
        Queue::assertNothingPushed();
    }

    public static function receiverFailures(): array
    {
        return ['bad bearer token' => ['success', 'wrong-secret', null, 401], 'unauthorized' => ['401', 'local-webhook-test-secret', null, 401],
            'validation' => ['422', 'local-webhook-test-secret', null, 422], 'conflict' => ['409', 'local-webhook-test-secret', null, 409],
            'request timeout' => ['408', 'local-webhook-test-secret', 10, 408], 'rate limit' => ['429', 'local-webhook-test-secret', 10, 429],
            'server error' => ['500', 'local-webhook-test-secret', 10, 500], 'unavailable' => ['503', 'local-webhook-test-secret', 10, 503],
            'HTML proxy error' => ['html', 'local-webhook-test-secret', 10, 502], 'invalid acknowledgement' => ['bad-ack', 'local-webhook-test-secret', null, 200],
            'connection reset' => ['disconnect', 'local-webhook-test-secret', 10, null]];
    }

    #[DataProvider('receiverFailures')]
    public function test_real_node_receiver_failure_matrix(string $mode, string $secret, ?int $delay, ?int $status): void
    {
        if (! getenv('CONTENT_WEBHOOK_NODE_RECEIVER')) {
            $this->markTestSkipped('Start scripts/webhook-receiver.cjs and set CONTENT_WEBHOOK_NODE_RECEIVER=1.');
        }
        Http::swap(new Factory);
        config(['content_webhooks.url' => 'http://127.0.0.1:4900/api/webhooks/cache-revalidate?failure='.$mode, 'content_webhooks.secret' => $secret]);
        $this->change();
        $service = app(DeliveryService::class);
        $cycle = $service->trigger();
        $service->deliver($cycle);
        $body = DB::table('content_webhook_batches')->value('payload');
        $this->assertSame($delay, $service->deliver($cycle));
        $batch = DB::table('content_webhook_batches')->first();
        $this->assertEquals($status, $batch->http_status);
        $this->assertSame($delay === null ? 'failed' : 'pending', $batch->status);
        $this->assertSame($body, $batch->payload);
        $this->assertEquals(1, DB::table('content_delivery_state')->where('id', 1)->value('next_patch'));
    }

    public function test_receiver_acceptance_followed_by_lost_acknowledgement_is_idempotent_on_retry(): void
    {
        if (! getenv('CONTENT_WEBHOOK_NODE_RECEIVER')) {
            $this->markTestSkipped('Requires local Node receiver.');
        }
        Http::swap(new Factory);
        config(['content_webhooks.url' => 'http://127.0.0.1:4900/api/webhooks/cache-revalidate?failure=accepted-timeout', 'content_webhooks.secret' => 'local-webhook-test-secret']);
        $this->change();
        $service = app(DeliveryService::class);
        $cycle = $service->trigger();
        $service->deliver($cycle);
        $batch = DB::table('content_webhook_batches')->first();
        $this->assertSame(10, $service->deliver($cycle));
        $this->assertEquals(1, DB::table('content_delivery_state')->where('id', 1)->value('next_patch'));
        config(['content_webhooks.url' => 'http://127.0.0.1:4900/api/webhooks/cache-revalidate']);
        $this->travel(11)->seconds();
        $this->assertSame(0, $service->deliver($cycle));
        $this->assertNull($service->deliver($cycle));
        $requests = collect(Http::get('http://127.0.0.1:4900/__test/requests')->json('requests'))->where('batch_id', $batch->id)->values();
        $this->assertCount(2, $requests);
        $this->assertSame($requests[0]['raw'], $requests[1]['raw']);
        $this->assertTrue($requests[1]['duplicate']);
        $this->assertEquals(2, DB::table('content_delivery_state')->where('id', 1)->value('next_patch'));
    }
}

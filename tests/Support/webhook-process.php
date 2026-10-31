<?php

use App\Services\ContentWebhooks\ChangeRecorder;
use App\Services\ContentWebhooks\DeliveryService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Isolated subprocess fixture. Never reads or writes the configured application DB.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = $argv[2] ?? '';
if (! str_starts_with(realpath($database) ?: '', realpath(sys_get_temp_dir())) || ! str_contains(basename($database), 'webhook-concurrency-')) {
    throw new RuntimeException('Only the isolated webhook test database is allowed.');
}
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $database,
    'database.connections.sqlite.busy_timeout' => 5000, 'content_webhooks.enabled' => true,
    'content_webhooks.url' => 'http://127.0.0.1:4900/api/webhooks/cache-revalidate?failure=slow',
    'content_webhooks.secret' => 'local-webhook-test-secret', 'logging.default' => 'stderr']);
DB::purge('sqlite');
DB::connection()->statement('PRAGMA busy_timeout=5000');
DB::connection()->statement('PRAGMA journal_mode=WAL');
$command = $argv[1];
if ($command === 'init') {
    Schema::create('activity_log', function ($t) {
        $t->id();
        $t->string('log_name')->nullable();
        $t->text('description');
        $t->string('event')->nullable();
        $t->nullableMorphs('subject');
        $t->nullableMorphs('causer');
        $t->json('properties')->nullable();
        $t->timestamps();
    });
    Schema::create('jobs', function ($t) {
        $t->id();
        $t->string('queue');
        $t->longText('payload');
        $t->unsignedTinyInteger('attempts');
        $t->unsignedInteger('reserved_at')->nullable();
        $t->unsignedInteger('available_at');
        $t->unsignedInteger('created_at');
    });
    (require database_path('migrations/2026_10_22_000001_create_content_webhook_tables.php'))->up();
    app(ChangeRecorder::class)->write(null, ['feature' => 'citizen_levels', 'category' => 'levels', 'entity_id' => '12',
        'resource' => 'record', 'event' => 'updated', 'action' => 'UPDATE', 'description' => 'Concurrent edit', 'affected_routes' => ['/api/levels/12']]);
    app(ChangeRecorder::class)->reconcile();
    echo json_encode(['initialized' => true]);
    exit;
}
$barrier = (float) ($argv[3] ?? 0);
while (microtime(true) < $barrier) {
    usleep(10000);
}
$service = app(DeliveryService::class);
if ($command === 'trigger') {
    echo json_encode(['cycle' => $service->trigger()]);
} elseif (in_array($command, ['deliver', 'crash-deliver'])) {
    if ($command === 'crash-deliver') {
        config(['content_webhooks.url' => 'http://127.0.0.1:4900/api/webhooks/cache-revalidate?failure=accepted-timeout']);
    }
    $cycle = DB::table('content_delivery_state')->where('id', 1)->value('active_cycle');
    echo json_encode(['delay' => $cycle ? $service->deliver($cycle) : null]);
} elseif ($command === 'state') {
    echo json_encode(['batches' => DB::table('content_webhook_batches')->get(),
        'cycles' => DB::table('content_delivery_cycles')->count(),
        'next_patch' => DB::table('content_delivery_state')->where('id', 1)->value('next_patch')]);
}

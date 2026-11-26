<?php

namespace Tests\Feature\ContentWebhooks;

use App\Http\Controllers\Api\V1\Translations\FieldController;
use App\Http\Controllers\Api\V1\Translations\TranslationController;
use App\Http\Middleware\CaptureContentChanges;
use App\Http\Requests\Translations\StoreFieldRequest;
use App\Http\Requests\Translations\UpdateFieldRequest;
use App\Models\Admin;
use App\Models\Translations\Field;
use App\Models\Translations\Translation;
use App\Services\ContentWebhooks\ChangeRecorder;
use App\Services\ContentWebhooks\DeliveryService;
use App\Services\ContentWebhooks\TranslationPublisher;
use App\Services\Translations\TranslationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class TranslationWebhookTest extends TestCase
{
    use CreatesAuthApiSchema;

    private string $testStorage;

    private string $originalStorage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalStorage = $this->app->storagePath();
        $this->testStorage = sys_get_temp_dir().'/webhook-translation-test-'.bin2hex(random_bytes(8));
        $this->app->useStoragePath($this->testStorage);
        $this->setUpAuthApiSchema();
        // Exercise a real separate connection: audit/delivery primary, source SQLite.
        config(['database.connections.primary' => config('database.connections.sqlite'), 'database.default' => 'primary']);
        $this->createActivityLogTable();
        (require database_path('migrations/2026_10_22_000001_create_content_webhook_tables.php'))->up();
        (require database_path('migrations/2026_11_05_000001_add_webhook_response_to_delivery_cycles.php'))->up();
        Schema::connection('sqlite')->create('translations', function (Blueprint $t) {
            $t->id();
            $t->string('code');
            $t->string('name');
            $t->boolean('status')->default(true);
            $t->unsignedInteger('version')->default(1);
            $t->string('file_url')->nullable();
        });
        Schema::connection('sqlite')->create('modals', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('translation_id');
            $t->string('name');
        });
        Schema::connection('sqlite')->create('tabs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('modal_id');
            $t->string('name');
        });
        Schema::connection('sqlite')->create('fields', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('tab_id');
            $t->unsignedBigInteger('unique_id')->nullable();
            $t->text('translation')->nullable();
        });
        (require database_path('migrations/2026_11_25_000001_create_translation_export_snapshots.php'))->up();
        foreach (['fa', 'en'] as $code) {
            $language = Translation::create(['code' => $code, 'name' => $code]);
            $language->modals()->create(['name' => 'Home'])->tabs()->create(['name' => 'Main']);
        }
    }

    protected function tearDown(): void
    {
        $this->app->useStoragePath($this->originalStorage);
        File::deleteDirectory($this->testStorage);
        parent::tearDown();
    }

    private function mutate(string $method, \Closure $callback, ?string $name = null): void
    {
        $request = Request::create('/api/translations/1/modals/1/tabs/1/fields', $method);
        if ($name !== null) {
            $request->setRouteResolver(fn () => app('router')->getRoutes()->getByName($name));
        }
        app(CaptureContentChanges::class)->handle($request, function () use ($callback) {
            $callback();

            return response()->json(['success' => true]);
        });
    }

    public function test_manual_field_mutations_are_logged_without_webhook_entries(): void
    {
        $language = Translation::first();
        $modal = $language->modals->first();
        $tab = $modal->tabs->first();
        $admin = (new Admin)->forceFill(['id' => 42, 'name' => 'Editor'])->setRelation('roles', collect());
        Auth::guard('admin')->setUser($admin);
        $controller = app(FieldController::class);
        $this->mutate('POST', fn () => $controller->store(
            StoreFieldRequest::create('/', 'POST', ['value' => 'سلام']), $language, $modal, $tab
        ), 'translations.fields.store');
        $field = $tab->fields()->firstOrFail();
        foreach (['درود', 'درود'] as $text) {
            $this->mutate('PATCH', fn () => $controller->update(
                UpdateFieldRequest::create('/', 'PATCH', ['translation' => $text]), $language, $modal, $tab, $field->fresh()
            ), 'translations.fields.update');
        }
        $this->mutate('DELETE', fn () => $controller->destroy($language, $modal, $tab, $field), 'translations.fields.destroy');
        $this->mutate('PATCH', fn () => app(TranslationService::class)->updateModal($modal, 'Renamed'));

        $entries = DB::table('activity_log')->orderBy('id')->get();
        $this->assertSame(['created', 'updated', 'deleted'], $entries->pluck('event')->all());
        foreach ($entries as $entry) {
            $this->assertEquals(42, $entry->causer_id);
            $data = json_decode($entry->properties, true);
            $this->assertEquals($field->unique_id, $data['attributes']['unique_id']);
            $this->assertSame('fa', $data['locale']);
            $this->assertSame([], $data['affected_routes']);
        }
        $this->assertSame(0, Field::count());
        $this->assertDatabaseCount('content_changes', 0);
        $this->assertDatabaseCount('content_change_outbox', 3, 'sqlite');
    }

    public function test_failed_translation_transaction_has_no_outbox_and_no_main_log(): void
    {
        $before = DB::table('activity_log')->count();
        $response = app(CaptureContentChanges::class)->handle(Request::create('/api/translations/1', 'PUT'), function () {
            Translation::first()->update(['name' => 'Rolled back']);

            return response()->json([], 422);
        });
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('fa', Translation::first()->name);
        $this->assertSame(0, DB::connection('sqlite')->table('content_change_outbox')->count());
        $this->assertSame($before, DB::table('activity_log')->count());
    }

    public function test_language_file_is_refreshed_atomically_without_touching_another_language(): void
    {
        $root = sys_get_temp_dir().'/webhook-language-'.bin2hex(random_bytes(5));
        $originalStorage = $this->app->storagePath();
        $this->app->useStoragePath($root);
        try {
            $language = Translation::first();
            $language->status = true;
            $language->save();
            $language->modals->first()->tabs->first()->fields()->create(['unique_id' => 1, 'translation' => 'سلام']);
            app(TranslationPublisher::class)->publish('fa');
            $this->assertSame(['1' => 'سلام'], json_decode(file_get_contents($root.'/app/lang/fa.json'), true));
            $this->assertFileDoesNotExist($root.'/app/lang/en.json');
        } finally {
            $this->app->useStoragePath($originalStorage);
            File::deleteDirectory($root);
        }
    }

    public function test_export_is_logged_once_per_success_and_delivery_does_not_rewrite_it(): void
    {
        $language = Translation::where('code', 'fa')->first();
        $tab = $language->modals->first()->tabs->first();
        $field = $tab->fields()->create(['unique_id' => 1, 'translation' => 'Exported value']);
        $this->export($language);
        $this->export($language);

        $this->assertDatabaseCount('activity_log', 2);
        $this->assertDatabaseCount('content_changes', 2);
        $data = json_decode(DB::table('content_changes')->orderByDesc('id')->first()->data, true);
        $this->assertSame('exported', $data['event']);
        $this->assertSame($language->version, $data['attributes']['version']);
        $this->assertSame(['/lang/fa.json'], $data['affected_routes']);
        $this->assertSame([], $data['unique_ids']);

        $field->update(['translation' => 'Unexported value']);
        config(['content_webhooks.enabled' => true, 'content_webhooks.url' => 'http://localhost/api/webhook', 'content_webhooks.secret' => 'test-secret']);
        Queue::fake();
        Http::fake(fn ($request) => Http::response(['batch_id' => $request['batch_id'], 'status' => 'accepted']));
        $service = app(DeliveryService::class);
        $cycle = $service->trigger();
        $service->deliver($cycle);
        $service->deliver($cycle);

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['changes'][0]['actions'] === ['CREATED']
            && ! array_key_exists('unique_ids', $request['changes'][0]));
        $this->assertDatabaseHas('content_webhook_batches', ['status' => 'delivered']);
        $this->assertSame(['1' => 'Exported value'], json_decode(File::get(storage_path('app/lang/fa.json')), true));
    }

    public function test_deleting_a_locale_supersedes_pending_exports_in_the_batch(): void
    {
        $language = Translation::where('code', 'en')->firstOrFail();
        $this->export($language);
        $language->modals->first()->tabs->first()->fields()->create(['unique_id' => 101, 'translation' => 'Added']);
        $this->export($language);

        $this->mutate('DELETE', fn () => app(TranslationController::class)->destroy($language), 'translations.destroy');

        $this->assertModelMissing($language);
        $this->assertDatabaseCount('content_changes', 3);
        config(['content_webhooks.enabled' => true, 'content_webhooks.url' => 'http://localhost/api/webhook', 'content_webhooks.secret' => 'test-secret']);
        Queue::fake();
        $service = app(DeliveryService::class);
        $service->deliver($service->trigger());

        $payload = json_decode(DB::table('content_webhook_batches')->value('payload'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(1, $payload['changes']);
        $change = $payload['changes'][0];
        $this->assertSame(['DELETED'], $change['actions']);
        $this->assertSame('en', $change['locale']);
        $this->assertSame(['/lang/en.json'], $change['affected_routes']);
        $this->assertArrayNotHasKey('unique_ids', $change);
    }

    /**
     * Track additions, edits, and deletions against the last manual export and merge IDs by locale.
     */
    public function test_english_exports_report_deduplicated_field_ids_in_a_mixed_batch(): void
    {
        $language = Translation::where('code', 'en')->firstOrFail();
        $tab = $language->modals->first()->tabs->first();
        $updated = $tab->fields()->create(['unique_id' => 101, 'translation' => 'Old']);
        $removed = $tab->fields()->create(['unique_id' => 205, 'translation' => null]);
        $tab->fields()->create(['unique_id' => 404, 'translation' => 'Unchanged']);
        $this->export($language);
        DB::table('content_changes')->delete();

        $updated->update(['translation' => 'New']);
        $removed->delete();
        $tab->fields()->create(['unique_id' => 319, 'translation' => 'Added']);
        app(TranslationPublisher::class)->publish('en');
        $this->export($language);

        $data = json_decode(DB::table('content_changes')->first()->data, true);
        $this->assertSame(['101', '205', '319'], $data['unique_ids']);
        $updated->update(['translation' => 'Latest']);
        $this->export($language);
        app(ChangeRecorder::class)->write(null, [
            'feature' => 'citizen_levels_details',
            'category' => 'citizen_levels',
            'entity_id' => '12',
            'resource' => 'gift',
            'event' => 'updated',
            'action' => 'UPDATE',
            'description' => 'Level gift updated.',
            'affected_routes' => ['/api/levels/12', '/api/levels/12/gift'],
        ]);

        config(['content_webhooks.enabled' => true, 'content_webhooks.url' => 'http://localhost/api/webhook', 'content_webhooks.secret' => 'test-secret']);
        Queue::fake();
        Http::fake(fn ($request) => Http::response(['batch_id' => $request['batch_id'], 'status' => 'accepted']));
        $service = app(DeliveryService::class);
        $cycle = $service->trigger();
        $service->deliver($cycle);
        $service->deliver($cycle);

        Http::assertSent(function ($request) use ($language) {
            $byFeature = collect($request['changes'])->keyBy('feature');
            $this->assertSame((string) $language->id, $byFeature['translations']['entity_id']);
            $this->assertSame('en', $byFeature['translations']['locale']);
            $this->assertSame(['101', '205', '319'], $byFeature['translations']['unique_ids']);
            $this->assertSame(['/lang/en.json'], $byFeature['translations']['affected_routes']);
            $this->assertSame('12', $byFeature['citizen_levels_details']['entity_id']);
            $this->assertArrayNotHasKey('unique_ids', $byFeature['citizen_levels_details']);

            return true;
        });
    }

    private function export(Translation $language): void
    {
        $request = Request::create('/api/translations/'.$language->id.'/export', 'POST');
        $request->setRouteResolver(fn () => app('router')->getRoutes()->getByName('translations.export'));
        $response = app(CaptureContentChanges::class)->handle($request, fn () => app(TranslationController::class)->export($language));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_failed_export_creates_no_activity_or_webhook_entry(): void
    {
        $language = Translation::first();
        $this->partialMock(Filesystem::class)
            ->shouldReceive('put')->once()->andReturn(false);

        try {
            $this->export($language);
            $this->fail('The export should fail when the file cannot be written.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Cannot write the translation dictionary.', $e->getMessage());
        }

        $this->assertDatabaseCount('activity_log', 0);
        $this->assertDatabaseCount('content_changes', 0);
        $this->assertDatabaseCount('content_change_outbox', 0, 'sqlite');
    }

    public function test_cleanup_requeues_unrelated_members_and_keeps_historical_activity(): void
    {
        $recorder = app(ChangeRecorder::class);
        $legacy = ['feature' => 'translations', 'category' => 'translations', 'event' => 'updated',
            'description' => 'Legacy translation edit', 'affected_routes' => ['/lang/fa.json']];
        $recorder->write('sqlite', $legacy);
        $recorder->reconcile();
        $this->assertDatabaseCount('activity_log', 1);
        $this->assertDatabaseCount('content_changes', 0);

        foreach (['delivered' => 1, 'failed' => 2, 'pending' => 3] as $status => $version) {
            DB::table('content_webhook_batches')->insert([
                'id' => $status, 'version' => 'V1.1.'.$version, 'status' => $status, 'payload' => '{}',
            ]);
        }
        $rows = [
            [1, 'delivered', $legacy],
            [2, 'failed', $legacy],
            [3, 'failed', [
                'feature' => 'videos', 'event' => 'updated', 'entity_id' => '7', 'resource' => 'record',
                'action' => 'UPDATE', 'modifier' => null, 'affected_routes' => ['/api/tutorials/example'],
            ]],
            [4, 'pending', $legacy],
            [5, null, $legacy],
        ];
        foreach ($rows as [$id, $batch, $data]) {
            DB::table('content_changes')->insert([
                'source_id' => (string) $id, 'activity_id' => $id, 'batch_id' => $batch,
                'data' => json_encode($data, JSON_THROW_ON_ERROR),
            ]);
        }
        DB::table('content_webhook_attempts')->insert(['batch_id' => 'failed', 'number' => 1]);

        $migration = require database_path('migrations/2026_11_24_000001_exclude_legacy_translation_webhook_changes.php');
        $migration->up();
        $migration->up();

        $this->assertDatabaseCount('content_changes', 2);
        $this->assertDatabaseHas('content_changes', ['activity_id' => 1, 'batch_id' => 'delivered']);
        $this->assertDatabaseHas('content_changes', ['activity_id' => 3, 'batch_id' => null]);
        $this->assertDatabaseHas('content_webhook_batches', ['id' => 'failed', 'status' => 'superseded']);
        $this->assertDatabaseHas('content_webhook_batches', ['id' => 'pending', 'status' => 'superseded']);
        $this->assertDatabaseHas('content_delivery_state', ['id' => 1, 'next_patch' => 4]);
        $this->assertDatabaseCount('activity_log', 1);
        $this->assertDatabaseCount('content_webhook_attempts', 1);

        config(['content_webhooks.enabled' => true, 'content_webhooks.url' => 'http://localhost/api/webhook', 'content_webhooks.secret' => 'test-secret']);
        Queue::fake();
        Http::fake(fn ($request) => Http::response(['batch_id' => $request['batch_id'], 'status' => 'accepted']));
        $delivery = app(DeliveryService::class);
        $cycle = $delivery->trigger();
        $delivery->deliver($cycle);
        $delivery->deliver($cycle);

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['version'] === 'V1.1.4'
            && array_column($request['changes'], 'feature') === ['videos']);
    }

    public function test_dictionary_is_written_before_real_receiver_acceptance(): void
    {
        if (! getenv('CONTENT_WEBHOOK_NODE_RECEIVER')) {
            $this->markTestSkipped('Requires local Node receiver.');
        }
        $root = sys_get_temp_dir().'/webhook-language-'.bin2hex(random_bytes(5));
        $originalStorage = $this->app->storagePath();
        $this->app->useStoragePath($root);
        try {
            $fa = Translation::where('code', 'fa')->first();
            $fa->update(['status' => true]);
            $this->mutate('POST', fn () => app(TranslationService::class)->createField($fa->modals->first()->tabs->first(), 'سلام'));
            $this->export($fa);
            config(['content_webhooks.enabled' => true, 'content_webhooks.url' => 'http://127.0.0.1:4900/api/webhooks/cache-revalidate', 'content_webhooks.secret' => 'local-webhook-test-secret']);
            Queue::fake();
            Http::globalRequestMiddleware(function ($request) use ($root) {
                $this->assertSame(['1' => 'سلام'], json_decode(file_get_contents($root.'/app/lang/fa.json'), true));
                $this->assertFileDoesNotExist($root.'/app/lang/en.json');

                return $request;
            });
            $service = app(DeliveryService::class);
            $cycle = $service->trigger();
            $service->deliver($cycle);
            $service->deliver($cycle);
            $batch = DB::table('content_webhook_batches')->first();
            $this->assertSame('delivered', $batch->status);
            $this->assertSame(['/lang/fa.json'], json_decode($batch->payload, true)['changes'][0]['affected_routes']);
        } finally {
            $this->app->useStoragePath($originalStorage);
            File::deleteDirectory($root);
        }
    }
}

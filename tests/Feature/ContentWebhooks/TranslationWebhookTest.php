<?php

namespace Tests\Feature\ContentWebhooks;

use App\Http\Middleware\CaptureContentChanges;
use App\Models\Translations\Field;
use App\Models\Translations\Translation;
use App\Services\ContentWebhooks\ChangeRecorder;
use App\Services\ContentWebhooks\DeliveryService;
use App\Services\ContentWebhooks\TranslationPublisher;
use App\Services\Translations\TranslationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
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

    private function mutate(string $method, \Closure $callback): void
    {
        app(CaptureContentChanges::class)->handle(Request::create('/api/translations/1/modals/1/tabs/1/fields', $method), function () use ($callback) {
            $callback();

            return response()->json(['success' => true]);
        });
    }

    public function test_new_persian_field_ignores_english_placeholder_and_projects_once(): void
    {
        $tab = Translation::where('code', 'fa')->first()->modals->first()->tabs->first();
        $this->mutate('POST', fn () => app(TranslationService::class)->createField($tab, 'سلام'));
        $changes = DB::table('content_changes')->get();
        $this->assertCount(1, $changes);
        $this->assertSame(['/lang/fa.json'], json_decode($changes[0]->data, true)['affected_routes']);
        $this->assertSame('CREATE', json_decode($changes[0]->data, true)['action']);
        $this->assertSame(2, DB::connection('sqlite')->table('fields')->count());
        DB::connection('sqlite')->table('content_change_outbox')->update(['projected_at' => null]);
        app(ChangeRecorder::class)->reconcile();
        $this->assertSame(1, DB::table('content_changes')->count());
    }

    public function test_shared_delete_notifies_all_populated_languages_and_structure_only_is_audit_only(): void
    {
        foreach (Translation::all() as $language) {
            $language->modals->first()->tabs->first()->fields()->create(['unique_id' => 1, 'translation' => $language->code]);
        }
        $field = Field::first();
        $this->mutate('DELETE', fn () => app(TranslationService::class)->deleteField($field));
        $routes = DB::table('content_changes')->get()->flatMap(fn ($c) => json_decode($c->data, true)['affected_routes'])->sort()->values()->all();
        $this->assertSame(['/lang/en.json', '/lang/fa.json'], $routes);
        $this->mutate('PUT', fn () => app(TranslationService::class)->updateModal(Translation::first()->modals->first(), 'Renamed'));
        $this->assertSame(2, DB::table('content_changes')->count());
        $this->assertSame(4, DB::table('content_change_receipts')->count());
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

    public function test_import_updates_only_selected_language_and_publication_failure_prevents_http(): void
    {
        $fa = Translation::where('code', 'fa')->first();
        $fa->modals->first()->tabs->first()->fields()->create(['unique_id' => 1, 'translation' => 'سلام']);
        $en = Translation::where('code', 'en')->first();
        app(CaptureContentChanges::class)->handle(Request::create('/api/translations/'.$en->id.'/import', 'POST'), function () use ($en) {
            app(TranslationService::class)->importTranslation($en, [1 => 'Hello']);

            return response()->json(['success' => true]);
        });
        $data = json_decode(DB::table('content_changes')->sole()->data, true);
        $this->assertSame('en', $data['locale']);
        $this->assertSame('UPDATE', $data['action']);
        $this->assertSame(['/lang/en.json'], $data['affected_routes']);
        config(['content_webhooks.enabled' => true, 'content_webhooks.url' => 'http://127.0.0.1:4900/api/webhooks/cache-revalidate', 'content_webhooks.secret' => 'local-webhook-test-secret']);
        Queue::fake();
        Http::fake();
        $service = app(DeliveryService::class);
        $cycle = $service->trigger();
        $service->deliver($cycle);
        $this->mock(TranslationPublisher::class)->shouldReceive('publish')->once()->with('en')->andThrow(new \RuntimeException('Test filesystem failure'));
        $this->assertSame(10, $service->deliver($cycle));
        Http::assertNothingSent();
        $this->assertSame('pending', DB::table('content_webhook_batches')->value('status'));
        $this->assertEquals(1, DB::table('content_delivery_state')->where('id', 1)->value('next_patch'));
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
            $this->assertSame(['/lang/fa.json'], json_decode($batch->payload, true)['affected_routes']);
        } finally {
            $this->app->useStoragePath($originalStorage);
            File::deleteDirectory($root);
        }
    }
}

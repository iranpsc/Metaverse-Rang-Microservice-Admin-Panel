<?php

namespace Tests\Feature\Translations;

use App\Models\Translations\AutoTranslationRun;
use App\Models\Translations\Field;
use App\Models\Translations\Translation;
use App\Services\ContentWebhooks\ChangeRecorder;
use App\Services\Translations\AutoTranslationService;
use App\Services\Translations\CloudflareRequestGate;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class AutoTranslationApiTest extends TestCase
{
    use ActsAsSuperAdmin, CreatesAuthApiSchema;

    private string $storage;

    private Translation $persian;

    private Translation $english;

    private Translation $german;

    /**
     * Prepare isolated SQLite, queue, and filesystem fixtures.
     *
     * @return void Initializes fixtures for the current test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = sys_get_temp_dir().'/auto-translation-'.bin2hex(random_bytes(8));
        $this->app->useStoragePath($this->storage);
        config(['services.openai.key' => 'test-key']);
        Http::preventStrayRequests();
        $this->setUpAuthApiSchema();
        $this->createActivityLogTable();

        foreach (['translations', 'modals', 'tabs', 'fields'] as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->id();
                match ($table->getTable()) {
                    'translations' => $this->translationColumns($table),
                    'modals' => [$table->foreignId('translation_id')->constrained()->cascadeOnDelete(), $table->string('name')],
                    'tabs' => [$table->foreignId('modal_id')->constrained()->cascadeOnDelete(), $table->string('name')],
                    'fields' => [
                        $table->foreignId('tab_id')->constrained()->cascadeOnDelete(),
                        $table->unsignedBigInteger('unique_id')->nullable(),
                        $table->text('translation')->nullable(),
                    ],
                };
            });
        }

        (require database_path('migrations/2026_10_22_000001_create_content_webhook_tables.php'))->up();
        (require database_path('migrations/2026_11_18_000001_create_auto_translation_queue.php'))->up();
        (require database_path('migrations/2026_11_18_000002_add_error_to_auto_translation_runs.php'))->up();
        (require database_path('migrations/2026_11_21_000001_add_provider_to_auto_translation_runs.php'))->up();

        $this->persian = Translation::create(['code' => 'fa', 'name' => 'Persian']);
        $this->english = Translation::create(['code' => 'en', 'name' => 'English']);
        $this->german = Translation::create(['code' => 'de', 'name' => 'German']);
    }

    /**
     * Restore the test clock and remove the generated dictionaries.
     *
     * @return void Runs PHPUnit's parent teardown after filesystem cleanup.
     */
    protected function tearDown(): void
    {
        $this->travelBack();
        File::deleteDirectory($this->storage);
        parent::tearDown();
    }

    /**
     * Check that the translation routes enforce admin authorization.
     *
     * @return void Checks HTTP 401 for guests and HTTP 403 for admins without the required role.
     */
    public function test_routes_require_translation_management_access(): void
    {
        $this->postJson($this->url($this->german))->assertUnauthorized();
        $this->getJson($this->url($this->german))->assertUnauthorized();
        $this->getJson('/api/translations/auto-translate/runs')->assertUnauthorized();
        $this->getJson($this->url($this->german).'/1')->assertUnauthorized();
        $this->deleteJson($this->url($this->german).'/1')->assertUnauthorized();

        $this->actingAsRegularAdmin();
        $this->postJson($this->url($this->german))->assertForbidden();
        $this->getJson($this->url($this->german))->assertForbidden();
        $this->getJson('/api/translations/auto-translate/runs')->assertForbidden();
        $this->getJson($this->url($this->german).'/1')->assertForbidden();
        $this->deleteJson($this->url($this->german).'/1')->assertForbidden();
    }

    /**
     * Check that an authorized admin reuses an active run and can only fetch it through its locale.
     *
     * @return void Checks an HTTP 202 start response and the locale-scoped status lookup.
     */
    public function test_translation_manager_can_start_one_run_and_status_is_scoped_to_the_locale(): void
    {
        $this->actingAsAdminWithRole('translations-management');
        $this->getJson($this->url($this->german))->assertOk()->assertJsonPath('data', null);
        $run = $this->start($this->german);
        $this->getJson($this->url($this->german))->assertOk()->assertJsonPath('data.id', $run);
        $this->getJson($this->url($this->english).'/'.$run)->assertNotFound();
        $this->getJson($this->url($this->german).'/'.$run)->assertOk()->assertJsonPath('data.status', 'queued');
        $this->getJson('/api/translations/auto-translate/runs')->assertOk()->assertJsonPath('data.0.id', $run);
        $this->postJson($this->url($this->german))->assertAccepted()->assertJsonPath('data.id', $run);
        $this->assertSame(1, Queue::connection('auto-translations')->size());
    }

    /**
     * Ensure deployments with pre-existing SQLite queue tables can record their missing migration history safely.
     *
     * @return void Verifies repeated migration execution preserves the queue schema without duplicate-table errors.
     */
    public function test_auto_translation_migrations_are_safe_for_existing_sqlite_tables(): void
    {
        (require database_path('migrations/2026_11_18_000001_create_auto_translation_queue.php'))->up();
        (require database_path('migrations/2026_11_18_000002_add_error_to_auto_translation_runs.php'))->up();
        (require database_path('migrations/2026_11_21_000001_add_provider_to_auto_translation_runs.php'))->up();

        $schema = Schema::connection('sqlite');
        $this->assertTrue($schema->hasTable('auto_translation_runs'));
        $this->assertTrue($schema->hasTable('auto_translation_jobs'));
        $this->assertTrue($schema->hasColumn('auto_translation_runs', 'error'));
        $this->assertTrue($schema->hasColumn('auto_translation_runs', 'provider'));
    }

    /**
     * Ensure an admin can stop only an active, locale-scoped translation queue.
     *
     * @return void Verifies cancellation persistence, queue-list removal, and safe no-op processing.
     */
    public function test_translation_manager_can_cancel_an_active_run(): void
    {
        $this->actingAsAdminWithRole('translations-management');
        $run = $this->start($this->german);

        $this->deleteJson($this->url($this->english).'/'.$run)->assertNotFound();
        $this->deleteJson($this->url($this->german).'/'.$run)
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.error', 'فرایند ترجمه خودکار توسط مدیر متوقف شد.');

        $this->assertDatabaseHas('auto_translation_runs', [
            'id' => $run,
            'status' => 'cancelled',
            'active' => null,
        ], 'sqlite');
        $this->getJson('/api/translations/auto-translate/runs')->assertJsonCount(0, 'data');
        $this->work();
        $this->assertRun($this->german, $run, 'cancelled');
    }

    /**
     * Ensure a Cloudflare-selected run uses the Workers AI response contract and persists its provider.
     *
     * @return void Verifies request parameters, translation progress, and the completed run.
     */
    public function test_cloudflare_provider_translates_and_persists_provider(): void
    {
        $this->actingAsAdminWithRole('translations-management');
        config([
            'services.cloudflare.account_id' => 'account-id',
            'services.cloudflare.api_token' => 'cloudflare-test-token',
        ]);
        $this->field($this->english, 1, '<b>Hello {{name}}</b> MetaRang');
        Http::fake([
            'api.cloudflare.com/*' => function (Request $request) {
                $this->assertSame('Hello', $request['text']);
                $this->assertSame('en', $request['source_lang']);
                $this->assertSame('de', $request['target_lang']);

                return Http::response(['result' => ['translated_text' => 'Hallo']]);
            },
        ]);

        $run = $this->postJson($this->url($this->german), ['provider' => 'cloudflare'])
            ->assertAccepted()
            ->assertJsonPath('data.provider', 'cloudflare')
            ->json('data.id');
        $this->work(3);

        $this->assertRun($this->german, $run, 'completed', 1, 1, 1);
        $this->getJson($this->url($this->german))->assertJsonPath('data.provider', 'cloudflare');
        $this->assertSame('<b>Hallo {{name}}</b> MetaRang', Field::query()->whereHas('tab.modal', fn ($query) => $query->where('translation_id', $this->german->id))->value('translation'));
        Http::assertSentCount(1);
    }

    /**
     * Stop Cloudflare requests after a rate limit while preserving earlier completed fields.
     *
     * @return void Verifies persisted progress and the absence of requests after HTTP 429.
     */
    public function test_cloudflare_rate_limit_stops_the_batch_and_commits_completed_fields(): void
    {
        $this->actingAsAdminWithRole('translations-management');
        config([
            'services.cloudflare.account_id' => 'account-id',
            'services.cloudflare.api_token' => 'cloudflare-test-token',
            'services.cloudflare.translation_batch_size' => 5,
        ]);
        foreach (range(1, 3) as $id) {
            $this->field($this->english, $id, "Text {$id} مرکز کنترل");
        }

        $requests = 0;
        Http::fake(function () use (&$requests) {
            $requests++;

            return $requests === 1
                ? Http::response(['result' => ['translated_text' => 'ترجمه اول']])
                : Http::response(['errors' => [['message' => 'Rate limit reached.']]], 429);
        });

        $run = $this->postJson($this->url($this->german), ['provider' => 'cloudflare'])->assertAccepted()->json('data.id');
        $this->work(2);

        $this->assertRun($this->german, $run, 'failed', 3, 1, 1);
        $this->getJson($this->url($this->german))->assertJsonPath('data.error', 'Cloudflare Workers AI (HTTP 429): Rate limit reached.');
        $this->assertSame('ترجمه اول Control Center', Field::query()->whereHas('tab.modal', fn ($query) => $query->where('translation_id', $this->german->id))->where('unique_id', 1)->value('translation'));
        $this->assertNull(Field::query()->whereHas('tab.modal', fn ($query) => $query->where('translation_id', $this->german->id))->where('unique_id', 2)->value('translation'));
        $this->assertSame(2, $requests);
    }

    /**
     * Persist completed fields on quota exhaustion and resume only after midnight UTC.
     */
    public function test_cloudflare_daily_quota_pauses_all_runs_for_the_account(): void
    {
        $this->travelTo(now('UTC')->startOfDay()->addHours(23)->addMinutes(59));
        $this->actingAsAdminWithRole('translations-management');
        config(['services.cloudflare.account_id' => 'account', 'services.cloudflare.api_token' => 'token']);
        $this->field($this->english, 1, 'First');
        $this->field($this->english, 2, 'Second');
        Http::fakeSequence()
            ->push(['result' => ['translated_text' => 'Erste']])
            ->push(['errors' => [['code' => 3036, 'message' => 'Daily quota exhausted.']]], 429)
            ->push(['result' => ['translated_text' => 'Zweite']])
            ->push(['result' => ['translated_text' => 'اول']])
            ->push(['result' => ['translated_text' => 'دوم']]);

        $run = $this->postJson($this->url($this->german), ['provider' => 'cloudflare'])->assertAccepted()->json('data.id');
        $this->work(2);
        $this->assertRun($this->german, $run, 'translating', 2, 1, 1);
        $this->getJson($this->url($this->german))
            ->assertJsonPath('data.error', 'Cloudflare Workers AI (HTTP 429, code 3036): Daily quota exhausted.');

        $otherRun = $this->postJson($this->url($this->persian), ['provider' => 'cloudflare'])->assertAccepted()->json('data.id');
        $this->work(2);
        $this->assertRun($this->persian, $otherRun, 'translating', 2);
        $this->travel(59)->seconds();
        $this->work();
        Http::assertSentCount(2);

        $this->travel(1)->seconds();
        $this->work(4);
        $this->assertRun($this->german, $run, 'completed', 2, 2, 2);
        $this->assertRun($this->persian, $otherRun, 'completed', 2, 2, 2);
        Http::assertSentCount(5);
    }

    /**
     * Honor the provider delay and end a run after two capacity retries.
     */
    public function test_cloudflare_capacity_retries_are_delayed_and_bounded(): void
    {
        $this->freezeSecond();
        $this->actingAsAdminWithRole('translations-management');
        config(['services.cloudflare.account_id' => 'account', 'services.cloudflare.api_token' => 'token']);
        $this->field($this->english, 1, 'First');
        Http::fake([
            '*' => Http::response(['errors' => [['code' => 3040, 'message' => 'Busy.']]], 429, ['Retry-After' => '12']),
        ]);

        $run = $this->postJson($this->url($this->german), ['provider' => 'cloudflare'])->assertAccepted()->json('data.id');
        $this->work(2);
        $this->travel(11)->seconds();
        $this->work();
        Http::assertSentCount(1);

        $this->travel(1)->seconds();
        $this->work();
        Http::assertSentCount(2);
        $this->travel(12)->seconds();
        $this->work(2);
        Http::assertSentCount(3);
        $this->assertRun($this->german, $run, 'failed', 1);
        $this->getJson($this->url($this->german))
            ->assertJsonPath('data.error', 'Cloudflare Workers AI (HTTP 429, code 3040): Busy.');
    }

    /**
     * Pace actual requests and resume a field split by a protected token.
     */
    public function test_cloudflare_request_budget_preserves_translated_segments_across_queue_steps(): void
    {
        $this->freezeSecond();
        $this->actingAsAdminWithRole('translations-management');
        config([
            'services.cloudflare.account_id' => 'account',
            'services.cloudflare.api_token' => 'token',
            'services.cloudflare.translation_requests_per_minute' => 1,
        ]);
        $this->field($this->english, 1, 'Hello {{name}} welcome');
        Http::fakeSequence()->push(['result' => ['translated_text' => 'Hallo']])
            ->push(['result' => ['translated_text' => 'willkommen']]);

        $run = $this->postJson($this->url($this->german), ['provider' => 'cloudflare'])->assertAccepted()->json('data.id');
        $this->work(2);
        $this->assertRun($this->german, $run, 'translating', 1);
        Http::assertSentCount(1);

        $this->travel(60)->seconds();
        $this->work(2);
        Http::assertSentCount(2);
        $this->assertRun($this->german, $run, 'completed', 1, 1, 1);
        $this->assertSame('Hallo {{name}} willkommen', Field::query()
            ->whereHas('tab.modal', fn ($query) => $query->where('translation_id', $this->german->id))->value('translation'));
    }

    /**
     * Checkpoint completed fields and segments before the worker can time out.
     */
    public function test_cloudflare_slow_requests_checkpoint_before_the_queue_timeout(): void
    {
        $this->freezeSecond();
        $this->actingAsAdminWithRole('translations-management');
        config(['services.cloudflare.account_id' => 'account', 'services.cloudflare.api_token' => 'token']);
        $this->field($this->english, 1, 'First');
        $this->field($this->english, 2, 'One {{x}} two {{y}} three {{z}} four');
        Http::fake(function (Request $request) {
            $this->travel(14)->seconds();

            return Http::response(['result' => ['translated_text' => ucfirst($request['text'])]]);
        });

        $run = $this->postJson($this->url($this->german), ['provider' => 'cloudflare'])->assertAccepted()->json('data.id');
        $this->work(2);
        Http::assertSentCount(4);
        $this->assertRun($this->german, $run, 'translating', 2, 1, 1);

        $this->travel(1)->seconds();
        $this->work(2);
        Http::assertSentCount(5);
        $this->assertRun($this->german, $run, 'completed', 2, 2, 2);
        $this->assertSame('One {{x}} Two {{y}} Three {{z}} Four', Field::query()
            ->whereHas('tab.modal', fn ($query) => $query->where('translation_id', $this->german->id))
            ->where('unique_id', 2)->value('translation'));
    }

    /**
     * Preserve the provider diagnostic and completed fields when the cooldown lock cannot be acquired.
     */
    public function test_cloudflare_checkpoints_when_the_cooldown_lock_times_out(): void
    {
        $this->freezeSecond();
        $this->actingAsAdminWithRole('translations-management');
        config(['services.cloudflare.account_id' => 'account', 'services.cloudflare.api_token' => 'token']);
        $this->partialMock(CloudflareRequestGate::class)
            ->shouldReceive('pause')->once()->andThrow(new LockTimeoutException);
        $this->field($this->english, 1, 'First');
        $this->field($this->english, 2, 'Second');
        Http::fakeSequence()
            ->push(['result' => ['translated_text' => 'Erste']])
            ->push(['errors' => [['code' => 3040, 'message' => 'Busy.']]], 429, ['Retry-After' => '12'])
            ->push(['result' => ['translated_text' => 'Zweite']]);

        $run = $this->postJson($this->url($this->german), ['provider' => 'cloudflare'])->assertAccepted()->json('data.id');
        $this->work(2);
        $this->assertRun($this->german, $run, 'translating', 2, 1, 1);
        $this->getJson($this->url($this->german))
            ->assertJsonPath('data.error', 'Cloudflare Workers AI (HTTP 429, code 3040): Busy.');

        $this->travel(12)->seconds();
        $this->work(2);
        Http::assertSentCount(3);
        $this->assertRun($this->german, $run, 'completed', 2, 2, 2);
    }

    /**
     * Check that a missing API key leaves no queued work.
     *
     * @return void Checks HTTP 503 and empty run and queue tables.
     */
    public function test_missing_configuration_leaves_no_run_or_job(): void
    {
        $this->actingAsSuperAdmin();
        config(['services.openai.key' => null]);

        $this->postJson($this->url($this->german))->assertServiceUnavailable();
        $this->assertSame(0, AutoTranslationRun::query()->count());
        $this->assertSame(0, Queue::connection('auto-translations')->size());
    }

    /**
     * Check that a missing Cloudflare credential leaves no queued work.
     *
     * @return void Checks HTTP 503 and empty run and queue tables for the selected provider.
     */
    public function test_missing_cloudflare_configuration_leaves_no_run_or_job(): void
    {
        $this->actingAsSuperAdmin();
        config(['services.cloudflare.account_id' => null, 'services.cloudflare.api_token' => null]);

        $this->postJson($this->url($this->german), ['provider' => 'cloudflare'])->assertServiceUnavailable();
        $this->assertSame(0, AutoTranslationRun::query()->count());
        $this->assertSame(0, Queue::connection('auto-translations')->size());
    }

    /**
     * Check source precedence, protection of existing values, and the published dictionary.
     *
     * @return void Verifies that SQLite and the served JSON contain the expected text.
     */
    public function test_persian_precedence_fallback_and_blank_only_persistence_publish_one_json_file(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $this->field($this->persian, 1, 'ورود به MetaRang {{name}}');
        $this->field($this->english, 1, 'Alternate source');
        $this->field($this->english, 2, 'Enter email', 'Form', 'Email');
        $this->field($this->english, 3, 'Leave this alone');
        $this->field($this->english, 4, '');
        $this->field($this->persian, null, 'Unkeyed source');
        $blank = $this->field($this->german, 1, '');
        $duplicate = $this->field($this->german, 1, null, 'Mirror');
        $skipped = $this->field($this->german, 3, '', 'Mirror');
        $existing = $this->field($this->german, 3, 'Von Hand');
        Http::fake(function (Request $request) {
            $this->assertSame('https://api.openai.com/v1/responses', $request->url());
            $this->assertTrue($request->hasHeader('Authorization', 'Bearer test-key'));
            $this->assertTrue($request['text']['format']['strict']);
            $this->assertFalse($request['store']);
            $input = json_decode($request['input'], true);
            $this->assertSame('de', $input['destination']['code']);
            $this->assertSame(['fa', 'en'], array_column($input['strings'], 'source'));
            $this->assertSame(['1', '2'], array_column($input['strings'], 'id'));
            $this->assertSame('Form', $input['strings'][1]['modal']);
            $this->assertSame('ورود به {{metarang_glossary_0}} {{name}}', $input['strings'][0]['text']);

            return $this->providerResponse([
                ['id' => '2', 'translation' => 'E-Mail eingeben'],
                ['id' => '1', 'translation' => 'Bei {{metarang_glossary_0}} anmelden {{name}}'],
            ]);
        });

        $run = $this->start($this->german);
        $this->work();
        $this->assertRun($this->german, $run, 'translating', total: 2);
        app(AutoTranslationService::class)->process($run, 0);
        $this->work();
        $this->assertRun($this->german, $run, 'publishing', 2, 2, 2);
        $this->get('/lang/de.json')->assertNotFound();
        $this->work();

        $this->assertRun($this->german, $run, 'completed', 2, 2, 2);
        $this->getJson('/api/translations/auto-translate/runs')->assertJsonCount(0, 'data');
        $this->assertSame('Bei MetaRang anmelden {{name}}', $blank->fresh()->translation);
        $this->assertSame('Bei MetaRang anmelden {{name}}', $duplicate->fresh()->translation);
        $this->assertSame('Von Hand', $existing->fresh()->translation);
        $this->assertSame('', $skipped->fresh()->translation);
        $this->assertSame('E-Mail eingeben', $this->german->modals()->where('name', 'Form')
            ->firstOrFail()->tabs()->where('name', 'Email')->firstOrFail()->fields()->value('translation'));
        $this->get('/lang/de.json')->assertOk();
        $this->assertSame([
            1 => 'Bei MetaRang anmelden {{name}}',
            2 => 'E-Mail eingeben',
            3 => 'Von Hand',
        ], json_decode(File::get(storage_path('app/lang/de.json')), true));
        $this->assertSame(2, $this->german->fresh()->version);
        $this->assertStringEndsWith('/lang/de.json', $this->german->fresh()->file_url);
        $this->assertDatabaseMissing('activity_log', ['log_name' => 'translations']);
        $this->assertDatabaseCount('content_changes', 0);
        Http::assertSentCount(1);
    }

    /**
     * Check English-to-Persian translation while source and destination values change.
     *
     * @return void Checks that English is used and newer edits are preserved.
     */
    public function test_persian_destination_uses_english_and_skips_manual_edits_and_stale_sources(): void
    {
        $this->actingAsSuperAdmin();
        $this->field($this->english, 1822, 'Save');
        $this->field($this->english, 1823, 'Load');
        $changedSource = $this->field($this->english, 1824, 'Old copy');
        $deletedSource = $this->field($this->english, 1825, 'Delete me');
        $manual = $this->field($this->persian, 1822, null);
        Cache::put('translations.persian_hierarchy_map', [1 => 'stale']);
        Http::fake(function (Request $request) use ($manual, $changedSource, $deletedSource) {
            $input = json_decode($request['input'], true);
            $this->assertSame(['en', 'en', 'en', 'en'], array_column($input['strings'], 'source'));
            $manual->update(['translation' => 'ذخیره دستی']);
            $changedSource->update(['translation' => 'New copy']);
            $deletedSource->delete();

            return $this->translateRequest($request, 'ترجمه آزمایشی');
        });

        $run = $this->start($this->persian);
        $this->work(3);

        $this->assertRun($this->persian, $run, 'completed', 4, 4, 1);
        $this->assertNull(Cache::get('translations.persian_hierarchy_map'));
        $this->assertSame([
            1822 => 'ذخیره دستی',
            1823 => 'ترجمه آزمایشی',
        ], json_decode(File::get(storage_path('app/lang/fa.json')), true));
    }

    /**
     * Check that a large OpenAI run checkpoints batches and retries a rate-limited request.
     *
     * @return void Verifies progress before and after the queued retry.
     */
    public function test_large_run_checkpoints_batches_and_retries_a_rate_limit_before_saving(): void
    {
        $this->actingAsSuperAdmin();
        foreach (range(1, 52) as $id) {
            $this->field($this->english, $id, "English string {$id}");
        }

        $batchSizes = [];
        Http::fake(function (Request $request) use (&$batchSizes) {
            $batchSizes[] = count(json_decode($request['input'], true)['strings']);

            return count($batchSizes) === 2
                ? Http::response(['error' => ['message' => 'Rate limit reached.']], 429)
                : $this->translateRequest($request);
        });

        $run = $this->start($this->german);
        $this->work(2);
        $this->assertRun($this->german, $run, 'translating', 52, 20, 20);
        $this->work();
        $this->assertRun($this->german, $run, 'translating', 52, 20, 20);
        $this->getJson($this->url($this->german))->assertJsonPath('data.error', 'OpenAI (HTTP 429): Rate limit reached.');
        $this->assertSame(1, $this->german->modals()->count());

        $this->travel(11)->seconds();
        $this->work(3);
        $this->assertRun($this->german, $run, 'completed', 52, 52, 52);
        $this->getJson($this->url($this->german))->assertJsonPath('data.error', null);
        $this->assertSame([20, 20, 20, 12], $batchSizes);
    }

    /**
     * Check that empty runs avoid the provider and disabled locales stay unpublished.
     *
     * @return void Verifies an empty run makes no HTTP request and a disabled locale has no public JSON.
     */
    public function test_empty_run_skips_openai_and_disabled_language_remains_unpublished(): void
    {
        $this->actingAsSuperAdmin();
        $empty = $this->start($this->german);
        $this->work();
        $this->assertRun($this->german, $empty, 'completed');
        Http::assertNothingSent();

        $this->german->update(['status' => false]);
        $this->field($this->english, 1, 'Save');
        Http::fake(fn (Request $request) => $this->translateRequest($request));
        $run = $this->start($this->german);
        $this->work(3);

        $this->assertRun($this->german, $run, 'completed', 1, 1, 1);
        $this->assertSame(0, $this->german->fresh()->status);
        $this->assertSame(1, $this->german->fresh()->version);
        $this->get('/lang/de.json')->assertNotFound();
    }

    /**
     * Check the character limit and preserve values filled while the job runs.
     *
     * @return void Verifies that changes made during batching take precedence at save time.
     */
    public function test_character_limit_splits_long_strings_and_skips_keys_filled_during_the_run(): void
    {
        $this->actingAsSuperAdmin();
        $this->field($this->english, 1, str_repeat('A', 7000));
        $this->field($this->english, 2, str_repeat('B', 7000));
        Http::fake(fn (Request $request) => $this->translateRequest($request));

        $run = $this->start($this->german);
        $this->work(2);
        $this->assertRun($this->german, $run, 'translating', 2, 1, 1);
        $this->field($this->german, 1, 'Von Hand');
        $this->field($this->german, 2, 'Auch von Hand');
        $this->work(2);

        $this->assertRun($this->german, $run, 'completed', 2, 2, 1);
        $this->assertDatabaseCount('content_change_outbox', 0);
        $this->get('/lang/de.json')->assertOk();
        Http::assertSentCount(2);
    }

    /**
     * Check that provider rejection and malformed output leave the destination untouched.
     *
     * @param  array<string, mixed>  $body  Mocked provider JSON body.
     * @param  int  $status  Mocked provider HTTP status.
     * @return void Verifies a failed run leaves the destination and job queue empty.
     */
    #[DataProvider('invalidProviderResponses')]
    public function test_invalid_provider_output_fails_without_applying_translations(array $body, int $status): void
    {
        $this->actingAsSuperAdmin();
        $this->field($this->english, 1, 'Save MetaRang {{name}}');
        $this->field($this->english, 2, 'Open');
        Http::fake(fn () => Http::response($body, $status));

        $run = $this->start($this->german);
        $this->work(2);

        $this->assertRun($this->german, $run, 'failed', 2);
        $this->assertSame(0, $this->german->modals()->count());
        $this->assertSame(0, Queue::connection('auto-translations')->size());
        $this->get('/lang/de.json')->assertNotFound();
    }

    /**
     * Provide provider errors and response bodies that fail validation.
     *
     * @return iterable<string, array{array<string, mixed>, int}> Named fixtures with a response body and HTTP status.
     */
    public static function invalidProviderResponses(): iterable
    {
        yield 'unauthorized' => [[], 401];
        yield 'incomplete output' => [['status' => 'incomplete', 'output' => []], 200];

        $validFirst = ['id' => '1', 'translation' => '{{metarang_glossary_0}} {{name}} speichern'];
        $variants = [
            'duplicate ID' => [$validFirst, $validFirst],
            'missing ID' => [$validFirst],
            'extra ID' => [$validFirst, ['id' => '3', 'translation' => 'Öffnen']],
            'blank translation' => [$validFirst, ['id' => '2', 'translation' => ' ']],
            'changed placeholder' => [['id' => '1', 'translation' => '{{metarang_glossary_0}} {other} speichern']],
            'malformed output' => null,
        ];
        foreach ($variants as $name => $results) {
            yield $name => [
                [
                    'status' => 'completed',
                    'output' => [['type' => 'message', 'content' => [[
                        'type' => 'output_text',
                        'text' => json_encode(['translations' => $results], JSON_THROW_ON_ERROR),
                    ]]]],
                ],
                200,
            ];
        }
    }

    /**
     * Check that retrying a file write reuses saved translations and advances the version once.
     *
     * @return void Verifies recovery from the saved publication state.
     */
    public function test_publication_can_be_retried_without_calling_openai_or_saving_twice(): void
    {
        $this->actingAsSuperAdmin();
        $this->field($this->english, 1, 'Save');
        Http::fake(fn (Request $request) => $this->translateRequest($request));
        $run = $this->start($this->german);
        $this->work(2);

        $filesystem = File::getFacadeRoot();
        File::partialMock()->shouldReceive('replace')->times(3)->andThrow(new RuntimeException('Filesystem failure'));
        $this->work();
        $this->travel(11)->seconds();
        $this->work();
        $this->travel(31)->seconds();
        $this->work();
        File::swap($filesystem);

        $this->assertRun($this->german, $run, 'publication_failed', 1, 1, 1);
        $this->assertSame(1, $this->german->fresh()->version);
        $this->assertSame(1, $this->german->modals()->count());
        $this->assertDatabaseCount('content_change_outbox', 0);

        config(['services.openai.key' => null]);
        $this->postJson($this->url($this->german))->assertAccepted()
            ->assertJsonPath('data.id', $run)->assertJsonPath('data.status', 'publishing');
        $this->work();

        $this->assertRun($this->german, $run, 'completed', 1, 1, 1);
        $this->assertSame(2, $this->german->fresh()->version);
        $this->assertDatabaseCount('content_changes', 0);
        Http::assertSentCount(1);
    }

    /**
     * Check that manual export remains successful when immediate projection fails.
     *
     * @return void Verifies the export can be projected later with the admin attribution.
     */
    public function test_manual_export_succeeds_when_audit_projection_needs_to_be_retried(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $this->field($this->english, 1, 'Save');
        Http::fake(fn (Request $request) => $this->translateRequest($request));
        $run = $this->start($this->german);
        $this->work(3);

        $recorder = app(ChangeRecorder::class);
        $this->partialMock(ChangeRecorder::class)->shouldReceive('reconcile')->once()
            ->andThrow(new RuntimeException('Projection unavailable'));
        $this->post('/api/translations/'.$this->german->id.'/export')->assertOk();

        $this->assertRun($this->german, $run, 'completed', 1, 1, 1);
        $this->get('/lang/de.json')->assertOk();
        $this->assertDatabaseCount('content_changes', 0);
        $recorder->reconcile();
        $this->assertDatabaseCount('content_changes', 1);
        $this->assertDatabaseHas('activity_log', ['causer_id' => $admin->id, 'log_name' => 'translations', 'event' => 'exported']);
    }

    /**
     * Keep an OpenAI failure visible when the admin reopens the dialog.
     *
     * @return void Verifies the provider message survives and credentials are redacted.
     */
    public function test_latest_run_exposes_redacted_provider_error_without_retrying_quota_errors(): void
    {
        $this->actingAsSuperAdmin();
        $this->field($this->english, 1, 'Save');
        Http::fake(fn () => Http::response(['error' => [
            'code' => 'insufficient_quota',
            'message' => 'Quota exceeded for test-key and sk-proj-secret.',
        ]], 429));

        $run = $this->start($this->german);
        $this->work(2);

        $this->getJson($this->url($this->german))->assertOk()
            ->assertJsonPath('data.id', $run)
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.error', 'OpenAI (HTTP 429): Quota exceeded for [redacted] and [redacted]');
        $this->assertSame(0, Queue::connection('auto-translations')->size());
        Http::assertSentCount(1);

        $retry = $this->start($this->german);
        $this->assertNotSame($run, $retry);
        $this->getJson($this->url($this->german))->assertJsonPath('data.id', $retry)
            ->assertJsonPath('data.error', null);
    }

    /**
     * Represent connection failures as safe, retryable progress details.
     *
     * @return void Verifies the OpenAI call remains queued for a later attempt.
     */
    public function test_openai_connection_failure_is_visible_while_waiting_to_retry(): void
    {
        $this->actingAsSuperAdmin();
        $this->field($this->english, 1, 'Save');
        Http::fake(['api.openai.com/*' => Http::failedConnection()]);

        $run = $this->start($this->german);
        $this->work(2);

        $this->assertRun($this->german, $run, 'translating', 1);
        $this->getJson($this->url($this->german))
            ->assertJsonPath('data.error', 'اتصال به OpenAI برقرار نشد یا زمان پاسخ‌گویی پایان یافت.');
        $this->assertSame(1, Queue::connection('auto-translations')->size());
    }

    /**
     * Define the locale columns used by the API and publisher.
     *
     * @param  Blueprint  $table  Fixture table being defined.
     * @return void Adds columns to the supplied schema blueprint.
     */
    private function translationColumns(Blueprint $table): void
    {
        $table->string('code')->unique();
        $table->string('name');
        $table->boolean('status')->default(true);
        $table->unsignedInteger('version')->default(1);
        $table->string('file_url')->nullable();
    }

    /**
     * Create a field in a named modal and tab for the given locale.
     *
     * @param  Translation  $language  Locale that owns the field.
     * @param  int|null  $id  Shared field ID or null for an unkeyed row.
     * @param  string|null  $text  Initial translation.
     * @param  string  $modal  Modal name for the field.
     * @param  string  $tab  Tab name for the field.
     * @return Field The field persisted in SQLite.
     */
    private function field(Translation $language, ?int $id, ?string $text, string $modal = 'Screen', string $tab = 'Main'): Field
    {
        return $language->modals()->firstOrCreate(['name' => $modal])
            ->tabs()->firstOrCreate(['name' => $tab])
            ->fields()->create(['unique_id' => $id, 'translation' => $text]);
    }

    /**
     * Build the auto-translation API path for a locale.
     *
     * @param  Translation  $language  Locale whose ID appears in the path.
     * @return string Locale-scoped auto-translation endpoint.
     */
    private function url(Translation $language): string
    {
        return "/api/translations/{$language->id}/auto-translate";
    }

    /**
     * Start an accepted run and return its identifier.
     *
     * @param  Translation  $language  Destination locale.
     * @return int Identifier read from the HTTP 202 response.
     */
    private function start(Translation $language): int
    {
        return $this->postJson($this->url($language))->assertAccepted()->json('data.id');
    }

    /**
     * Run the specified number of jobs through Laravel’s database queue.
     *
     * @param  int  $jobs  Number of queue steps to run.
     * @return void Executes jobs through Laravel's queue worker.
     */
    private function work(int $jobs = 1): void
    {
        for ($index = 0; $index < $jobs; $index++) {
            app('queue.worker')->runNextJob('auto-translations', 'auto-translations', new WorkerOptions(sleep: 0));
        }
    }

    /**
     * Assert the run status and progress counts returned by the API.
     *
     * @param  Translation  $language  Locale used to scope the request.
     * @param  int  $id  Run ID to fetch.
     * @param  string  $status  Expected status.
     * @param  int  $total  Expected candidate count.
     * @param  int  $processed  Expected validated result count.
     * @param  int  $translated  Expected applied translation count.
     * @return void Reports mismatches through PHPUnit assertions.
     */
    private function assertRun(Translation $language, int $id, string $status, int $total = 0, int $processed = 0, int $translated = 0): void
    {
        $this->getJson($this->url($language).'/'.$id)->assertOk()
            ->assertJsonPath('data.status', $status)
            ->assertJsonPath('data.total', $total)
            ->assertJsonPath('data.processed', $processed)
            ->assertJsonPath('data.translated', $translated);
    }

    /**
     * Return the supplied translation for each requested key.
     *
     * @param  Request  $request  Captured OpenAI HTTP request.
     * @param  string  $text  Fixture translation to return for every key.
     * @return PromiseInterface Mock HTTP response accepted by Laravel's HTTP fake.
     */
    private function translateRequest(Request $request, string $text = 'Übersetzung')
    {
        return $this->providerResponse(array_map(
            fn ($item) => ['id' => $item['id'], 'translation' => $text],
            json_decode($request['input'], true)['strings']
        ));
    }

    /**
     * Wrap fixture translations in a completed OpenAI Responses API payload.
     *
     * @param  list<array{id: string, translation: string}>  $results  Mocked translations to encode.
     * @return PromiseInterface Mock HTTP response accepted by Laravel's HTTP fake.
     */
    private function providerResponse(array $results)
    {
        return Http::response([
            'status' => 'completed',
            'output' => [
                ['type' => 'reasoning', 'summary' => []],
                ['type' => 'message', 'content' => [
                    ['type' => 'output_text', 'text' => json_encode(['translations' => $results], JSON_THROW_ON_ERROR)],
                ]],
            ],
        ]);
    }
}

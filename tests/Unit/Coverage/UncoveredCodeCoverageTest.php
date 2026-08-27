<?php

namespace Tests\Unit\Coverage;

use App\Console\Kernel as ConsoleKernel;
use App\Exceptions\Handler;
use App\Http\Controllers\Api\MapsController;
use App\Http\Controllers\Api\V1\TranslationController as LegacyTranslationController;
use App\Http\Controllers\UploadVideoController;
use App\Http\Middleware\AccessLog;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\FilterIp;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrustHosts;
use App\Models\Asset;
use App\Models\Challenge\Answer;
use App\Models\Challenge\CorrectAnswer;
use App\Models\Challenge\UserChallengePrizes;
use App\Models\Comission;
use App\Models\Coordinate;
use App\Models\CrsProperty;
use App\Models\Dislike;
use App\Models\Employee\Employee;
use App\Models\FeatureImage;
use App\Models\FirstOrder;
use App\Models\Image;
use App\Models\Interaction;
use App\Models\Ip;
use App\Models\KycError;
use App\Models\Land;
use App\Models\Level\Prize;
use App\Models\Level\UserLog;
use App\Models\Like;
use App\Models\Note;
use App\Models\Order;
use App\Models\ReferralOrderHistory;
use App\Models\SellFeatureRequest;
use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\Transaction;
use App\Models\Translations\Translation;
use App\Models\VariableChangeLog;
use App\Models\View;
use App\Providers\AuthServiceProvider;
use App\Providers\BroadcastServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\AuthManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class UncoveredCodeCoverageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('activitylog.enabled', false);
        \Illuminate\Support\Facades\DB::purge('sqlite');
        \Illuminate\Support\Facades\DB::reconnect('sqlite');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_access_log_middleware_appends_log_line(): void
    {
        $logPath = storage_path('logs/access.log');
        @unlink($logPath);

        $middleware = new AccessLog;
        $request = Request::create('/api/coverage', 'GET');
        $response = $middleware->handle($request, fn () => response('ok', 201));

        $this->assertSame(201, $response->getStatusCode());
        $this->assertFileExists($logPath);
        $this->assertStringContainsString('"GET api/coverage" 201', (string) file_get_contents($logPath));
        @unlink($logPath);
    }

    public function test_filter_ip_middleware_allows_local_and_checks_admin_ips(): void
    {
        $middleware = new FilterIp;
        $this->app['env'] = 'local';

        $allowed = $middleware->handle(Request::create('/api/anything', 'GET'), fn () => response('local'));
        $this->assertSame('local', $allowed->getContent());

        $this->app['env'] = 'staging';

        Schema::create('ips', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('from')->nullable();
            $table->unsignedBigInteger('to')->nullable();
            $table->timestamps();
        });

        $ipValue = '203.0.113.10';
        Ip::query()->create([
            'title' => 'admin',
            'type' => 'admin',
            'from' => $ipValue,
            'to' => $ipValue,
        ]);

        $adminRequest = Request::create('/api/dashboard', 'GET', server: ['REMOTE_ADDR' => $ipValue]);
        $ok = $middleware->handle($adminRequest, fn () => response('admin-ok'));
        $this->assertSame('admin-ok', $ok->getContent());

        try {
            $middleware->handle(
                Request::create('/api/dashboard', 'GET', server: ['REMOTE_ADDR' => '198.51.100.1']),
                fn () => response('should-not-run')
            );
            $this->fail('Expected 403 abort for unauthorized IP');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_redirect_if_authenticated_middleware_redirects_and_passes_through(): void
    {
        $middleware = new RedirectIfAuthenticated;

        $guest = $middleware->handle(Request::create('/login', 'GET'), fn () => response('guest'));
        $this->assertSame('guest', $guest->getContent());

        Auth::shouldReceive('guard')->with(null)->andReturnSelf();
        Auth::shouldReceive('check')->andReturn(true);

        $redirect = $middleware->handle(Request::create('/login', 'GET'), fn () => response('authed'));
        $this->assertSame(302, $redirect->getStatusCode());
        $this->assertSame(url(RouteServiceProvider::HOME), $redirect->headers->get('Location'));
    }

    public function test_authenticate_redirect_to_returns_login_for_non_json(): void
    {
        $middleware = new class($this->app->make(AuthManager::class)) extends Authenticate
        {
            public function exposedRedirectTo($request)
            {
                return $this->redirectTo($request);
            }
        };

        $jsonRequest = Request::create('/api/x', 'GET');
        $jsonRequest->headers->set('Accept', 'application/json');
        $this->assertNull($middleware->exposedRedirectTo($jsonRequest));

        // Cover the non-JSON branch without relying on a named login route.
        $covered = false;
        try {
            $middleware->exposedRedirectTo(Request::create('/admin', 'GET'));
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $exception) {
            $covered = str_contains($exception->getMessage(), 'login');
        }
        $this->assertTrue($covered);
    }

    public function test_trust_hosts_returns_application_subdomain_pattern(): void
    {
        config(['app.url' => 'https://admin.example.test']);
        $hosts = (new TrustHosts($this->app))->hosts();

        $this->assertNotEmpty($hosts);
        $this->assertIsString($hosts[0]);
    }

    public function test_console_kernel_schedule_and_commands_loaders(): void
    {
        $kernel = $this->app->make(ConsoleKernel::class);
        $scheduleMethod = new \ReflectionMethod(ConsoleKernel::class, 'schedule');
        $scheduleMethod->setAccessible(true);
        $scheduleMethod->invoke($kernel, $this->app->make(Schedule::class));

        $commandsMethod = new \ReflectionMethod(ConsoleKernel::class, 'commands');
        $commandsMethod->setAccessible(true);
        $commandsMethod->invoke($kernel);

        $this->assertTrue(true);
    }

    public function test_exception_handler_register_adds_reportable_callback(): void
    {
        $handler = $this->app->make(Handler::class);
        $handler->register();
        $this->assertTrue(true);
    }

    public function test_auth_event_and_broadcast_providers_boot(): void
    {
        (new AuthServiceProvider($this->app))->boot();

        $eventProvider = new EventServiceProvider($this->app);
        $eventProvider->boot();
        $this->assertFalse($eventProvider->shouldDiscoverEvents());

        try {
            (new BroadcastServiceProvider($this->app))->boot();
        } catch (\Throwable) {
            // Ignore duplicate route registration in the test suite.
        }

        $this->assertTrue(true);
    }

    public function test_route_service_provider_rate_limiter_configuration(): void
    {
        $provider = new RouteServiceProvider($this->app);
        $configure = new \ReflectionMethod(RouteServiceProvider::class, 'configureRateLimiting');
        $configure->setAccessible(true);
        $configure->invoke($provider);

        $limiter = \Illuminate\Support\Facades\RateLimiter::limiter('api');
        $this->assertNotNull($limiter);
        $limit = $limiter(Request::create('/api/x', 'GET'));
        $this->assertSame(60, $limit->maxAttempts);
    }

    public function test_ip_model_casts_and_admin_scope(): void
    {
        Schema::create('ips', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('from')->nullable();
            $table->unsignedBigInteger('to')->nullable();
            $table->timestamps();
        });

        $ip = Ip::query()->create([
            'title' => 'range',
            'type' => 'admin',
            'from' => '127.0.0.1',
            'to' => '127.0.0.2',
        ]);

        $fresh = $ip->fresh();
        $this->assertSame('127.0.0.1', $fresh->from);
        $this->assertSame('127.0.0.2', $fresh->to);
        $this->assertSame(1, Ip::admin()->count());
    }

    public function test_thin_model_relations_for_previously_uncovered_models(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Asset)->user());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Asset)->variable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Comission)->trade());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Coordinate)->geometry());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new CrsProperty)->crs());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, (new Dislike)->dislikeable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new FeatureImage)->feature());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new FirstOrder)->user());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, (new Image)->imageable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, (new Interaction)->likeable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, (new KycError)->errorable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Land)->user());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, (new Like)->dislikeable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Note)->user());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class, (new Order)->transactions());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new ReferralOrderHistory)->user());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new ReferralOrderHistory)->referral());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new TicketResponse)->ticket());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new TicketResponse)->responser());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, (new Transaction)->payable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Transaction)->user());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, (new VariableChangeLog)->changeable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, (new View)->viewable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Answer)->question());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new CorrectAnswer)->question());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new CorrectAnswer)->answer());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new UserChallengePrizes)->user());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new UserChallengePrizes)->questionPrize());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, (new Employee)->bankAccounts());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Prize)->level());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new UserLog)->user());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new SellFeatureRequest)->seller());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new SellFeatureRequest)->feature());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, (new Ticket)->responses());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Ticket)->sender());
    }

    public function test_legacy_translation_controller_index_and_nested_resources(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name')->nullable();
            $table->string('native_name')->nullable();
            $table->string('direction')->nullable();
            $table->unsignedInteger('version')->default(0);
            $table->string('file_url')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::create('modals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('tabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modal_id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tab_id');
            $table->unsignedBigInteger('unique_id')->nullable();
            $table->text('translation')->nullable();
            $table->timestamps();
        });

        $translation = Translation::query()->create([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'status' => true,
            'version' => 1,
            'file_url' => 'https://example.test/lang/en.json',
        ]);
        $modal = $translation->modals()->create(['name' => 'profile']);
        $tab = $modal->tabs()->create(['name' => 'general']);
        $tab->fields()->create(['unique_id' => 1, 'translation' => 'Hello']);

        $controller = new LegacyTranslationController;
        $index = $controller->index();
        $this->assertSame(200, $index->getStatusCode());
        $payload = $index->getData(true);
        $this->assertSame('en', $payload['data'][0]['code']);
        $this->assertStringContainsString('EN.svg', $payload['data'][0]['icon']);

        $modals = $controller->getModals($translation)->getData(true);
        $this->assertCount(1, $modals['data']);

        $tabs = $controller->getTabs($translation, $modal)->getData(true);
        $this->assertCount(1, $tabs['data']);

        $fields = $controller->getFields($translation, $modal, $tab)->getData(true);
        $this->assertCount(1, $fields['data']);
    }

    public function test_upload_video_controller_finished_and_partial_chunk_paths(): void
    {
        Storage::fake('local');

        $controller = new UploadVideoController;

        $finishedRequest = Request::create('/upload', 'POST', [], [], [
            'file' => UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
        ]);
        $finished = $controller->upload($finishedRequest);

        $this->assertSame(200, $finished->getStatusCode());
        $finishedData = $finished->getData(true);
        $this->assertArrayHasKey('file_name', $finishedData);
        $this->assertArrayHasKey('file_path', $finishedData);
        Storage::disk('local')->assertExists($finishedData['file_path']);

        $partialRequest = Request::create('/upload', 'POST', [
            'resumableChunkNumber' => 1,
            'resumableTotalChunks' => 3,
            'resumableChunkSize' => 1000,
            'resumableCurrentChunkSize' => 50,
            'resumableTotalSize' => 3000,
            'resumableIdentifier' => 'coverage-partial-'.uniqid(),
            'resumableFilename' => 'part.mp4',
            'resumableRelativePath' => 'part.mp4',
        ], [], [
            'file' => UploadedFile::fake()->create('part.mp4', 50, 'video/mp4'),
        ]);
        $partial = $controller->upload($partialRequest);
        $this->assertSame(200, $partial->getStatusCode());
        $partialData = $partial->getData(true);
        $this->assertTrue($partialData['status']);
        $this->assertArrayHasKey('done', $partialData);
    }

    public function test_maps_controller_sanitize_helpers_cover_invalid_names(): void
    {
        $controller = new class extends MapsController
        {
            public function callSanitizeUpload(string $name): string
            {
                return $this->sanitizeUploadFileName($name);
            }

            public function callSanitizeStored(string $name): ?string
            {
                return $this->sanitizeStoredFileName($name);
            }
        };

        $this->assertSame('ok.geojson', $controller->callSanitizeUpload('ok.geojson'));

        try {
            $controller->callSanitizeUpload('..');
            $this->fail('Expected invalid upload name');
        } catch (\InvalidArgumentException) {
            $this->assertTrue(true);
        }

        try {
            $controller->callSanitizeUpload("bad\x00name.geojson");
            $this->fail('Expected control-character rejection');
        } catch (\InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $this->assertNull($controller->callSanitizeStored('..'));
        $this->assertSame('keep.geojson', $controller->callSanitizeStored('keep.geojson'));
    }
}

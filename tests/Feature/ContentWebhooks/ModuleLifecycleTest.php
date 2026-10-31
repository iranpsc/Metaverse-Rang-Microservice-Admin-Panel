<?php

namespace Tests\Feature\ContentWebhooks;

use App\Http\Middleware\CaptureContentChanges;
use App\Models\Calendar;
use App\Models\Image;
use App\Models\Level\Level;
use App\Models\Level\LevelGem;
use App\Models\Level\LevelGeneralInfo;
use App\Models\Level\LevelGift;
use App\Models\Level\LevelLicense;
use App\Models\Level\LevelPrize;
use App\Models\Video;
use App\Models\VideoCategory;
use App\Models\VideoSubCategory;
use Illuminate\Http\Request;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class ModuleLifecycleTest extends TestCase
{
    use ActsAsSuperAdmin, CreatesAuthApiSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthApiSchema();
        foreach (['levels', 'level_general_infos', 'level_gems', 'level_gifts', 'level_licenses', 'level_prizes', 'calendars', 'videos', 'video_categories', 'video_sub_categories', 'images'] as $name) {
            Schema::create($name, function ($t) {
                $t->id();
                $t->timestamps();
                foreach (['name', 'title', 'slug', 'score', 'description', 'color', 'url', 'imageable_type'] as $field) {
                    $t->string($field)->nullable();
                }
                foreach (['level_id', 'video_category_id', 'video_sub_category_id', 'imageable_id', 'monthly_capacity_count', 'psc', 'points'] as $field) {
                    $t->integer($field)->nullable();
                }
                $t->boolean('is_version')->default(false);
                $t->boolean('create_union')->default(false);
            });
        }
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
        config(['content_webhooks.enabled' => true, 'content_webhooks.secret' => 'local-webhook-test-secret',
            'content_webhooks.url' => 'http://127.0.0.1:4900/api/webhooks/cache-revalidate']);
        $this->actingAsSuperAdmin();
    }

    public static function modules(): array
    {
        return [
            'level' => [Level::class, 'name', 'Diamond', 'Ruby', 'citizen_levels', 'record', '/api/levels/1'],
            'general information' => [LevelGeneralInfo::class, 'description', 'Before', 'After', 'citizen_levels_details', 'general-info', '/api/levels/1/general-info'],
            'gem' => [LevelGem::class, 'points', 1, 2, 'citizen_levels_details', 'gem', '/api/levels/1/gem'],
            'gift' => [LevelGift::class, 'monthly_capacity_count', 1, 2, 'citizen_levels_details', 'gift', '/api/levels/1/gift'],
            'licenses' => [LevelLicense::class, 'create_union', false, true, 'citizen_levels_details', 'licenses', '/api/levels/1/licenses'],
            'prize' => [LevelPrize::class, 'psc', 1, 2, 'citizen_levels_details', 'prize', '/api/levels/1/prize'],
            'image' => [Image::class, 'url', 'before.png', 'after.png', 'citizen_levels', 'image', '/api/levels/1'],
            'calendar' => [Calendar::class, 'title', 'Event', 'New event', 'calendar', 'record', '/api/calendar/1'],
            'version' => [Calendar::class, 'title', 'Release', 'New release', 'versions', 'record', '/api/calendar/latest-version'],
            'video' => [Video::class, 'slug', 'before', 'after', 'videos', 'record', '/api/tutorials/before'],
            'category' => [VideoCategory::class, 'slug', 'before', 'after', 'video_categories', 'record', '/api/tutorials/categories/before'],
            'subcategory' => [VideoSubCategory::class, 'slug', 'before', 'after', 'video_sub_categories', 'record', '/api/tutorials/categories/parent/before'],
        ];
    }

    #[DataProvider('modules')]
    public function test_each_module_preserves_audit_history_and_delivers_through_database_queue($class, $field, $before, $after, $feature, $resource, $route): void
    {
        // Fixture parents must not add publication records to this lifecycle.
        $level = Level::withoutEvents(fn () => Level::create(['name' => 'Parent']));
        $category = VideoCategory::withoutEvents(fn () => VideoCategory::create(['slug' => 'parent']));
        $sub = VideoSubCategory::withoutEvents(fn () => VideoSubCategory::create(['slug' => 'child', 'video_category_id' => $category->id]));
        $attrs = [$field => $before];
        if (str_contains($class, '\\Level\\') && $class !== Level::class) {
            $attrs['level_id'] = $level->id;
        }
        if ($class === Image::class) {
            $attrs += ['imageable_type' => Level::class, 'imageable_id' => $level->id];
        }
        if ($class === Calendar::class) {
            $attrs['is_version'] = $feature === 'versions';
        }
        if ($class === Video::class) {
            $attrs['video_sub_category_id'] = $sub->id;
        }
        if ($class === VideoSubCategory::class) {
            $attrs['video_category_id'] = $category->id;
        }
        $model = null;
        $mutate = fn ($method, $callback) => app(CaptureContentChanges::class)->handle(Request::create('/api/levels/1', $method), function () use ($callback) {
            $callback();

            return response()->json(['success' => true]);
        });
        $mutate('POST', function () use (&$model, $class, $attrs, $level) {
            $model = $class === Image::class ? $level->image()->create($attrs) : $class::create($attrs);
        });
        $mutate('PUT', fn () => $model->update([$field => $after]));
        if ($model->usesTimestamps()) {
            $this->travel(1)->seconds();
            $mutate('PUT', fn () => $model->touch());
        }
        $mutate('DELETE', fn () => $model->delete());
        $this->assertSame(3, DB::table('content_changes')->count(), 'Create, meaningful update, delete; no timestamp-only log');
        $entries = DB::table('content_changes')->orderBy('id')->get();
        $data = $entries->map(fn ($e) => json_decode($e->data, true));
        $this->assertSame(['CREATE', 'UPDATE', 'DELETE'], $data->pluck('action')->all());
        $this->assertSame([$feature], $data->pluck('feature')->unique()->values()->all());
        $this->assertSame([$resource], $data->pluck('resource')->unique()->values()->all());
        $this->assertStringContainsString($resource === 'image' ? 'image' : str_replace('_', ' ', $field), $data[1]['description']);
        if ($class === Level::class) {
            $route = '/api/levels/'.$model->id;
        }
        $this->assertContains($route, $data[0]['affected_routes']);
        if ($class === Video::class) {
            $this->assertContains('/api/tutorials/after', $data[1]['affected_routes']);
            $this->assertContains('/api/tutorials/before', $data[1]['affected_routes']);
        }
        if (! getenv('CONTENT_WEBHOOK_NODE_RECEIVER')) {
            Http::fake(fn ($r) => Http::response(['batch_id' => $r['batch_id'], 'status' => 'accepted']));
        }
        $this->postJson('/api/activity-logs/webhook-dispatch')->assertStatus(202);
        $work = fn () => $this->app->make('queue.worker')->runNextJob('content-webhooks', 'content-webhooks', new WorkerOptions(sleep: 0, maxTries: 0));
        $work();
        $work();
        $work();
        $batch = DB::table('content_webhook_batches')->first();
        $this->assertSame('delivered', $batch->status);
        $this->assertSame('V1.1.1', $batch->version);
        $this->assertSame(0, DB::table('jobs')->count());
        foreach ($entries as $entry) {
            $this->getJson('/api/activity-logs/'.$entry->activity_id)->assertOk()->assertJsonPath('data.activity.delivery.status', 'delivered');
        }
    }
}

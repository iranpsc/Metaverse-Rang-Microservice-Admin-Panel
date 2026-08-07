<?php

namespace Tests\Feature\ActivityLog;

use App\Models\Admin;
use App\Services\ActivityLogCategoryResolver;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Morilog\Jalali\Jalalian;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class ActivityLogApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesAuthApiSchema;

    private const CATEGORIES_PATH = '/api/activity-logs/categories';

    private const INDEX_PATH = '/api/activity-logs';

    private const FORBIDDEN_MESSAGE = 'شما دسترسی مشاهده گزارش فعالیت‌ها را ندارید.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthApiSchema();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // -------------------------------------------------------------------------
    // Auth / authorization
    // -------------------------------------------------------------------------

    public function test_unauthenticated_request_returns_unauthorized_on_categories(): void
    {
        $this->getJson(self::CATEGORIES_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_request_returns_unauthorized_on_index(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_request_returns_unauthorized_on_show(): void
    {
        $this->getJson($this->showPath(1))->assertUnauthorized();
    }

    public function test_regular_admin_without_permission_receives_forbidden_on_categories(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::CATEGORIES_PATH)
            ->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => self::FORBIDDEN_MESSAGE,
            ]);
    }

    public function test_regular_admin_without_permission_receives_forbidden_on_index(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => self::FORBIDDEN_MESSAGE,
            ]);
    }

    public function test_regular_admin_without_permission_receives_forbidden_on_show(): void
    {
        $this->actingAsRegularAdmin();

        $activity = $this->createActivity(['description' => 'blocked show']);

        $this->getJson($this->showPath($activity->id))
            ->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => self::FORBIDDEN_MESSAGE,
            ]);
    }

    public function test_super_admin_can_access_categories(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::CATEGORIES_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_super_admin_can_access_index(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_super_admin_can_access_show(): void
    {
        $this->actingAsSuperAdmin();
        $activity = $this->createActivity(['description' => 'super show']);

        $this->getJson($this->showPath($activity->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.activity.id', $activity->id);
    }

    public function test_admin_with_view_activity_logs_permission_can_access_endpoints(): void
    {
        $this->actingAsAdminWithViewActivityLogsPermission();

        $activity = $this->createActivity(['description' => 'permission access']);

        $this->getJson(self::CATEGORIES_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson($this->showPath($activity->id))
            ->assertOk()
            ->assertJsonPath('data.activity.id', $activity->id);
    }

    // -------------------------------------------------------------------------
    // categories()
    // -------------------------------------------------------------------------

    public function test_categories_returns_all_resolver_categories_as_id_label_pairs(): void
    {
        $this->actingAsSuperAdmin();

        $expected = collect(ActivityLogCategoryResolver::categories())
            ->map(fn (string $label, string $id) => ['id' => $id, 'label' => $label])
            ->values()
            ->all();

        $response = $this->getJson(self::CATEGORIES_PATH)
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['categories' => $expected],
            ]);

        $categories = $response->json('data.categories');

        $this->assertCount(count(ActivityLogCategoryResolver::categories()), $categories);
        $this->assertContains(['id' => 'auth', 'label' => 'احراز هویت'], $categories);
        $this->assertContains(['id' => 'dashboard', 'label' => 'داشبورد'], $categories);
    }

    // -------------------------------------------------------------------------
    // index()
    // -------------------------------------------------------------------------

    public function test_index_empty_database_returns_empty_activities_and_pagination(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'activities' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 15,
                        'total' => 0,
                        'from' => null,
                        'to' => null,
                    ],
                ],
            ]);
    }

    public function test_index_lists_activities_newest_first_by_id(): void
    {
        $this->actingAsSuperAdmin();

        $older = $this->createActivity(['description' => 'older']);
        $newer = $this->createActivity(['description' => 'newer']);

        $ids = collect($this->getJson(self::INDEX_PATH)->assertOk()->json('data.activities'))
            ->pluck('id')
            ->all();

        $this->assertSame([$newer->id, $older->id], $ids);
    }

    public function test_index_filters_by_category_log_name(): void
    {
        $this->actingAsSuperAdmin();

        $this->createActivity(['log_name' => 'auth', 'description' => 'auth log']);
        $this->createActivity(['log_name' => 'dashboard', 'description' => 'dash log']);

        $activities = $this->getJson(self::INDEX_PATH.'?category=auth')
            ->assertOk()
            ->json('data.activities');

        $this->assertCount(1, $activities);
        $this->assertSame('auth', $activities[0]['log_name']);
        $this->assertSame('auth log', $activities[0]['description']);
    }

    public function test_index_category_all_does_not_filter(): void
    {
        $this->actingAsSuperAdmin();

        $this->createActivity(['log_name' => 'auth', 'description' => 'a']);
        $this->createActivity(['log_name' => 'dashboard', 'description' => 'b']);

        $this->getJson(self::INDEX_PATH.'?category=all')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);
    }

    public function test_index_filters_by_event(): void
    {
        $this->actingAsSuperAdmin();

        $this->createActivity(['event' => 'created', 'description' => 'created one']);
        $this->createActivity(['event' => 'updated', 'description' => 'updated one']);

        $activities = $this->getJson(self::INDEX_PATH.'?event=updated')
            ->assertOk()
            ->json('data.activities');

        $this->assertCount(1, $activities);
        $this->assertSame('updated', $activities[0]['event']);
    }

    public function test_index_event_all_does_not_filter(): void
    {
        $this->actingAsSuperAdmin();

        $this->createActivity(['event' => 'created', 'description' => 'a']);
        $this->createActivity(['event' => 'deleted', 'description' => 'b']);

        $this->getJson(self::INDEX_PATH.'?event=all')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);
    }

    public function test_index_searches_by_description(): void
    {
        $this->actingAsSuperAdmin();

        $this->createActivity(['description' => 'unique-search-token']);
        $this->createActivity(['description' => 'other activity']);

        $activities = $this->getJson(self::INDEX_PATH.'?search=unique-search-token')
            ->assertOk()
            ->json('data.activities');

        $this->assertCount(1, $activities);
        $this->assertSame('unique-search-token', $activities[0]['description']);
    }

    public function test_index_searches_by_causer_name(): void
    {
        $this->actingAsSuperAdmin();

        $causer = $this->createCauserAdmin([
            'name' => 'Searchable Causer',
            'email' => 'causer-name@example.com',
        ]);

        $this->createActivity([
            'description' => 'by name',
            'causer_type' => Admin::class,
            'causer_id' => $causer->id,
        ]);
        $this->createActivity(['description' => 'no causer match']);

        $activities = $this->getJson(self::INDEX_PATH.'?search=Searchable')
            ->assertOk()
            ->json('data.activities');

        $this->assertCount(1, $activities);
        $this->assertSame('by name', $activities[0]['description']);
        $this->assertSame($causer->id, $activities[0]['causer']['id']);
    }

    public function test_index_searches_by_causer_email(): void
    {
        $this->actingAsSuperAdmin();

        $causer = $this->createCauserAdmin([
            'name' => 'Email Causer',
            'email' => 'unique-causer@example.com',
        ]);

        $this->createActivity([
            'description' => 'by email',
            'causer_type' => Admin::class,
            'causer_id' => $causer->id,
        ]);
        $this->createActivity(['description' => 'other']);

        $activities = $this->getJson(self::INDEX_PATH.'?search=unique-causer@example.com')
            ->assertOk()
            ->json('data.activities');

        $this->assertCount(1, $activities);
        $this->assertSame('by email', $activities[0]['description']);
    }

    public function test_index_search_trims_whitespace(): void
    {
        $this->actingAsSuperAdmin();

        $this->createActivity(['description' => 'trim-target-activity']);
        $this->createActivity(['description' => 'noise']);

        $activities = $this->getJson(self::INDEX_PATH.'?search='.urlencode('  trim-target-activity  '))
            ->assertOk()
            ->json('data.activities');

        $this->assertCount(1, $activities);
        $this->assertSame('trim-target-activity', $activities[0]['description']);
    }

    public function test_index_respects_custom_page_and_per_page(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->createActivity(['description' => "activity-{$i}"]);
        }

        $response = $this->getJson(self::INDEX_PATH.'?per_page=2&page=2')
            ->assertOk();

        $this->assertCount(2, $response->json('data.activities'));
        $this->assertSame(2, $response->json('data.pagination.current_page'));
        $this->assertSame(2, $response->json('data.pagination.per_page'));
        $this->assertSame(5, $response->json('data.pagination.total'));
        $this->assertSame(3, $response->json('data.pagination.last_page'));
    }

    public function test_index_caps_per_page_at_fifty(): void
    {
        $this->actingAsSuperAdmin();

        $this->createActivity(['description' => 'cap test']);

        $this->getJson(self::INDEX_PATH.'?per_page=100')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 50);
    }

    public function test_index_formats_activity_json_structure_without_subject(): void
    {
        $this->actingAsSuperAdmin();

        $causer = $this->createCauserAdmin([
            'name' => 'Format Causer',
            'email' => 'format@example.com',
        ]);
        $subject = $this->createCauserAdmin([
            'name' => 'Subject Admin',
            'email' => 'subject@example.com',
        ]);

        $createdAt = now()->startOfSecond();
        $activity = $this->createActivity([
            'log_name' => 'auth',
            'description' => 'formatted activity',
            'event' => 'login',
            'causer_type' => Admin::class,
            'causer_id' => $causer->id,
            'subject_type' => Admin::class,
            'subject_id' => $subject->id,
            'properties' => ['ip' => '127.0.0.1'],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $jalali = Jalalian::fromCarbon($createdAt);

        $item = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'activities' => [
                        [
                            'id',
                            'description',
                            'event',
                            'log_name',
                            'category',
                            'category_label',
                            'subject_type',
                            'subject_id',
                            'causer' => ['id', 'name', 'email'],
                            'properties',
                            'created_at',
                            'created_at_jalali',
                            'created_at_time',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'from',
                        'to',
                    ],
                ],
            ])
            ->json('data.activities.0');

        $this->assertSame($activity->id, $item['id']);
        $this->assertSame('formatted activity', $item['description']);
        $this->assertSame('login', $item['event']);
        $this->assertSame('auth', $item['log_name']);
        $this->assertSame('auth', $item['category']);
        $this->assertSame('احراز هویت', $item['category_label']);
        $this->assertSame('Admin', $item['subject_type']);
        $this->assertSame($subject->id, $item['subject_id']);
        $this->assertSame([
            'id' => $causer->id,
            'name' => 'Format Causer',
            'email' => 'format@example.com',
        ], $item['causer']);
        $this->assertSame(['ip' => '127.0.0.1'], $item['properties']);
        $this->assertSame($jalali->format('Y/m/d'), $item['created_at_jalali']);
        $this->assertSame($jalali->format('H:i:s'), $item['created_at_time']);
        $this->assertArrayNotHasKey('subject', $item);
    }

    public function test_index_uses_properties_category_override_for_label(): void
    {
        $this->actingAsSuperAdmin();

        $this->createActivity([
            'log_name' => 'default',
            'description' => 'override category',
            'properties' => ['category' => 'citizens'],
        ]);

        $item = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->json('data.activities.0');

        $this->assertSame('citizens', $item['category']);
        $this->assertSame('شهروندان', $item['category_label']);
        $this->assertSame('default', $item['log_name']);
    }

    public function test_index_returns_null_causer_when_missing(): void
    {
        $this->actingAsSuperAdmin();

        $this->createActivity([
            'description' => 'no causer',
            'causer_type' => null,
            'causer_id' => null,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.activities.0.causer', null);
    }

    // -------------------------------------------------------------------------
    // show()
    // -------------------------------------------------------------------------

    public function test_show_returns_not_found_for_missing_id(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson($this->showPath(999999))->assertNotFound();
    }

    public function test_show_returns_formatted_activity_including_subject(): void
    {
        $this->actingAsSuperAdmin();

        $causer = $this->createCauserAdmin([
            'name' => 'Show Causer',
            'email' => 'show-causer@example.com',
        ]);
        $subject = $this->createCauserAdmin([
            'name' => 'Show Subject',
            'email' => 'show-subject@example.com',
        ]);

        $createdAt = now()->startOfSecond();
        $activity = $this->createActivity([
            'log_name' => 'dashboard',
            'description' => 'show detail',
            'event' => 'viewed',
            'causer_type' => Admin::class,
            'causer_id' => $causer->id,
            'subject_type' => Admin::class,
            'subject_id' => $subject->id,
            'properties' => ['source' => 'test'],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $jalali = Jalalian::fromCarbon($createdAt);

        $payload = $this->getJson($this->showPath($activity->id))
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'activity' => [
                        'id',
                        'description',
                        'event',
                        'log_name',
                        'category',
                        'category_label',
                        'subject_type',
                        'subject_id',
                        'causer' => ['id', 'name', 'email'],
                        'properties',
                        'created_at',
                        'created_at_jalali',
                        'created_at_time',
                        'subject',
                    ],
                ],
            ])
            ->json('data.activity');

        $this->assertSame($activity->id, $payload['id']);
        $this->assertSame('show detail', $payload['description']);
        $this->assertSame('viewed', $payload['event']);
        $this->assertSame('dashboard', $payload['log_name']);
        $this->assertSame('dashboard', $payload['category']);
        $this->assertSame('داشبورد', $payload['category_label']);
        $this->assertSame('Admin', $payload['subject_type']);
        $this->assertSame($subject->id, $payload['subject_id']);
        $this->assertSame([
            'id' => $causer->id,
            'name' => 'Show Causer',
            'email' => 'show-causer@example.com',
        ], $payload['causer']);
        $this->assertSame(['source' => 'test'], $payload['properties']);
        $this->assertSame($jalali->format('Y/m/d'), $payload['created_at_jalali']);
        $this->assertSame($jalali->format('H:i:s'), $payload['created_at_time']);
        $this->assertIsArray($payload['subject']);
        $this->assertSame($subject->id, $payload['subject']['id']);
        $this->assertSame('Show Subject', $payload['subject']['name']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function showPath(int $id): string
    {
        return self::INDEX_PATH.'/'.$id;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createActivity(array $attributes = []): Activity
    {
        $now = now();

        return Activity::create(array_merge([
            'log_name' => 'default',
            'description' => 'test activity',
            'event' => 'created',
            'subject_type' => null,
            'subject_id' => null,
            'causer_type' => null,
            'causer_id' => null,
            'properties' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createCauserAdmin(array $overrides = []): Admin
    {
        return Admin::withoutEvents(function () use ($overrides) {
            return Admin::create([
                'name' => $overrides['name'] ?? 'Causer Admin',
                'email' => $overrides['email'] ?? Str::uuid().'@example.com',
                'password' => bcrypt('password'),
                'phone' => $overrides['phone'] ?? '09123334444',
                'active' => 1,
            ]);
        });
    }

    private function actingAsAdminWithViewActivityLogsPermission(): Admin
    {
        $admin = Admin::withoutEvents(function () {
            return Admin::create([
                'name' => 'Permission Admin',
                'email' => Str::uuid().'@example.com',
                'password' => bcrypt('password'),
                'phone' => '09125556666',
                'active' => 1,
            ]);
        });

        // Spatie resolves Admin's default permission guard via auth.defaults.guard
        // (web) because Admin exposes getGuardName() rather than guardName().
        $permission = Permission::firstOrCreate(
            ['name' => 'view-activity-logs', 'guard_name' => 'web'],
            ['title' => 'View Activity Logs']
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin->givePermissionTo($permission);

        Sanctum::actingAs($admin, abilities: ['*'], guard: 'admin');

        return $admin;
    }
}

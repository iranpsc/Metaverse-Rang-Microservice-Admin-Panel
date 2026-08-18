<?php

namespace Tests\Feature\Version;

use App\Models\Calendar;
use App\Models\View;
use Morilog\Jalali\Jalalian;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesCalendarApiSchema;
use Tests\TestCase;

class VersionApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesCalendarApiSchema;

    private const INDEX_PATH = '/api/versions';

    private const INDEX_SUCCESS_MESSAGE = 'ورژن‌ها با موفقیت بازیابی شدند.';

    private const STORE_SUCCESS_MESSAGE = 'ورژن جدید با موفقیت ایجاد شد.';

    private const DESTROY_SUCCESS_MESSAGE = 'ورژن با موفقیت حذف شد.';

    private const DESTROY_NON_VERSION_FORBIDDEN = 'امکان حذف این ورژن وجود ندارد.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpCalendarApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_store_returns_unauthorized(): void
    {
        $this->postJson(self::INDEX_PATH, $this->validStorePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $version = Calendar::factory()->version()->create();

        $this->deleteJson($this->versionPath($version))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'Super admin version',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $toDelete = Calendar::factory()->version()->create();

        $this->deleteJson($this->versionPath($toDelete))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'Regular admin version',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true);

        $toDelete = Calendar::factory()->version()->create();

        $this->deleteJson($this->versionPath($toDelete))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Happy path / structure (index)
    // -------------------------------------------------------------------------

    public function test_empty_dataset_returns_empty_versions_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.versions', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->version()->create([
            'title' => 'Structure version',
            'version_title' => 'v1.0.0',
            'content' => 'Structure content',
            'writer' => 'Writer',
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'versions' => [
                        [
                            'id',
                            'title',
                            'version_title',
                            'content',
                            'color',
                            'btn_name',
                            'btn_link',
                            'writer',
                            'image_url',
                            'status',
                            'is_version',
                            'starts_at',
                            'ends_at',
                            'start_date',
                            'start_time',
                            'end_date',
                            'end_time',
                            'created_at',
                            'created_at_jalali',
                            'views_count',
                            'likes_count',
                            'dislikes_count',
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
            ]);
    }

    public function test_index_only_returns_version_records(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->create(['title' => 'Regular event']);
        $version = Calendar::factory()->version()->create(['title' => 'Visible version']);

        $response = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1);

        $this->assertSame($version->id, $response->json('data.versions.0.id'));
        $this->assertSame('Visible version', $response->json('data.versions.0.title'));
    }

    public function test_versions_are_ordered_by_id_desc(): void
    {
        $this->actingAsSuperAdmin();

        $first = Calendar::factory()->version()->create(['title' => 'First']);
        $second = Calendar::factory()->version()->create(['title' => 'Second']);
        $third = Calendar::factory()->version()->create(['title' => 'Third']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.versions.0.id', $third->id)
            ->assertJsonPath('data.versions.1.id', $second->id)
            ->assertJsonPath('data.versions.2.id', $first->id);
    }

    public function test_index_includes_views_count(): void
    {
        $this->actingAsSuperAdmin();

        $version = Calendar::factory()->version()->create(['title' => 'Counted']);

        View::unguarded(function () use ($version) {
            $version->views()->create([]);
            $version->views()->create([]);
            $version->views()->create([]);
        });

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.versions.0.views_count', 3);
    }

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    public function test_search_by_title(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->version()->create([
            'title' => 'UniqueTitleNeedle',
            'version_title' => 'v1',
            'content' => 'Plain',
        ]);
        Calendar::factory()->version()->create([
            'title' => 'Other title',
            'version_title' => 'v2',
            'content' => 'Plain',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=UniqueTitleNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.versions.0.title', 'UniqueTitleNeedle');
    }

    public function test_search_by_version_title(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->version()->create([
            'title' => 'A',
            'version_title' => 'UniqueVersionTitleNeedle',
            'content' => 'Plain',
        ]);
        Calendar::factory()->version()->create([
            'title' => 'B',
            'version_title' => 'Other',
            'content' => 'Plain',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=UniqueVersionTitleNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.versions.0.version_title', 'UniqueVersionTitleNeedle');
    }

    public function test_search_by_content(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->version()->create([
            'title' => 'A',
            'version_title' => 'v1',
            'content' => 'UniqueContentNeedle here',
        ]);
        Calendar::factory()->version()->create([
            'title' => 'B',
            'version_title' => 'v2',
            'content' => 'Something else',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=UniqueContentNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.versions.0.content', 'UniqueContentNeedle here');
    }

    public function test_empty_search_behaves_like_no_filter(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->version()->count(2)->create();

        $this->getJson(self::INDEX_PATH.'?search=')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_pagination_respects_per_page_and_page(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->version()->count(5)->create();

        $this->getJson(self::INDEX_PATH.'?'.http_build_query([
            'per_page' => 2,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonCount(2, 'data.versions');
    }

    public function test_pagination_defaults_to_page_one_and_ten_per_page(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->version()->count(3)->create();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 3);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_version_with_correct_fields(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $expectedStartsAt = Jalalian::fromFormat('Y/m/d', '1403/01/15')
            ->toCarbon()
            ->startOfDay();

        $response = $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'New version title',
            'content' => 'Version body text',
            'version_title' => 'v2.0.0',
            'starts_at' => '1403/01/15',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.version.title', 'New version title')
            ->assertJsonPath('data.version.content', 'Version body text')
            ->assertJsonPath('data.version.version_title', 'v2.0.0')
            ->assertJsonPath('data.version.writer', $admin->name)
            ->assertJsonPath('data.version.is_version', true)
            ->assertJsonPath('data.version.start_date', '1403/01/15')
            ->assertJsonPath('data.version.start_time', '00:00')
            ->assertJsonStructure([
                'data' => [
                    'version' => [
                        'id',
                        'title',
                        'version_title',
                        'content',
                        'writer',
                        'status',
                        'is_version',
                        'starts_at',
                        'views_count',
                        'likes_count',
                        'dislikes_count',
                    ],
                ],
            ]);

        $this->assertTrue($response->json('data.version.is_version'));
        $this->assertIsBool($response->json('data.version.is_version'));

        $this->assertDatabaseHas('calendars', [
            'title' => 'New version title',
            'content' => 'Version body text',
            'version_title' => 'v2.0.0',
            'writer' => $admin->name,
            'is_version' => 1,
        ]);

        $version = Calendar::query()->where('title', 'New version title')->first();
        $this->assertNotNull($version);
        $this->assertTrue($version->starts_at->equalTo($expectedStartsAt));
    }

    public function test_store_returns_201_with_calendar_resource_structure(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload())
            ->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'version' => [
                        'id',
                        'title',
                        'version_title',
                        'content',
                        'color',
                        'btn_name',
                        'btn_link',
                        'writer',
                        'image_url',
                        'status',
                        'is_version',
                        'starts_at',
                        'ends_at',
                        'start_date',
                        'start_time',
                        'end_date',
                        'end_time',
                        'created_at',
                        'created_at_jalali',
                        'views_count',
                        'likes_count',
                        'dislikes_count',
                    ],
                ],
            ]);
    }

    public function test_store_with_regular_admin_sets_writer_to_admin_name(): void
    {
        $admin = $this->actingAsRegularAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'Regular writer version',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.version.writer', $admin->name);

        $this->assertDatabaseHas('calendars', [
            'title' => 'Regular writer version',
            'writer' => $admin->name,
            'is_version' => 1,
        ]);
    }

    public function test_store_parses_starts_at_from_jalali_as_start_of_day(): void
    {
        $this->actingAsSuperAdmin();

        $expected = Jalalian::fromFormat('Y/m/d', '1402/12/29')
            ->toCarbon()
            ->startOfDay();

        $response = $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'Jalali start version',
            'starts_at' => '1402/12/29',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.version.start_date', '1402/12/29')
            ->assertJsonPath('data.version.start_time', '00:00');

        $this->assertSame(
            $expected->toIso8601String(),
            $response->json('data.version.starts_at')
        );
    }

    // -------------------------------------------------------------------------
    // Store validation
    // -------------------------------------------------------------------------

    public function test_store_requires_title(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validStorePayload();
        unset($payload['title']);

        $this->postJson(self::INDEX_PATH, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_requires_content(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validStorePayload();
        unset($payload['content']);

        $this->postJson(self::INDEX_PATH, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_store_requires_version_title(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validStorePayload();
        unset($payload['version_title']);

        $this->postJson(self::INDEX_PATH, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['version_title']);
    }

    public function test_store_requires_starts_at(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validStorePayload();
        unset($payload['starts_at']);

        $this->postJson(self::INDEX_PATH, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['starts_at']);
    }

    public function test_store_rejects_invalid_starts_at_format(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'starts_at' => '1403-01-15',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['starts_at']);

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'starts_at' => '15/01/1403',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['starts_at']);
    }

    public function test_store_rejects_title_exceeding_max_length(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'title' => str_repeat('a', 256),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_rejects_version_title_exceeding_max_length(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'version_title' => str_repeat('b', 256),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['version_title']);
    }

    public function test_store_rejects_content_exceeding_max_length(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'content' => str_repeat('c', 20001),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_version_successfully(): void
    {
        $this->actingAsSuperAdmin();

        $version = Calendar::factory()->version()->create(['title' => 'To delete']);

        $this->deleteJson($this->versionPath($version))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('calendars', ['id' => $version->id]);
    }

    public function test_destroy_non_version_calendar_returns_forbidden(): void
    {
        $this->actingAsSuperAdmin();

        $event = Calendar::factory()->create(['is_version' => false]);

        $this->deleteJson($this->versionPath($event))
            ->assertForbidden()
            ->assertJsonPath('message', self::DESTROY_NON_VERSION_FORBIDDEN);

        $this->assertDatabaseHas('calendars', ['id' => $event->id]);
    }

    public function test_destroy_nonexistent_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson(self::INDEX_PATH.'/999999')->assertNotFound();
    }

    public function test_destroy_does_not_delete_other_versions_or_events(): void
    {
        $this->actingAsSuperAdmin();

        $target = Calendar::factory()->version()->create(['title' => 'Target version']);
        $otherVersion = Calendar::factory()->version()->create(['title' => 'Other version']);
        $event = Calendar::factory()->create(['title' => 'Other event']);

        $this->deleteJson($this->versionPath($target))->assertOk();

        $this->assertDatabaseMissing('calendars', ['id' => $target->id]);
        $this->assertDatabaseHas('calendars', ['id' => $otherVersion->id, 'title' => 'Other version']);
        $this->assertDatabaseHas('calendars', ['id' => $event->id, 'title' => 'Other event']);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function test_index_zero_views_count_when_no_views(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->version()->create();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.versions.0.views_count', 0);
    }

    public function test_is_version_is_boolean_true_on_index_and_store(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->version()->create();

        $indexResponse = $this->getJson(self::INDEX_PATH)->assertOk();
        $this->assertTrue($indexResponse->json('data.versions.0.is_version'));
        $this->assertIsBool($indexResponse->json('data.versions.0.is_version'));

        $storeResponse = $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'Boolean version check',
        ]))->assertCreated();

        $this->assertTrue($storeResponse->json('data.version.is_version'));
        $this->assertIsBool($storeResponse->json('data.version.is_version'));
    }

    public function test_status_is_dash_dash_dash_for_versions(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->version()->create(['title' => 'Status version']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.versions.0.status', '---');

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'Status store version',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.version.status', '---');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function versionPath(Calendar $version): string
    {
        return self::INDEX_PATH.'/'.$version->id;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validStorePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Valid version title',
            'content' => 'Valid version content body',
            'version_title' => 'v1.0.0',
            'starts_at' => '1403/01/15',
        ], $overrides);
    }
}

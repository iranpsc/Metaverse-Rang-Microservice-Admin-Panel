<?php

namespace Tests\Feature\Calendar;

use App\Models\Calendar;
use App\Models\Interaction;
use App\Models\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesCalendarApiSchema;
use Tests\TestCase;

class CalendarApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesCalendarApiSchema;

    private const INDEX_PATH = '/api/calendars';

    private const INDEX_SUCCESS_MESSAGE = 'رویدادها با موفقیت بازیابی شدند.';

    private const STORE_SUCCESS_MESSAGE = 'وقعه ثبت شد.';

    private const UPDATE_SUCCESS_MESSAGE = 'وقعه ویرایش شد.';

    private const DESTROY_SUCCESS_MESSAGE = 'وقعه حذف شد.';

    private const VERSION_UPDATE_FORBIDDEN = 'امکان ویرایش این رویداد وجود ندارد.';

    private const VERSION_DESTROY_FORBIDDEN = 'امکان حذف این رویداد وجود ندارد.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpCalendarApiSchema();
        Storage::fake('public');
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
        $this->post(self::INDEX_PATH, $this->validStorePayload(), [
            'Accept' => 'application/json',
        ])->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $calendar = Calendar::factory()->create();

        $this->putJson($this->calendarPath($calendar), $this->validUpdatePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $calendar = Calendar::factory()->create();

        $this->deleteJson($this->calendarPath($calendar))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'Super admin event',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $calendar = Calendar::factory()->create(['title' => 'Updatable']);

        $this->putJson($this->calendarPath($calendar), $this->validUpdatePayload([
            'title' => 'Updated by super',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $toDelete = Calendar::factory()->create();

        $this->deleteJson($this->calendarPath($toDelete))
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

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'Regular admin event',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $calendar = Calendar::factory()->create();

        $this->putJson($this->calendarPath($calendar), $this->validUpdatePayload([
            'title' => 'Updated by regular',
        ]))->assertOk();

        $toDelete = Calendar::factory()->create();

        $this->deleteJson($this->calendarPath($toDelete))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Happy path / structure (index)
    // -------------------------------------------------------------------------

    public function test_empty_dataset_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.events', [])
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

        Calendar::factory()->ongoing()->create([
            'title' => 'Structure event',
            'content' => 'Structure content',
            'color' => '#7C3AED',
            'writer' => 'Writer',
            'btn_name' => 'Open',
            'btn_link' => 'https://example.com',
            'image' => 'https://example.com/uploads/calendars/a.jpg',
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'events' => [
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

    public function test_index_excludes_version_records(): void
    {
        $this->actingAsSuperAdmin();

        $event = Calendar::factory()->create(['title' => 'Visible event']);
        Calendar::factory()->version()->create(['title' => 'Hidden version']);

        $response = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1);

        $this->assertSame($event->id, $response->json('data.events.0.id'));
        $this->assertSame('Visible event', $response->json('data.events.0.title'));
    }

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    public function test_search_by_title(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->create(['title' => 'UniqueTitleNeedle', 'content' => 'Plain']);
        Calendar::factory()->create(['title' => 'Other title', 'content' => 'Plain']);

        $this->getJson(self::INDEX_PATH.'?search=UniqueTitleNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.events.0.title', 'UniqueTitleNeedle');
    }

    public function test_search_by_content(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->create(['title' => 'A', 'content' => 'UniqueContentNeedle here']);
        Calendar::factory()->create(['title' => 'B', 'content' => 'Something else']);

        $this->getJson(self::INDEX_PATH.'?search=UniqueContentNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.events.0.content', 'UniqueContentNeedle here');
    }

    public function test_empty_search_behaves_like_no_filter(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->count(2)->create();

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

        for ($i = 1; $i <= 5; $i++) {
            Calendar::factory()->create([
                'title' => "Event {$i}",
                'starts_at' => now()->addDays($i),
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?'.http_build_query([
            'per_page' => 2,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonCount(2, 'data.events');
    }

    public function test_pagination_defaults_to_page_one_and_ten_per_page(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->count(3)->create();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 3);
    }

    // -------------------------------------------------------------------------
    // Ordering
    // -------------------------------------------------------------------------

    public function test_events_are_ordered_by_starts_at_desc(): void
    {
        $this->actingAsSuperAdmin();

        $older = Calendar::factory()->create([
            'title' => 'Older start',
            'starts_at' => now()->subDays(2),
        ]);
        $newer = Calendar::factory()->create([
            'title' => 'Newer start',
            'starts_at' => now()->addDay(),
        ]);
        $middle = Calendar::factory()->create([
            'title' => 'Middle start',
            'starts_at' => now()->subHour(),
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.events.0.id', $newer->id)
            ->assertJsonPath('data.events.1.id', $middle->id)
            ->assertJsonPath('data.events.2.id', $older->id);
    }

    // -------------------------------------------------------------------------
    // Counts & status
    // -------------------------------------------------------------------------

    public function test_index_includes_correct_views_likes_and_dislikes_counts(): void
    {
        $this->actingAsSuperAdmin();

        $calendar = Calendar::factory()->create(['title' => 'Counted']);

        Interaction::unguarded(function () use ($calendar) {
            $calendar->interactions()->create(['liked' => true]);
            $calendar->interactions()->create(['liked' => true]);
            $calendar->interactions()->create(['liked' => false]);
        });

        View::unguarded(function () use ($calendar) {
            $calendar->views()->create([]);
            $calendar->views()->create([]);
            $calendar->views()->create([]);
            $calendar->views()->create([]);
        });

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.events.0.views_count', 4)
            ->assertJsonPath('data.events.0.likes_count', 2)
            ->assertJsonPath('data.events.0.dislikes_count', 1);
    }

    public function test_index_status_mapping_for_past_and_ongoing_events(): void
    {
        $this->actingAsSuperAdmin();

        $past = Calendar::factory()->past()->create(['title' => 'Past event']);
        $ongoing = Calendar::factory()->ongoing()->create(['title' => 'Ongoing event']);

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $byId = collect($response->json('data.events'))->keyBy('id');

        $this->assertSame('سپری شده', $byId[$past->id]['status']);
        $this->assertSame('در حال برگزاری', $byId[$ongoing->id]['status']);
    }

    public function test_index_jalali_date_time_fields_are_coherent_with_starts_and_ends(): void
    {
        $this->actingAsSuperAdmin();

        $startsAt = Jalalian::fromFormat('Y/m/d', '1403/01/15')
            ->toCarbon()
            ->setTimeFromTimeString('10:00');
        $endsAt = Jalalian::fromFormat('Y/m/d', '1403/01/15')
            ->toCarbon()
            ->setTimeFromTimeString('12:00');

        $calendar = Calendar::factory()->create([
            'title' => 'Jalali coherent',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $event = collect($response->json('data.events'))->firstWhere('id', $calendar->id);

        $this->assertSame('1403/01/15', $event['start_date']);
        $this->assertSame('10:00', $event['start_time']);
        $this->assertSame('1403/01/15', $event['end_date']);
        $this->assertSame('12:00', $event['end_time']);
        $this->assertSame($startsAt->toIso8601String(), $event['starts_at']);
        $this->assertSame($endsAt->toIso8601String(), $event['ends_at']);
        $this->assertSame(
            Jalalian::fromCarbon($calendar->created_at)->format('Y/m/d'),
            $event['created_at_jalali']
        );
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_event_with_defaults_and_admin_writer(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $response = $this->post(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'New calendar event',
            'content' => 'Event body text',
            'color' => '',
            'btn_name' => null,
            'btn_link' => null,
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.event.title', 'New calendar event')
            ->assertJsonPath('data.event.content', 'Event body text')
            ->assertJsonPath('data.event.color', '#000000')
            ->assertJsonPath('data.event.writer', $admin->name)
            ->assertJsonPath('data.event.start_date', '1403/01/15')
            ->assertJsonPath('data.event.start_time', '10:00')
            ->assertJsonPath('data.event.end_date', '1403/01/15')
            ->assertJsonPath('data.event.end_time', '12:00')
            ->assertJsonStructure([
                'data' => [
                    'event' => [
                        'id',
                        'title',
                        'image_url',
                        'views_count',
                        'likes_count',
                        'dislikes_count',
                    ],
                ],
            ]);

        $imageUrl = $response->json('data.event.image_url');
        $this->assertNotNull($imageUrl);
        $this->assertStringContainsString('/uploads/calendars/', $imageUrl);

        $path = $this->extractPublicPathFromImageUrl($imageUrl);
        Storage::disk('public')->assertExists($path);

        $this->assertDatabaseHas('calendars', [
            'title' => 'New calendar event',
            'content' => 'Event body text',
            'color' => '#000000',
            'writer' => $admin->name,
            'is_version' => 0,
        ]);
    }

    public function test_store_persists_custom_color_and_button_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'Custom fields event',
            'color' => '#06B6D4',
            'btn_name' => 'Go',
            'btn_link' => 'https://hamgit.ir',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.event.color', '#06B6D4')
            ->assertJsonPath('data.event.btn_name', 'Go')
            ->assertJsonPath('data.event.btn_link', 'https://hamgit.ir');

        $this->assertDatabaseHas('calendars', [
            'title' => 'Custom fields event',
            'color' => '#06B6D4',
            'btn_name' => 'Go',
            'btn_link' => 'https://hamgit.ir',
        ]);
    }

    public function test_store_returns_201_response_structure(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload(), [
            'Accept' => 'application/json',
        ])
            ->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'event' => [
                        'id',
                        'title',
                        'content',
                        'color',
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

    // -------------------------------------------------------------------------
    // Store validation
    // -------------------------------------------------------------------------

    public function test_store_requires_title(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validStorePayload();
        unset($payload['title']);

        $this->post(self::INDEX_PATH, $payload, ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_requires_content(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validStorePayload();
        unset($payload['content']);

        $this->post(self::INDEX_PATH, $payload, ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_store_requires_image(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validStorePayload();
        unset($payload['image']);

        $this->post(self::INDEX_PATH, $payload, ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_store_rejects_invalid_image_mime(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'image' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_store_requires_start_date(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validStorePayload();
        unset($payload['start_date']);

        $this->post(self::INDEX_PATH, $payload, ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    public function test_store_requires_end_date(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validStorePayload();
        unset($payload['end_date']);

        $this->post(self::INDEX_PATH, $payload, ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_store_rejects_invalid_date_format(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'start_date' => '1403-01-15',
            'end_date' => '15/01/1403',
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    public function test_store_requires_start_and_end_time(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validStorePayload();
        unset($payload['start_time'], $payload['end_time']);

        $this->post(self::INDEX_PATH, $payload, ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_time', 'end_time']);
    }

    public function test_store_rejects_invalid_time_format(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'start_time' => '10:00:00',
            'end_time' => '9:00',
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_time', 'end_time']);
    }

    public function test_store_rejects_too_short_title_and_content(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'A',
            'content' => 'B',
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_changes_fields_and_clears_ends_when_end_omitted(): void
    {
        $this->actingAsSuperAdmin();

        $calendar = Calendar::factory()->create([
            'title' => 'Before',
            'content' => 'Before content',
            'color' => '#111111',
            'btn_name' => 'Old',
            'btn_link' => 'https://old.example',
            'ends_at' => now()->addDays(5),
        ]);

        $this->putJson($this->calendarPath($calendar), $this->validUpdatePayload([
            'title' => 'After',
            'content' => 'After content',
            'color' => '#22C55E',
            'btn_name' => 'New',
            'btn_link' => 'https://new.example',
            // end_date / end_time intentionally omitted → ends_at null
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.event.title', 'After')
            ->assertJsonPath('data.event.content', 'After content')
            ->assertJsonPath('data.event.color', '#22C55E')
            ->assertJsonPath('data.event.btn_name', 'New')
            ->assertJsonPath('data.event.btn_link', 'https://new.example')
            ->assertJsonPath('data.event.end_date', null)
            ->assertJsonPath('data.event.end_time', null)
            ->assertJsonPath('data.event.ends_at', null);

        $this->assertDatabaseHas('calendars', [
            'id' => $calendar->id,
            'title' => 'After',
            'content' => 'After content',
            'color' => '#22C55E',
            'ends_at' => null,
        ]);
    }

    public function test_update_sets_ends_at_when_end_date_and_time_provided(): void
    {
        $this->actingAsSuperAdmin();

        $calendar = Calendar::factory()->create(['ends_at' => null]);

        $this->putJson($this->calendarPath($calendar), $this->validUpdatePayload([
            'end_date' => '1403/01/20',
            'end_time' => '18:30',
        ]))
            ->assertOk()
            ->assertJsonPath('data.event.end_date', '1403/01/20')
            ->assertJsonPath('data.event.end_time', '18:30');

        $calendar->refresh();
        $this->assertNotNull($calendar->ends_at);
        $this->assertSame('1403/01/20', Jalalian::fromCarbon($calendar->ends_at)->format('Y/m/d'));
        $this->assertSame('18:30', $calendar->ends_at->format('H:i'));
    }

    public function test_update_replaces_image_when_file_provided(): void
    {
        $this->actingAsSuperAdmin();

        $calendar = Calendar::factory()->create([
            'image' => 'https://example.com/uploads/calendars/old.jpg',
        ]);

        $response = $this->post($this->calendarPath($calendar), array_merge(
            $this->validUpdatePayload(['title' => 'With new image']),
            [
                '_method' => 'PUT',
                'image' => UploadedFile::fake()->image('new-event.png'),
            ]
        ), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.event.title', 'With new image');

        $imageUrl = $response->json('data.event.image_url');
        $this->assertStringContainsString('/uploads/calendars/', $imageUrl);
        Storage::disk('public')->assertExists($this->extractPublicPathFromImageUrl($imageUrl));
    }

    public function test_update_does_not_change_image_when_file_omitted(): void
    {
        $this->actingAsSuperAdmin();

        $originalImage = 'https://example.com/uploads/calendars/keep.jpg';
        $calendar = Calendar::factory()->create(['image' => $originalImage]);

        $this->putJson($this->calendarPath($calendar), $this->validUpdatePayload([
            'title' => 'No image change',
        ]))
            ->assertOk()
            ->assertJsonPath('data.event.image_url', $originalImage);

        $this->assertDatabaseHas('calendars', [
            'id' => $calendar->id,
            'image' => $originalImage,
        ]);
    }

    public function test_update_version_calendar_returns_forbidden(): void
    {
        $this->actingAsSuperAdmin();

        $version = Calendar::factory()->version()->create();

        $this->putJson($this->calendarPath($version), $this->validUpdatePayload())
            ->assertForbidden();
    }

    public function test_update_nonexistent_calendar_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson(self::INDEX_PATH.'/999999', $this->validUpdatePayload())
            ->assertNotFound();
    }

    public function test_update_validation_failures(): void
    {
        $this->actingAsSuperAdmin();

        $calendar = Calendar::factory()->create();

        $this->putJson($this->calendarPath($calendar), [
            'title' => 'A',
            'content' => 'B',
            'start_date' => 'bad-date',
            'start_time' => 'bad',
            'end_date' => 'also-bad',
            'end_time' => '99',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'content',
                'start_date',
                'start_time',
                'end_date',
                'end_time',
            ]);
    }

    public function test_update_requires_title_content_start_date_and_start_time(): void
    {
        $this->actingAsSuperAdmin();

        $calendar = Calendar::factory()->create();

        $this->putJson($this->calendarPath($calendar), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'content',
                'start_date',
                'start_time',
            ]);
    }

    public function test_update_defaults_empty_color_to_black(): void
    {
        $this->actingAsSuperAdmin();

        $calendar = Calendar::factory()->create(['color' => '#ABCDEF']);

        $this->putJson($this->calendarPath($calendar), $this->validUpdatePayload([
            'color' => '',
        ]))
            ->assertOk()
            ->assertJsonPath('data.event.color', '#000000');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_hard_deletes_event(): void
    {
        $this->actingAsSuperAdmin();

        $calendar = Calendar::factory()->create(['title' => 'To delete']);

        $this->deleteJson($this->calendarPath($calendar))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('calendars', ['id' => $calendar->id]);
    }

    public function test_destroy_version_returns_forbidden(): void
    {
        $this->actingAsSuperAdmin();

        $version = Calendar::factory()->version()->create();

        $this->deleteJson($this->calendarPath($version))
            ->assertForbidden();

        $this->assertDatabaseHas('calendars', ['id' => $version->id]);
    }

    public function test_destroy_nonexistent_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson(self::INDEX_PATH.'/999999')->assertNotFound();
    }

    public function test_destroy_does_not_delete_other_events(): void
    {
        $this->actingAsSuperAdmin();

        $target = Calendar::factory()->create(['title' => 'Target']);
        $other = Calendar::factory()->create(['title' => 'Other']);

        $this->deleteJson($this->calendarPath($target))->assertOk();

        $this->assertDatabaseMissing('calendars', ['id' => $target->id]);
        $this->assertDatabaseHas('calendars', ['id' => $other->id, 'title' => 'Other']);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function test_index_zero_counts_when_no_interactions_or_views(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->create();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.events.0.views_count', 0)
            ->assertJsonPath('data.events.0.likes_count', 0)
            ->assertJsonPath('data.events.0.dislikes_count', 0);
    }

    public function test_store_with_regular_admin_sets_writer_to_admin_name(): void
    {
        $admin = $this->actingAsRegularAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'title' => 'Regular writer event',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.event.writer', $admin->name);

        $this->assertDatabaseHas('calendars', [
            'title' => 'Regular writer event',
            'writer' => $admin->name,
        ]);
    }

    public function test_is_version_is_boolean_false_on_index(): void
    {
        $this->actingAsSuperAdmin();

        Calendar::factory()->create();

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $this->assertFalse($response->json('data.events.0.is_version'));
        $this->assertIsBool($response->json('data.events.0.is_version'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function calendarPath(Calendar $calendar): string
    {
        return self::INDEX_PATH.'/'.$calendar->id;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validStorePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Valid calendar title',
            'content' => 'Valid calendar content body',
            'image' => UploadedFile::fake()->image('event.jpg'),
            'start_date' => '1403/01/15',
            'end_date' => '1403/01/15',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'color' => '#7C3AED',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Updated calendar title',
            'content' => 'Updated calendar content body',
            'start_date' => '1403/01/15',
            'start_time' => '10:00',
            'color' => '#7C3AED',
        ], $overrides);
    }

    private function extractPublicPathFromImageUrl(string $imageUrl): string
    {
        $marker = '/uploads/';
        $pos = strpos($imageUrl, $marker);
        $this->assertNotFalse($pos, 'image_url should contain /uploads/');

        return substr($imageUrl, $pos + strlen($marker));
    }
}

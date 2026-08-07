<?php

namespace Tests\Feature\Report;

use App\Http\Controllers\Api\ReportController;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesCitizensApiSchema;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesCitizensApiSchema;

    private const INDEX_PATH = '/api/reports';

    private const SUCCESS_MESSAGE = 'گزارش‌ها با موفقیت بازیابی شدند.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpCitizensApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_request_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_authenticated_super_admin_receives_success_envelope(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_receives_success_envelope(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Happy path / structure
    // -------------------------------------------------------------------------

    public function test_empty_dataset_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE)
            ->assertJsonPath('data.reports', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null)
            ->assertJsonPath('data.filters.subject', 'spellingError')
            ->assertJsonPath('data.filters.search', null);
    }

    public function test_index_returns_full_json_structure(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $report = $this->createReport($user, [
            'subject' => 'spellingError',
            'title' => 'Broken typo',
        ]);
        $report->images()->create(['url' => 'reports/shot.png']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'reports' => [
                        [
                            'id',
                            'subject',
                            'title',
                            'content',
                            'url',
                            'user' => ['id', 'name', 'code'],
                            'images' => [
                                ['id', 'url'],
                            ],
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
                    'filters' => [
                        'subject',
                        'search',
                    ],
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // Filtering & search
    // -------------------------------------------------------------------------

    public function test_default_subject_is_spelling_error_when_omitted(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createReport($user, ['subject' => 'spellingError', 'title' => 'Default subject hit']);
        $this->createReport($user, ['subject' => 'fpsError', 'title' => 'Other subject']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.filters.subject', 'spellingError')
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.reports.0.title', 'Default subject hit');
    }

    public function test_filter_by_each_subject_returns_only_matching_reports(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();

        foreach (ReportController::SUBJECTS as $candidate) {
            $this->createReport($user, [
                'subject' => $candidate,
                'title' => "Report for {$candidate}",
            ]);
        }

        foreach (ReportController::SUBJECTS as $subject) {
            $response = $this->getJson(self::INDEX_PATH.'?'.http_build_query(['subject' => $subject]))
                ->assertOk()
                ->assertJsonPath('data.filters.subject', $subject)
                ->assertJsonPath('data.pagination.total', 1);

            $this->assertSame($subject, $response->json('data.reports.0.subject'));
            $this->assertSame("Report for {$subject}", $response->json('data.reports.0.title'));
        }
    }

    public function test_invalid_subject_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH.'?subject=notARealSubject')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subject']);
    }

    public function test_search_by_title(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createReport($user, ['subject' => 'spellingError', 'title' => 'UniqueTitleNeedle']);
        $this->createReport($user, ['subject' => 'spellingError', 'title' => 'Other title']);

        $this->getJson(self::INDEX_PATH.'?search=UniqueTitleNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.reports.0.title', 'UniqueTitleNeedle')
            ->assertJsonPath('data.filters.search', 'UniqueTitleNeedle');
    }

    public function test_search_by_content(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createReport($user, [
            'subject' => 'spellingError',
            'title' => 'A',
            'content' => 'contains ContentNeedle phrase',
        ]);
        $this->createReport($user, [
            'subject' => 'spellingError',
            'title' => 'B',
            'content' => 'unrelated body',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=ContentNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.reports.0.title', 'A');
    }

    public function test_search_by_url(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createReport($user, [
            'subject' => 'spellingError',
            'title' => 'URL hit',
            'url' => 'https://example.com/UrlNeedle/path',
        ]);
        $this->createReport($user, [
            'subject' => 'spellingError',
            'title' => 'URL miss',
            'url' => 'https://example.com/other',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=UrlNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.reports.0.title', 'URL hit');
    }

    public function test_search_by_numeric_id(): void
    {
        $this->actingAsSuperAdmin();

        // Code must not contain the report id digits, or LIKE user.code would inflate matches.
        $user = $this->createUser(['code' => '9999']);
        $target = $this->createReport($user, [
            'subject' => 'spellingError',
            'title' => 'By id',
            'content' => 'plain body',
            'url' => 'https://example.com/page',
        ]);
        $this->createReport($user, [
            'subject' => 'spellingError',
            'title' => 'Other',
            'content' => 'plain body',
            'url' => 'https://example.com/page',
        ]);

        $this->getJson(self::INDEX_PATH.'?search='.$target->id)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.reports.0.id', $target->id);
    }

    public function test_search_by_user_name(): void
    {
        $this->actingAsSuperAdmin();

        $matchingUser = $this->createUser(['name' => 'Searchable Reporter']);
        $otherUser = $this->createUser(['name' => 'Someone Else', 'email' => 'else@example.com', 'code' => '999']);

        $this->createReport($matchingUser, ['subject' => 'spellingError', 'title' => 'From matching user']);
        $this->createReport($otherUser, ['subject' => 'spellingError', 'title' => 'From other user']);

        $this->getJson(self::INDEX_PATH.'?search=Searchable')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.reports.0.user.name', 'Searchable Reporter');
    }

    public function test_search_by_user_code(): void
    {
        $this->actingAsSuperAdmin();

        $matchingUser = $this->createUser(['code' => '4242']);
        $otherUser = $this->createUser(['email' => 'other@example.com', 'code' => '1111']);

        $this->createReport($matchingUser, ['subject' => 'spellingError', 'title' => 'Code match']);
        $this->createReport($otherUser, ['subject' => 'spellingError', 'title' => 'Code miss']);

        $this->getJson(self::INDEX_PATH.'?search=4242')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.reports.0.user.code', '4242');
    }

    public function test_invalid_page_and_per_page_return_validation_errors(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH.'?page=0')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['page']);

        $this->getJson(self::INDEX_PATH.'?page=-1')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['page']);

        $this->getJson(self::INDEX_PATH.'?per_page=0')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);

        $this->getJson(self::INDEX_PATH.'?per_page=-5')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);

        $this->getJson(self::INDEX_PATH.'?per_page=101')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_search_longer_than_255_characters_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH.'?search='.str_repeat('a', 256))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['search']);
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_default_per_page_is_ten(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        for ($i = 0; $i < 12; $i++) {
            $this->createReport($user, [
                'subject' => 'spellingError',
                'title' => "Report {$i}",
            ]);
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 10)
            ->assertJsonCount(10, 'data.reports');
    }

    public function test_custom_per_page_and_page_work(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        for ($i = 0; $i < 5; $i++) {
            $this->createReport($user, [
                'subject' => 'spellingError',
                'title' => "Paged {$i}",
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonPath('data.pagination.from', 3)
            ->assertJsonPath('data.pagination.to', 4)
            ->assertJsonCount(2, 'data.reports');
    }

    // -------------------------------------------------------------------------
    // Resource shaping
    // -------------------------------------------------------------------------

    public function test_resource_includes_user_jalali_dates_and_images(): void
    {
        $this->actingAsSuperAdmin();

        $createdAt = Carbon::parse('2024-03-20 14:30:45');
        Carbon::setTestNow($createdAt);

        $user = $this->createUser(['name' => 'Reporter', 'code' => '777']);
        $report = $this->createReport($user, [
            'subject' => 'spellingError',
            'title' => 'Shaped report',
            'content' => 'Body',
            'url' => 'https://example.com/page',
        ]);
        $report->images()->create(['url' => 'relative/path.png']);

        $jalali = Jalalian::fromDateTime($createdAt);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.reports.0.id', $report->id)
            ->assertJsonPath('data.reports.0.user.id', $user->id)
            ->assertJsonPath('data.reports.0.user.name', 'Reporter')
            ->assertJsonPath('data.reports.0.user.code', '777')
            ->assertJsonPath('data.reports.0.created_at_jalali', $jalali->format('Y/m/d'))
            ->assertJsonPath('data.reports.0.created_at_time', $jalali->format('H:i:s'))
            ->assertJsonCount(1, 'data.reports.0.images');

        Carbon::setTestNow();
    }

    public function test_relative_image_url_gets_api_url_prefix(): void
    {
        $this->actingAsSuperAdmin();

        putenv('API_URL=https://cdn.test');
        $_ENV['API_URL'] = 'https://cdn.test';
        $_SERVER['API_URL'] = 'https://cdn.test';

        $user = $this->createUser();
        $report = $this->createReport($user, ['subject' => 'spellingError']);
        $report->images()->create(['url' => '/shots/a.png']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.reports.0.images.0.url', 'https://cdn.test/uploads/shots/a.png');

        putenv('API_URL');
        unset($_ENV['API_URL'], $_SERVER['API_URL']);
    }

    public function test_absolute_http_image_url_is_left_as_is(): void
    {
        $this->actingAsSuperAdmin();

        $absolute = 'https://images.example.com/file.jpg';
        $user = $this->createUser();
        $report = $this->createReport($user, ['subject' => 'spellingError']);
        $report->images()->create(['url' => $absolute]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.reports.0.images.0.url', $absolute);
    }

    // -------------------------------------------------------------------------
    // Ordering
    // -------------------------------------------------------------------------

    public function test_reports_are_ordered_by_created_at_desc(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();

        $older = $this->createReport($user, [
            'subject' => 'spellingError',
            'title' => 'Older',
        ]);
        $older->forceFill(['created_at' => now()->subDays(2)])->save();

        $newer = $this->createReport($user, [
            'subject' => 'spellingError',
            'title' => 'Newer',
        ]);
        $newer->forceFill(['created_at' => now()->subHour()])->save();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.reports.0.id', $newer->id)
            ->assertJsonPath('data.reports.1.id', $older->id);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Citizen '.Str::random(5),
            'email' => Str::uuid().'@example.com',
            'code' => (string) random_int(1000, 9999),
            'password' => 'secret',
            'ip' => '127.0.0.1',
        ], $overrides));
    }

    private function createReport(User $user, array $overrides = []): Report
    {
        return Report::create(array_merge([
            'subject' => 'spellingError',
            'title' => 'Report title',
            'content' => 'Report content',
            'url' => 'https://example.com/report',
            'user_id' => $user->id,
        ], $overrides));
    }
}

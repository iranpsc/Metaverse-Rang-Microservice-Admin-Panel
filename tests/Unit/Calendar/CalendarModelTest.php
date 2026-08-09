<?php

namespace Tests\Unit\Calendar;

use App\Models\Calendar;
use App\Models\Interaction;
use App\Models\View;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tests\Concerns\CreatesCalendarApiSchema;
use Tests\TestCase;

class CalendarModelTest extends TestCase
{
    use CreatesCalendarApiSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpCalendarApiSchema();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function test_scope_event_returns_only_non_version_calendars(): void
    {
        $event = Calendar::factory()->create(['title' => 'Real event']);
        Calendar::factory()->version()->create(['title' => 'Version row']);

        $results = Calendar::query()->event()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($event));
    }

    public function test_scope_version_returns_only_version_calendars(): void
    {
        Calendar::factory()->create(['title' => 'Real event']);
        $version = Calendar::factory()->version()->create(['title' => 'Version row']);

        $results = Calendar::query()->version()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($version));
    }

    // -------------------------------------------------------------------------
    // getStatus
    // -------------------------------------------------------------------------

    public function test_get_status_returns_dashes_for_version(): void
    {
        $calendar = Calendar::factory()->version()->make();

        $this->assertSame('---', $calendar->getStatus());
    }

    public function test_get_status_returns_past_when_ends_at_before_now(): void
    {
        $calendar = Calendar::factory()->past()->make();

        $this->assertSame('سپری شده', $calendar->getStatus());
    }

    public function test_get_status_returns_ongoing_when_ends_at_after_now(): void
    {
        $calendar = Calendar::factory()->ongoing()->make();

        $this->assertSame('در حال برگزاری', $calendar->getStatus());
    }

    public function test_get_status_treats_null_ends_at_as_past(): void
    {
        $calendar = Calendar::factory()->make([
            'is_version' => false,
            'ends_at' => null,
        ]);

        $this->assertSame('سپری شده', $calendar->getStatus());
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_interactions_relation_is_morph_many(): void
    {
        $calendar = Calendar::factory()->create();

        $this->assertInstanceOf(MorphMany::class, $calendar->interactions());
        $this->assertSame(Interaction::class, $calendar->interactions()->getRelated()::class);
    }

    public function test_views_relation_is_morph_many(): void
    {
        $calendar = Calendar::factory()->create();

        $this->assertInstanceOf(MorphMany::class, $calendar->views());
        $this->assertSame(View::class, $calendar->views()->getRelated()::class);
    }

    public function test_interactions_and_views_can_be_persisted_via_morph(): void
    {
        $calendar = Calendar::factory()->create();

        Interaction::unguarded(function () use ($calendar) {
            $calendar->interactions()->create(['liked' => true]);
            $calendar->interactions()->create(['liked' => false]);
        });

        View::unguarded(function () use ($calendar) {
            $calendar->views()->create([]);
            $calendar->views()->create([]);
            $calendar->views()->create([]);
        });

        $this->assertSame(2, $calendar->interactions()->count());
        $this->assertSame(3, $calendar->views()->count());
        $this->assertSame(1, $calendar->interactions()->where('liked', 1)->count());
        $this->assertSame(1, $calendar->interactions()->where('liked', 0)->count());
    }
}

<?php

namespace Tests\Feature\ContentWebhooks;

use App\Models\Calendar;
use App\Services\ContentWebhooks\DeletionContext;
use App\Services\ContentWebhooks\RouteMapper;
use App\Services\Videos\VideoCategoryService;
use Tests\Concerns\CreatesVideosApiSchema;
use Tests\TestCase;

class RouteMapperTest extends TestCase
{
    use CreatesVideosApiSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpVideosApiSchema();
    }

    public function test_video_mapping_uses_slug_and_containers_but_not_interactions_until_delete(): void
    {
        $category = $this->createVideoCategory(['slug' => 'learn']);
        $sub = $this->createVideoSubCategory($category, ['slug' => 'start']);
        $video = $this->createVideo($sub, ['slug' => 'hello']);
        $mapper = app(RouteMapper::class);
        $routes = $mapper->map($video, 'updated')['affected_routes'];
        foreach (['/api/tutorials/hello', '/api/tutorials/search', '/api/tutorials/categories/learn/videos', '/api/tutorials/categories/learn/start'] as $route) {
            $this->assertContains($route, $routes);
        }
        $this->assertNotContains('/api/tutorials/'.$video->id.'/interactions', $routes);
        $this->assertContains('/api/tutorials/'.$video->id.'/interactions', $mapper->map($video, 'deleted')['affected_routes']);
    }

    public function test_moving_and_renaming_subcategory_retains_old_targets(): void
    {
        $old = $this->createVideoCategory(['slug' => 'old']);
        $new = $this->createVideoCategory(['slug' => 'new']);
        $sub = $this->createVideoSubCategory($old, ['slug' => 'before']);
        // Eloquent observers run after persistence but before syncOriginal.
        $sub->video_category_id = $new->id;
        $sub->slug = 'after';
        $routes = app(RouteMapper::class)->map($sub, 'updated')['affected_routes'];
        foreach (['/api/tutorials/categories/old/before', '/api/tutorials/categories/new/after', '/api/tutorials/categories/old/videos', '/api/tutorials/categories/new/videos'] as $route) {
            $this->assertContains($route, $routes);
        }
    }

    public function test_category_bulk_delete_keeps_descendant_routes_in_snapshot(): void
    {
        $category = $this->createVideoCategory(['slug' => 'learn']);
        $sub = $this->createVideoSubCategory($category, ['slug' => 'start']);
        $this->createVideo($sub, ['slug' => 'hello']);
        app(VideoCategoryService::class)->delete($category);
        $snapshot = app(DeletionContext::class)->get($category);
        $this->assertContains('/api/tutorials/hello', $snapshot['affected_routes']);
        $this->assertContains('/api/tutorials/categories/learn/start', $snapshot['affected_routes']);
    }

    public function test_versions_have_latest_version_target_but_calendar_events_do_not(): void
    {
        $calendar = new Calendar(['id' => 3, 'title' => 'Release', 'is_version' => true]);
        $mapping = app(RouteMapper::class)->map($calendar, 'created');
        $this->assertSame('versions', $mapping['category']);
        $this->assertContains('/api/calendar/latest-version', $mapping['affected_routes']);
        $calendar->is_version = false;
        $this->assertNotContains('/api/calendar/latest-version', app(RouteMapper::class)->map($calendar, 'created')['affected_routes']);
    }

    public function test_category_slug_change_invalidates_old_and_new_descendant_urls(): void
    {
        $category = $this->createVideoCategory(['slug' => 'before']);
        $this->createVideoSubCategory($category, ['slug' => 'child']);
        $category->slug = 'after';
        $routes = app(RouteMapper::class)->map($category, 'updated')['affected_routes'];
        $this->assertContains('/api/tutorials/categories/before/child', $routes);
        $this->assertContains('/api/tutorials/categories/after/child', $routes);
    }
}

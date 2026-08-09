<?php

namespace Tests\Feature\Videos;

use App\Models\Interaction;
use App\Models\Video;
use App\Models\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesVideosApiSchema;
use Tests\TestCase;

class VideosApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesVideosApiSchema;

    private const INDEX_PATH = '/api/videos';

    private const META_PATH = '/api/videos/meta';

    private const INDEX_SUCCESS_MESSAGE = 'فهرست ویدیوها با موفقیت دریافت شد.';

    private const STORE_SUCCESS_MESSAGE = 'ویدیو با موفقیت ایجاد شد.';

    private const UPDATE_SUCCESS_MESSAGE = 'ویدیو با موفقیت به روزرسانی شد.';

    private const DESTROY_SUCCESS_MESSAGE = 'ویدیو با موفقیت حذف شد.';

    private const META_SUCCESS_MESSAGE = 'اطلاعات کمکی ویدیوها با موفقیت دریافت شد.';

    private const MISSING_UPLOAD_MESSAGE = 'ویدیو بارگذاری نشده است.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpVideosApiSchema();
    }

    private function videoPath(int|Video $video): string
    {
        $id = $video instanceof Video ? $video->id : $video;

        return self::INDEX_PATH.'/'.$id;
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_meta_returns_unauthorized(): void
    {
        $this->getJson(self::META_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_store_returns_unauthorized(): void
    {
        $this->post(self::INDEX_PATH, $this->validVideoStorePayload(), ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $video = $this->createVideo();

        $this->putJson($this->videoPath($video), $this->validVideoUpdatePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $video = $this->createVideo();

        $this->deleteJson($this->videoPath($video))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory(['slug' => 'meta-cat', 'name' => 'Meta Cat']);
        $sub = $this->createVideoSubCategory($category, ['slug' => 'meta-sub', 'name' => 'Meta Sub']);
        $creator = $this->createCreatorUser(['code' => 'CREATOR1']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->getJson(self::META_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::META_SUCCESS_MESSAGE);

        $response = $this->post(self::INDEX_PATH, $this->validVideoStorePayload($category, $sub, $creator, [
            'title' => 'Super Video',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $id = $response->json('data.id');

        $this->putJson($this->videoPath($id), $this->validVideoUpdatePayload([
            'title' => 'Super Updated',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->deleteJson($this->videoPath($id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $category = $this->createVideoCategory();
        $sub = $this->createVideoSubCategory($category);
        $creator = $this->createCreatorUser();

        $this->getJson(self::INDEX_PATH)->assertOk();
        $this->getJson(self::META_PATH)->assertOk();

        $response = $this->post(self::INDEX_PATH, $this->validVideoStorePayload($category, $sub, $creator), [
            'Accept' => 'application/json',
        ])->assertCreated();

        $id = $response->json('data.id');

        $this->putJson($this->videoPath($id), $this->validVideoUpdatePayload())->assertOk();
        $this->deleteJson($this->videoPath($id))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Meta
    // -------------------------------------------------------------------------

    public function test_meta_returns_categories_with_nested_sub_categories_ordered_by_name(): void
    {
        $this->actingAsSuperAdmin();

        $beta = $this->createVideoCategory(['name' => 'Beta', 'slug' => 'beta']);
        $alpha = $this->createVideoCategory(['name' => 'Alpha', 'slug' => 'alpha']);
        $this->createVideoSubCategory($alpha, ['name' => 'A2', 'slug' => 'a2']);
        $this->createVideoSubCategory($alpha, ['name' => 'A1', 'slug' => 'a1']);
        $this->createVideoSubCategory($beta, ['name' => 'B1', 'slug' => 'b1']);

        $response = $this->getJson(self::META_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::META_SUCCESS_MESSAGE)
            ->assertJsonCount(2, 'data.categories');

        $this->assertSame('Alpha', $response->json('data.categories.0.name'));
        $this->assertSame('Beta', $response->json('data.categories.1.name'));
        $this->assertCount(2, $response->json('data.categories.0.sub_categories'));
        $this->assertSame(['id', 'name', 'slug'], array_keys($response->json('data.categories.0.sub_categories.0')));
    }

    public function test_meta_returns_empty_categories_when_none_exist(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::META_PATH)
            ->assertOk()
            ->assertJsonPath('data.categories', []);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.videos', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure_with_counts(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory(['name' => 'Parent', 'slug' => 'parent']);
        $sub = $this->createVideoSubCategory($category, ['name' => 'Child', 'slug' => 'child']);
        $video = $this->createVideo($sub, ['title' => 'Counted Video']);

        View::unguarded(function () use ($video) {
            $video->views()->create([]);
            $video->views()->create([]);
        });

        Interaction::unguarded(function () use ($video) {
            $video->interactions()->create(['liked' => 1]);
            $video->interactions()->create(['liked' => 1]);
            $video->interactions()->create(['liked' => 0]);
        });

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'videos' => [
                        [
                            'id',
                            'title',
                            'slug',
                            'description',
                            'creator_code',
                            'file_name',
                            'file_url',
                            'image',
                            'image_url',
                            'category' => [
                                'id',
                                'name',
                                'slug',
                                'parent' => ['id', 'name', 'slug'],
                            ],
                            'views_count',
                            'likes_count',
                            'dislikes_count',
                            'created_at',
                            'created_at_formatted' => ['date', 'time'],
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
            ->assertJsonPath('data.videos.0.views_count', 2)
            ->assertJsonPath('data.videos.0.likes_count', 2)
            ->assertJsonPath('data.videos.0.dislikes_count', 1)
            ->assertJsonPath('data.videos.0.category.parent.name', 'Parent');
    }

    public function test_index_orders_by_created_at_desc(): void
    {
        $this->actingAsSuperAdmin();

        $sub = $this->createVideoSubCategory();
        $first = $this->createVideo($sub, ['title' => 'First']);
        $this->travel(1)->seconds();
        $second = $this->createVideo($sub, ['title' => 'Second']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.videos.0.id', $second->id)
            ->assertJsonPath('data.videos.1.id', $first->id);
    }

    public function test_index_defaults_invalid_per_page_to_ten(): void
    {
        $this->actingAsSuperAdmin();

        $this->createVideo();

        $this->getJson(self::INDEX_PATH.'?per_page=0')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10);

        $this->getJson(self::INDEX_PATH.'?per_page=-5')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10);
    }

    public function test_index_search_filters_by_title(): void
    {
        $this->actingAsSuperAdmin();

        $sub = $this->createVideoSubCategory();
        $this->createVideo($sub, ['title' => 'Needle Title']);
        $this->createVideo($sub, ['title' => 'Other Video']);

        $this->getJson(self::INDEX_PATH.'?search=Needle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.videos.0.title', 'Needle Title');
    }

    public function test_index_filters_by_category_and_sub_category(): void
    {
        $this->actingAsSuperAdmin();

        $catA = $this->createVideoCategory(['slug' => 'cat-a']);
        $catB = $this->createVideoCategory(['slug' => 'cat-b']);
        $subA = $this->createVideoSubCategory($catA, ['slug' => 'sub-a']);
        $subB = $this->createVideoSubCategory($catB, ['slug' => 'sub-b']);
        $this->createVideo($subA, ['title' => 'Video A']);
        $this->createVideo($subB, ['title' => 'Video B']);

        $this->getJson(self::INDEX_PATH.'?video_category_id='.$catA->id)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.videos.0.title', 'Video A');

        $this->getJson(self::INDEX_PATH.'?video_sub_category_id='.$subB->id)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.videos.0.title', 'Video B');
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_video_moves_temp_file_and_lowercases_creator_code(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory(['slug' => 'tutorials']);
        $sub = $this->createVideoSubCategory($category, ['slug' => 'basics']);
        $creator = $this->createCreatorUser(['code' => 'AbC123']);
        $fileName = $this->putResumableVideoFile('intro.mp4', 'video-content');

        $response = $this->post(self::INDEX_PATH, $this->validVideoStorePayload($category, $sub, $creator, [
            'title' => 'Intro',
            'description' => 'Intro description',
            'video_file_name' => $fileName,
            'creator_code' => 'AbC123',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.title', 'Intro')
            ->assertJsonPath('data.creator_code', 'abc123')
            ->assertJsonPath('data.category.id', $sub->id)
            ->assertJsonPath('data.category.parent.id', $category->id);

        $storedPath = $response->json('data.file_name');
        $this->assertSame('tutorials/tutorials/basics/intro.mp4', $storedPath);
        Storage::disk('public')->assertExists($storedPath);
        Storage::disk('local')->assertMissing('resumable-tmp/intro.mp4');
        Storage::disk('public')->assertExists($response->json('data.image'));

        $this->assertDatabaseHas('videos', [
            'id' => $response->json('data.id'),
            'title' => 'Intro',
            'creator_code' => 'abc123',
            'video_sub_category_id' => $sub->id,
        ]);
    }

    public function test_store_returns_422_when_resumable_file_missing(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory();
        $sub = $this->createVideoSubCategory($category);
        $creator = $this->createCreatorUser();

        $this->post(self::INDEX_PATH, $this->validVideoStorePayload($category, $sub, $creator, [
            'video_file_name' => 'missing-file.mp4',
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', self::MISSING_UPLOAD_MESSAGE);
    }

    public function test_store_rejects_mismatched_category_and_sub_category(): void
    {
        $this->actingAsSuperAdmin();

        $catA = $this->createVideoCategory(['slug' => 'a']);
        $catB = $this->createVideoCategory(['slug' => 'b']);
        $subB = $this->createVideoSubCategory($catB);
        $creator = $this->createCreatorUser();

        $this->post(self::INDEX_PATH, $this->validVideoStorePayload($catA, $subB, $creator, [
            'video_category_id' => $catA->id,
            'video_sub_category_id' => $subB->id,
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_sub_category_id']);
    }

    public function test_store_validation_requires_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'description',
                'video_category_id',
                'video_sub_category_id',
                'image',
                'video_file_name',
                'creator_code',
            ]);
    }

    public function test_store_rejects_unknown_creator_code(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory();
        $sub = $this->createVideoSubCategory($category);

        $this->post(self::INDEX_PATH, $this->validVideoStorePayload($category, $sub, null, [
            'creator_code' => 'NOPE999',
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['creator_code']);
    }

    public function test_store_rejects_non_image_and_oversized_image(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory();
        $sub = $this->createVideoSubCategory($category);
        $creator = $this->createCreatorUser();

        $this->post(self::INDEX_PATH, $this->validVideoStorePayload($category, $sub, $creator, [
            'image' => UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf'),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image']);

        $this->post(self::INDEX_PATH, $this->validVideoStorePayload($category, $sub, $creator, [
            'image' => UploadedFile::fake()->image('big.jpg')->size(1025),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_store_rejects_oversized_title_and_description(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory();
        $sub = $this->createVideoSubCategory($category);
        $creator = $this->createCreatorUser();

        $this->post(self::INDEX_PATH, $this->validVideoStorePayload($category, $sub, $creator, [
            'title' => str_repeat('t', 256),
            'description' => str_repeat('d', 20001),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_changes_title_and_description(): void
    {
        $this->actingAsSuperAdmin();

        $video = $this->createVideo(null, [
            'title' => 'Old',
            'description' => 'Old desc',
            'creator_code' => 'oldcode',
        ]);

        $this->putJson($this->videoPath($video), $this->validVideoUpdatePayload([
            'title' => 'New Title',
            'description' => 'New description',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.title', 'New Title')
            ->assertJsonPath('data.description', 'New description')
            ->assertJsonPath('data.creator_code', 'oldcode');
    }

    public function test_update_lowercases_creator_code_when_provided(): void
    {
        $this->actingAsSuperAdmin();

        $video = $this->createVideo(null, ['creator_code' => 'old']);
        $creator = $this->createCreatorUser(['code' => 'NewCode']);

        $this->putJson($this->videoPath($video), $this->validVideoUpdatePayload([
            'creator_code' => 'NewCode',
        ]))
            ->assertOk()
            ->assertJsonPath('data.creator_code', 'newcode');

        $this->assertDatabaseHas('videos', [
            'id' => $video->id,
            'creator_code' => 'newcode',
        ]);
        $this->assertNotNull($creator->id);
    }

    public function test_update_replaces_image_and_video_file(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory(['slug' => 'cat']);
        $sub = $this->createVideoSubCategory($category, ['slug' => 'sub']);

        Storage::disk('public')->put('tutorials/cat/sub/old.mp4', 'old-video');
        Storage::disk('public')->put('tutorials/cat/sub/old.jpg', 'old-image');

        $video = $this->createVideo($sub, [
            'fileName' => 'tutorials/cat/sub/old.mp4',
            'image' => 'tutorials/cat/sub/old.jpg',
        ]);

        $newFile = $this->putResumableVideoFile('replacement.mp4', 'new-video');

        $response = $this->post($this->videoPath($video), array_merge(
            $this->validVideoUpdatePayload(['title' => 'Replaced Media']),
            [
                '_method' => 'PUT',
                'image' => UploadedFile::fake()->image('new-thumb.jpg'),
                'video_file_name' => $newFile,
            ]
        ), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Replaced Media');

        Storage::disk('public')->assertMissing('tutorials/cat/sub/old.mp4');
        Storage::disk('public')->assertMissing('tutorials/cat/sub/old.jpg');
        Storage::disk('public')->assertExists($response->json('data.file_name'));
        Storage::disk('public')->assertExists($response->json('data.image'));
        Storage::disk('local')->assertMissing('resumable-tmp/replacement.mp4');
    }

    public function test_update_returns_422_when_replacement_video_missing(): void
    {
        $this->actingAsSuperAdmin();

        $video = $this->createVideo();

        $this->putJson($this->videoPath($video), $this->validVideoUpdatePayload([
            'video_file_name' => 'does-not-exist.mp4',
        ]))
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::MISSING_UPLOAD_MESSAGE);
    }

    public function test_update_validation_requires_title_and_description(): void
    {
        $this->actingAsSuperAdmin();

        $video = $this->createVideo();

        $this->putJson($this->videoPath($video), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description']);
    }

    public function test_update_rejects_unknown_creator_code(): void
    {
        $this->actingAsSuperAdmin();

        $video = $this->createVideo();

        $this->putJson($this->videoPath($video), $this->validVideoUpdatePayload([
            'creator_code' => 'MISSING',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['creator_code']);
    }

    public function test_update_returns_404_for_missing_video(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->videoPath(99999), $this->validVideoUpdatePayload())
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_video_and_assets(): void
    {
        $this->actingAsSuperAdmin();

        Storage::disk('public')->put('tutorials/del/video.mp4', 'video');
        Storage::disk('public')->put('tutorials/del/thumb.jpg', 'thumb');

        $video = $this->createVideo(null, [
            'fileName' => 'tutorials/del/video.mp4',
            'image' => 'tutorials/del/thumb.jpg',
        ]);

        $this->deleteJson($this->videoPath($video))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('videos', ['id' => $video->id]);
        Storage::disk('public')->assertMissing('tutorials/del/video.mp4');
        Storage::disk('public')->assertMissing('tutorials/del/thumb.jpg');
    }

    public function test_destroy_returns_404_for_missing_video(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson($this->videoPath(99999))->assertNotFound();
    }

    public function test_show_route_is_not_registered(): void
    {
        $routes = collect(app('router')->getRoutes())
            ->filter(fn ($route) => $route->uri() === 'api/videos/{video}')
            ->flatMap(fn ($route) => $route->methods())
            ->filter(fn ($method) => $method !== 'HEAD')
            ->values()
            ->all();

        $this->assertNotContains('GET', $routes);
        $this->assertContains('PUT', $routes);
        $this->assertContains('DELETE', $routes);
    }
}

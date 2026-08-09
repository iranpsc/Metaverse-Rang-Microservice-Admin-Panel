<?php

namespace Tests\Feature\Videos;

use App\Models\VideoSubCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesVideosApiSchema;
use Tests\TestCase;

class VideoSubCategoriesApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesVideosApiSchema;

    private const INDEX_PATH = '/api/video-sub-categories';

    private const INDEX_SUCCESS_MESSAGE = 'زیر دسته ها با موفقیت دریافت شد.';

    private const STORE_SUCCESS_MESSAGE = 'زیر دسته با موفقیت ایجاد شد.';

    private const UPDATE_SUCCESS_MESSAGE = 'زیر دسته با موفقیت به روزرسانی شد.';

    private const DESTROY_SUCCESS_MESSAGE = 'زیر دسته با موفقیت حذف شد.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpVideosApiSchema();
    }

    private function subCategoryPath(int|VideoSubCategory $subCategory): string
    {
        $id = $subCategory instanceof VideoSubCategory ? $subCategory->id : $subCategory;

        return self::INDEX_PATH.'/'.$id;
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
        $this->post(self::INDEX_PATH, $this->validVideoSubCategoryStorePayload(), ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $sub = $this->createVideoSubCategory();

        $this->putJson($this->subCategoryPath($sub), $this->validVideoSubCategoryUpdatePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $sub = $this->createVideoSubCategory();

        $this->deleteJson($this->subCategoryPath($sub))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();
        $category = $this->createVideoCategory(['slug' => 'super-parent']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $response = $this->post(self::INDEX_PATH, $this->validVideoSubCategoryStorePayload($category, [
            'name' => 'Super Sub',
            'slug' => 'super-sub',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $id = $response->json('data.id');

        $this->putJson($this->subCategoryPath($id), $this->validVideoSubCategoryUpdatePayload($category, [
            'name' => 'Super Sub Updated',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->deleteJson($this->subCategoryPath($id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();
        $category = $this->createVideoCategory();

        $this->getJson(self::INDEX_PATH)->assertOk();

        $response = $this->post(self::INDEX_PATH, $this->validVideoSubCategoryStorePayload($category, [
            'slug' => 'regular-sub',
        ]), ['Accept' => 'application/json'])
            ->assertCreated();

        $id = $response->json('data.id');

        $this->putJson($this->subCategoryPath($id), $this->validVideoSubCategoryUpdatePayload($category))
            ->assertOk();

        $this->deleteJson($this->subCategoryPath($id))->assertOk();
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
            ->assertJsonPath('data.sub_categories', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure_with_category(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory(['name' => 'Parent', 'slug' => 'parent']);
        $this->createVideoSubCategory($category, ['name' => 'Child', 'slug' => 'child']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'sub_categories' => [
                        [
                            'id',
                            'video_category_id',
                            'name',
                            'slug',
                            'description',
                            'image',
                            'image_url',
                            'icon',
                            'icon_url',
                            'created_at',
                            'created_at_formatted' => ['date', 'time'],
                            'category' => ['id', 'name', 'slug'],
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
            ->assertJsonPath('data.sub_categories.0.category.name', 'Parent');
    }

    public function test_index_filters_by_video_category_id(): void
    {
        $this->actingAsSuperAdmin();

        $catA = $this->createVideoCategory(['slug' => 'cat-a']);
        $catB = $this->createVideoCategory(['slug' => 'cat-b']);
        $this->createVideoSubCategory($catA, ['name' => 'A Sub']);
        $this->createVideoSubCategory($catB, ['name' => 'B Sub']);

        $this->getJson(self::INDEX_PATH.'?video_category_id='.$catA->id)
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.sub_categories.0.name', 'A Sub')
            ->assertJsonPath('data.sub_categories.0.video_category_id', $catA->id);
    }

    public function test_index_search_matches_name_and_slug(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory();
        $this->createVideoSubCategory($category, ['name' => 'Unique Sub Name', 'slug' => 'plain-slug']);
        $this->createVideoSubCategory($category, ['name' => 'Other', 'slug' => 'unique-sub-slug']);

        $this->getJson(self::INDEX_PATH.'?search=Unique+Sub+Name')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.sub_categories.0.name', 'Unique Sub Name');

        $this->getJson(self::INDEX_PATH.'?search=unique-sub-slug')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.sub_categories.0.slug', 'unique-sub-slug');
    }

    public function test_index_respects_per_page(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory();
        for ($i = 0; $i < 4; $i++) {
            $this->createVideoSubCategory($category, ['slug' => 'sub-page-'.$i]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.total', 4)
            ->assertJsonCount(2, 'data.sub_categories');
    }

    public function test_index_orders_by_created_at_desc(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory();
        $first = $this->createVideoSubCategory($category, ['name' => 'First']);
        $this->travel(1)->seconds();
        $second = $this->createVideoSubCategory($category, ['name' => 'Second']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.sub_categories.0.id', $second->id)
            ->assertJsonPath('data.sub_categories.1.id', $first->id);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_sub_category_and_stores_files(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory(['slug' => 'parent-cat']);

        $response = $this->post(self::INDEX_PATH, $this->validVideoSubCategoryStorePayload($category, [
            'name' => 'New Sub',
            'slug' => 'new-sub',
            'description' => 'Sub description',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.name', 'New Sub')
            ->assertJsonPath('data.slug', 'new-sub')
            ->assertJsonPath('data.video_category_id', $category->id)
            ->assertJsonPath('data.category.id', $category->id);

        $image = $response->json('data.image');
        $icon = $response->json('data.icon');
        Storage::disk('public')->assertExists($image);
        Storage::disk('public')->assertExists($icon);
        $this->assertStringContainsString('tutorials/parent-cat/new-sub/', $image);

        $this->assertDatabaseHas('video_sub_categories', [
            'id' => $response->json('data.id'),
            'video_category_id' => $category->id,
            'slug' => 'new-sub',
        ]);
    }

    public function test_store_validation_requires_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'video_category_id',
                'name',
                'slug',
                'description',
                'image',
                'icon',
            ]);
    }

    public function test_store_rejects_missing_parent_category(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validVideoSubCategoryStorePayload(
            $this->createVideoCategory(),
            ['video_category_id' => 99999]
        ), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_category_id']);
    }

    public function test_store_rejects_non_integer_category_id(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validVideoSubCategoryStorePayload(
            $this->createVideoCategory(),
            ['video_category_id' => 'abc']
        ), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_category_id']);
    }

    public function test_store_rejects_invalid_image_and_icon(): void
    {
        $this->actingAsSuperAdmin();
        $category = $this->createVideoCategory();

        $this->post(self::INDEX_PATH, $this->validVideoSubCategoryStorePayload($category, [
            'image' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            'icon' => UploadedFile::fake()->create('icon.pdf', 20, 'application/pdf'),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image', 'icon']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_changes_fields_and_can_move_category(): void
    {
        $this->actingAsSuperAdmin();

        $oldCategory = $this->createVideoCategory(['slug' => 'old-parent']);
        $newCategory = $this->createVideoCategory(['slug' => 'new-parent']);
        $sub = $this->createVideoSubCategory($oldCategory, [
            'name' => 'Old Sub',
            'slug' => 'move-sub',
        ]);

        $this->putJson($this->subCategoryPath($sub), $this->validVideoSubCategoryUpdatePayload($newCategory, [
            'name' => 'Moved Sub',
            'slug' => 'moved-sub',
            'description' => 'Moved description',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.name', 'Moved Sub')
            ->assertJsonPath('data.slug', 'moved-sub')
            ->assertJsonPath('data.video_category_id', $newCategory->id)
            ->assertJsonPath('data.category.id', $newCategory->id);
    }

    public function test_update_replaces_files_when_provided(): void
    {
        $this->actingAsSuperAdmin();

        Storage::disk('public')->put('tutorials/parent/sub/old.jpg', 'old');
        Storage::disk('public')->put('tutorials/parent/sub/old.svg', 'old');

        $category = $this->createVideoCategory(['slug' => 'parent']);
        $sub = $this->createVideoSubCategory($category, [
            'slug' => 'sub',
            'image' => 'tutorials/parent/sub/old.jpg',
            'icon' => 'tutorials/parent/sub/old.svg',
        ]);

        $response = $this->post($this->subCategoryPath($sub), array_merge(
            $this->validVideoSubCategoryUpdatePayload($category, ['name' => 'With Files']),
            [
                '_method' => 'PUT',
                'image' => UploadedFile::fake()->image('fresh.jpg'),
                'icon' => $this->fakeSvg('fresh.svg'),
            ]
        ), ['Accept' => 'application/json'])
            ->assertOk();

        Storage::disk('public')->assertMissing('tutorials/parent/sub/old.jpg');
        Storage::disk('public')->assertMissing('tutorials/parent/sub/old.svg');
        Storage::disk('public')->assertExists($response->json('data.image'));
        Storage::disk('public')->assertExists($response->json('data.icon'));
    }

    public function test_update_keeps_slug_when_omitted(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory();
        $sub = $this->createVideoSubCategory($category, ['slug' => 'keep-me']);

        $this->putJson($this->subCategoryPath($sub), $this->validVideoSubCategoryUpdatePayload($category, [
            'name' => 'Renamed Only',
        ]))
            ->assertOk()
            ->assertJsonPath('data.slug', 'keep-me')
            ->assertJsonPath('data.name', 'Renamed Only');
    }

    public function test_update_validation_requires_core_fields(): void
    {
        $this->actingAsSuperAdmin();

        $sub = $this->createVideoSubCategory();

        $this->putJson($this->subCategoryPath($sub), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_category_id', 'name', 'description']);
    }

    public function test_update_returns_404_for_missing_sub_category(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->subCategoryPath(99999), $this->validVideoSubCategoryUpdatePayload())
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_sub_category_and_assets(): void
    {
        $this->actingAsSuperAdmin();

        Storage::disk('public')->put('tutorials/del/sub/image.jpg', 'img');
        Storage::disk('public')->put('tutorials/del/sub/icon.svg', 'icon');

        $category = $this->createVideoCategory(['slug' => 'del']);
        $sub = $this->createVideoSubCategory($category, [
            'slug' => 'sub',
            'image' => 'tutorials/del/sub/image.jpg',
            'icon' => 'tutorials/del/sub/icon.svg',
        ]);

        $this->deleteJson($this->subCategoryPath($sub))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('video_sub_categories', ['id' => $sub->id]);
        Storage::disk('public')->assertMissing('tutorials/del/sub/image.jpg');
        Storage::disk('public')->assertMissing('tutorials/del/sub/icon.svg');
    }

    public function test_destroy_returns_404_for_missing_sub_category(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson($this->subCategoryPath(99999))->assertNotFound();
    }

    public function test_show_route_is_not_registered(): void
    {
        $routes = collect(app('router')->getRoutes())
            ->filter(fn ($route) => $route->uri() === 'api/video-sub-categories/{video_sub_category}')
            ->flatMap(fn ($route) => $route->methods())
            ->filter(fn ($method) => $method !== 'HEAD')
            ->values()
            ->all();

        $this->assertNotContains('GET', $routes);
        $this->assertContains('PUT', $routes);
        $this->assertContains('DELETE', $routes);
    }
}

<?php

namespace Tests\Feature\Videos;

use App\Models\VideoCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesVideosApiSchema;
use Tests\TestCase;

class VideoCategoriesApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesVideosApiSchema;

    private const INDEX_PATH = '/api/video-categories';

    private const INDEX_SUCCESS_MESSAGE = 'دسته بندی ها با موفقیت دریافت شد.';

    private const STORE_SUCCESS_MESSAGE = 'دسته بندی با موفقیت ایجاد شد.';

    private const UPDATE_SUCCESS_MESSAGE = 'دسته بندی با موفقیت به روزرسانی شد.';

    private const DESTROY_SUCCESS_MESSAGE = 'دسته بندی با موفقیت حذف شد.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpVideosApiSchema();
    }

    private function categoryPath(int|VideoCategory $category): string
    {
        $id = $category instanceof VideoCategory ? $category->id : $category;

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
        $this->post(self::INDEX_PATH, $this->validVideoCategoryStorePayload(), ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $category = $this->createVideoCategory();

        $this->putJson($this->categoryPath($category), $this->validVideoCategoryUpdatePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $category = $this->createVideoCategory();

        $this->deleteJson($this->categoryPath($category))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $response = $this->post(self::INDEX_PATH, $this->validVideoCategoryStorePayload([
            'name' => 'Super Admin Category',
            'slug' => 'super-admin-category',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $id = $response->json('data.id');

        $this->putJson($this->categoryPath($id), $this->validVideoCategoryUpdatePayload([
            'name' => 'Super Updated',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->deleteJson($this->categoryPath($id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $response = $this->post(self::INDEX_PATH, $this->validVideoCategoryStorePayload([
            'slug' => 'regular-admin-category',
        ]), ['Accept' => 'application/json'])
            ->assertCreated();

        $id = $response->json('data.id');

        $this->putJson($this->categoryPath($id), $this->validVideoCategoryUpdatePayload())
            ->assertOk();

        $this->deleteJson($this->categoryPath($id))->assertOk();
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
            ->assertJsonPath('data.categories', [])
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

        $category = $this->createVideoCategory(['name' => 'Structure Cat', 'slug' => 'structure-cat']);
        $this->createVideoSubCategory($category, ['name' => 'Child Sub']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'categories' => [
                        [
                            'id',
                            'name',
                            'slug',
                            'description',
                            'image',
                            'image_url',
                            'icon',
                            'icon_url',
                            'created_at',
                            'created_at_formatted' => ['date', 'time'],
                            'sub_categories_count',
                            'sub_categories',
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
            ->assertJsonPath('data.categories.0.sub_categories_count', 1);
    }

    public function test_index_orders_by_created_at_desc(): void
    {
        $this->actingAsSuperAdmin();

        $first = $this->createVideoCategory(['name' => 'First', 'slug' => 'first-cat']);
        $this->travel(1)->seconds();
        $second = $this->createVideoCategory(['name' => 'Second', 'slug' => 'second-cat']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.categories.0.id', $second->id)
            ->assertJsonPath('data.categories.1.id', $first->id);
    }

    public function test_index_respects_per_page_and_pagination(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 5; $i++) {
            $this->createVideoCategory(['slug' => 'page-cat-'.$i]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonCount(2, 'data.categories');
    }

    public function test_index_search_matches_name_and_slug(): void
    {
        $this->actingAsSuperAdmin();

        $this->createVideoCategory(['name' => 'Alpha Lessons', 'slug' => 'alpha-lessons']);
        $this->createVideoCategory(['name' => 'Beta Course', 'slug' => 'needle-slug']);
        $this->createVideoCategory(['name' => 'Other', 'slug' => 'other']);

        $this->getJson(self::INDEX_PATH.'?search=Alpha')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.categories.0.name', 'Alpha Lessons');

        $this->getJson(self::INDEX_PATH.'?search=needle-slug')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.categories.0.slug', 'needle-slug');
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_category_and_stores_files(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::INDEX_PATH, $this->validVideoCategoryStorePayload([
            'name' => 'New Category',
            'slug' => 'new-category',
            'description' => 'Fresh description',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.name', 'New Category')
            ->assertJsonPath('data.slug', 'new-category')
            ->assertJsonPath('data.description', 'Fresh description');

        $this->assertDatabaseHas('video_categories', [
            'id' => $response->json('data.id'),
            'name' => 'New Category',
            'slug' => 'new-category',
        ]);

        $image = $response->json('data.image');
        $icon = $response->json('data.icon');
        $this->assertIsString($image);
        $this->assertIsString($icon);
        Storage::disk('public')->assertExists($image);
        Storage::disk('public')->assertExists($icon);
        $this->assertStringContainsString('tutorials/new-category/', $image);
    }

    public function test_store_trims_slug_for_directory(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::INDEX_PATH, $this->validVideoCategoryStorePayload([
            'slug' => '  spaced-slug  ',
        ]), ['Accept' => 'application/json'])
            ->assertCreated();

        $this->assertSame('spaced-slug', $response->json('data.slug'));
        $this->assertStringContainsString('tutorials/spaced-slug/', $response->json('data.image'));
    }

    public function test_store_validation_requires_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug', 'description', 'image', 'icon']);
    }

    public function test_store_rejects_non_image_image_file(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validVideoCategoryStorePayload([
            'image' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_store_rejects_non_svg_icon(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validVideoCategoryStorePayload([
            'icon' => UploadedFile::fake()->image('icon.png'),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['icon']);
    }

    public function test_store_rejects_oversized_icon(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validVideoCategoryStorePayload([
            'icon' => $this->fakeSvg('big.svg', 1025),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['icon']);
    }

    public function test_store_rejects_oversized_string_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validVideoCategoryStorePayload([
            'name' => str_repeat('a', 256),
            'slug' => str_repeat('b', 256),
            'description' => str_repeat('c', 20001),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug', 'description']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_changes_name_and_description_without_files(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory([
            'name' => 'Old Name',
            'slug' => 'keep-slug',
            'description' => 'Old desc',
            'image' => 'tutorials/keep-slug/old.jpg',
            'icon' => 'tutorials/keep-slug/old.svg',
        ]);

        $this->putJson($this->categoryPath($category), $this->validVideoCategoryUpdatePayload([
            'name' => 'New Name',
            'description' => 'New desc',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.description', 'New desc')
            ->assertJsonPath('data.slug', 'keep-slug')
            ->assertJsonPath('data.image', 'tutorials/keep-slug/old.jpg');
    }

    public function test_update_replaces_image_and_icon_when_provided(): void
    {
        $this->actingAsSuperAdmin();

        Storage::disk('public')->put('tutorials/replace-cat/old.jpg', 'old-image');
        Storage::disk('public')->put('tutorials/replace-cat/old.svg', 'old-icon');

        $category = $this->createVideoCategory([
            'slug' => 'replace-cat',
            'image' => 'tutorials/replace-cat/old.jpg',
            'icon' => 'tutorials/replace-cat/old.svg',
        ]);

        $response = $this->post($this->categoryPath($category), array_merge(
            $this->validVideoCategoryUpdatePayload(['name' => 'Replaced']),
            [
                '_method' => 'PUT',
                'image' => UploadedFile::fake()->image('new.jpg'),
                'icon' => $this->fakeSvg('new.svg'),
            ]
        ), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Replaced');

        Storage::disk('public')->assertMissing('tutorials/replace-cat/old.jpg');
        Storage::disk('public')->assertMissing('tutorials/replace-cat/old.svg');
        Storage::disk('public')->assertExists($response->json('data.image'));
        Storage::disk('public')->assertExists($response->json('data.icon'));
    }

    public function test_update_validation_requires_name_and_description(): void
    {
        $this->actingAsSuperAdmin();

        $category = $this->createVideoCategory();

        $this->putJson($this->categoryPath($category), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description']);
    }

    public function test_update_returns_404_for_missing_category(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->categoryPath(99999), $this->validVideoCategoryUpdatePayload())
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_category_assets_and_sub_categories(): void
    {
        $this->actingAsSuperAdmin();

        Storage::disk('public')->put('tutorials/delete-cat/image.jpg', 'img');
        Storage::disk('public')->put('tutorials/delete-cat/icon.svg', 'icon');

        $category = $this->createVideoCategory([
            'slug' => 'delete-cat',
            'image' => 'tutorials/delete-cat/image.jpg',
            'icon' => 'tutorials/delete-cat/icon.svg',
        ]);
        $sub = $this->createVideoSubCategory($category);

        $this->deleteJson($this->categoryPath($category))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('video_categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('video_sub_categories', ['id' => $sub->id]);
        Storage::disk('public')->assertMissing('tutorials/delete-cat/image.jpg');
        Storage::disk('public')->assertMissing('tutorials/delete-cat/icon.svg');
    }

    public function test_destroy_returns_404_for_missing_category(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson($this->categoryPath(99999))->assertNotFound();
    }

    public function test_show_route_is_not_registered(): void
    {
        $routes = collect(app('router')->getRoutes())
            ->filter(fn ($route) => $route->uri() === 'api/video-categories/{video_category}')
            ->flatMap(fn ($route) => $route->methods())
            ->filter(fn ($method) => $method !== 'HEAD')
            ->values()
            ->all();

        $this->assertNotContains('GET', $routes);
        $this->assertContains('PUT', $routes);
        $this->assertContains('DELETE', $routes);
    }
}

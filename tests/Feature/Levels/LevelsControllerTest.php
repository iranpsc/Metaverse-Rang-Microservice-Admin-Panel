<?php

namespace Tests\Feature\Levels;

use App\Models\Level\Level;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesLevelsApiSchema;
use Tests\TestCase;

class LevelsControllerTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesLevelsApiSchema;

    private const INDEX_PATH = '/api/levels';

    private const INDEX_SUCCESS_MESSAGE = 'لیست سطوح با موفقیت دریافت شد.';

    private const STORE_SUCCESS_MESSAGE = 'سطح ایجاد شد';

    private const UPDATE_SUCCESS_MESSAGE = 'سطح ویرایش شد';

    private const DESTROY_SUCCESS_MESSAGE = 'سطح حذف شد';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLevelsApiSchema();
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
        $level = Level::factory()->create();

        $this->putJson($this->levelPath($level), $this->validUpdatePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->deleteJson($this->levelPath($level))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'Super Admin Level',
            'slug' => 'super-admin-level',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $level = Level::factory()->create(['name' => 'Updatable', 'slug' => 'updatable']);

        $this->putJson($this->levelPath($level), $this->validUpdatePayload([
            'name' => 'Updated by super',
            'slug' => 'updated-by-super',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $toDelete = Level::factory()->create();

        $this->deleteJson($this->levelPath($toDelete))
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
            'name' => 'Regular Admin Level',
            'slug' => 'regular-admin-level',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $level = Level::factory()->create();

        $this->putJson($this->levelPath($level), $this->validUpdatePayload([
            'name' => 'Updated by regular',
            'slug' => 'updated-by-regular',
        ]))->assertOk();

        $toDelete = Level::factory()->create();

        $this->deleteJson($this->levelPath($toDelete))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Index — happy path / structure
    // -------------------------------------------------------------------------

    public function test_empty_dataset_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.levels', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0);
    }

    public function test_index_returns_full_json_structure(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create([
            'name' => 'Structure Level',
            'slug' => 'structure-level',
            'score' => '150',
        ]);

        $level->image()->create(['url' => 'levels/structure.png']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'levels' => [
                        [
                            'id',
                            'name',
                            'slug',
                            'score',
                            'background_image',
                            'background_image_url',
                            'image' => [
                                'id',
                                'url',
                                'full_url',
                            ],
                            'image_url',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'pagination' => [
                        'total',
                        'per_page',
                        'current_page',
                        'last_page',
                    ],
                ],
            ]);
    }

    public function test_index_score_is_integer_in_response(): void
    {
        $this->actingAsSuperAdmin();

        Level::factory()->create(['score' => '42']);

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $this->assertSame(42, $response->json('data.levels.0.score'));
        $this->assertIsInt($response->json('data.levels.0.score'));
    }

    public function test_index_includes_null_image_fields_when_no_morph_image(): void
    {
        $this->actingAsSuperAdmin();

        Level::factory()->create();

        $response = $this->getJson(self::INDEX_PATH)->assertOk();
        $levelPayload = $response->json('data.levels.0');

        $this->assertNull($levelPayload['image_url']);
        // Controller maps LevelResource::toArray() directly; missing morph image
        // must not expose a real image payload with an id/url.
        if (array_key_exists('image', $levelPayload) && $levelPayload['image'] !== null) {
            $this->assertIsArray($levelPayload['image']);
            $this->assertArrayNotHasKey('id', $levelPayload['image']);
            $this->assertArrayNotHasKey('url', $levelPayload['image']);
        }
    }

    public function test_index_orders_levels_by_id_desc(): void
    {
        $this->actingAsSuperAdmin();

        $first = Level::factory()->create(['name' => 'First', 'slug' => 'first']);
        $second = Level::factory()->create(['name' => 'Second', 'slug' => 'second']);
        $third = Level::factory()->create(['name' => 'Third', 'slug' => 'third']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.levels.0.id', $third->id)
            ->assertJsonPath('data.levels.1.id', $second->id)
            ->assertJsonPath('data.levels.2.id', $first->id);
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_pagination_respects_per_page_and_page(): void
    {
        $this->actingAsSuperAdmin();

        Level::factory()->count(5)->create();

        $this->getJson(self::INDEX_PATH.'?'.http_build_query([
            'per_page' => 2,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonCount(2, 'data.levels');
    }

    public function test_pagination_defaults_to_page_one_and_ten_per_page(): void
    {
        $this->actingAsSuperAdmin();

        Level::factory()->count(3)->create();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 3);
    }

    public function test_zero_or_negative_per_page_falls_back_to_ten(): void
    {
        $this->actingAsSuperAdmin();

        Level::factory()->count(3)->create();

        $this->getJson(self::INDEX_PATH.'?per_page=0')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10);

        $this->getJson(self::INDEX_PATH.'?per_page=-5')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10);
    }

    // -------------------------------------------------------------------------
    // Store — happy path
    // -------------------------------------------------------------------------

    public function test_store_creates_level_with_required_background_and_optional_image(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'Bronze Tier',
            'slug' => 'bronze-tier',
            'score' => 100,
            'image' => UploadedFile::fake()->image('badge.png'),
            'background_image' => UploadedFile::fake()->image('bg.jpg'),
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.level.name', 'Bronze Tier')
            ->assertJsonPath('data.level.slug', 'bronze-tier')
            ->assertJsonPath('data.level.score', 100)
            ->assertJsonStructure([
                'data' => [
                    'level' => [
                        'id',
                        'name',
                        'slug',
                        'score',
                        'background_image',
                        'background_image_url',
                        'image' => ['id', 'url', 'full_url'],
                        'image_url',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $backgroundUrl = $response->json('data.level.background_image');
        $this->assertNotNull($backgroundUrl);
        $this->assertStringContainsString('/uploads/levels/', $backgroundUrl);

        $imagePath = $response->json('data.level.image.url');
        $this->assertNotNull($imagePath);
        Storage::disk('public')->assertExists($imagePath);
        Storage::disk('public')->assertExists($this->extractPublicPathFromUploadsUrl($backgroundUrl));

        $this->assertDatabaseHas('levels', [
            'name' => 'Bronze Tier',
            'slug' => 'bronze-tier',
            'score' => 100,
        ]);
    }

    public function test_store_creates_level_without_optional_image(): void
    {
        $this->actingAsSuperAdmin();

        $payload = $this->validStorePayload([
            'name' => 'No Image Level',
            'slug' => 'no-image-level',
        ]);
        unset($payload['image']);

        $response = $this->post(self::INDEX_PATH, $payload, ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.level.name', 'No Image Level')
            ->assertJsonPath('data.level.image_url', null);

        $this->assertDatabaseHas('levels', [
            'name' => 'No Image Level',
            'slug' => 'no-image-level',
        ]);

        $level = Level::query()->where('slug', 'no-image-level')->first();
        $this->assertNull($level?->image);
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
                    'level' => [
                        'id',
                        'name',
                        'slug',
                        'score',
                        'background_image',
                        'background_image_url',
                        'image_url',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // Store — validation
    // -------------------------------------------------------------------------

    public function test_store_requires_name_slug_score_and_background_image(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, [], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug', 'score', 'background_image']);
    }

    public function test_store_rejects_duplicate_name_and_slug(): void
    {
        $this->actingAsSuperAdmin();

        Level::factory()->create([
            'name' => 'Taken Name',
            'slug' => 'taken-slug',
        ]);

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'Taken Name',
            'slug' => 'taken-slug',
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug']);
    }

    public function test_store_rejects_negative_score(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'score' => -1,
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['score']);
    }

    public function test_store_rejects_non_integer_score(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'score' => 'not-a-number',
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['score']);
    }

    public function test_store_accepts_zero_score(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'Zero Score',
            'slug' => 'zero-score',
            'score' => 0,
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.level.score', 0);
    }

    public function test_store_rejects_invalid_background_image_mime(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'background_image' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_image']);
    }

    public function test_store_rejects_invalid_optional_image_mime(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'image' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_store_rejects_oversized_background_image(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'background_image' => UploadedFile::fake()->image('huge.jpg')->size(5025),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_image']);
    }

    // -------------------------------------------------------------------------
    // Update — happy path
    // -------------------------------------------------------------------------

    public function test_update_changes_fields_without_replacing_images(): void
    {
        $this->actingAsSuperAdmin();

        $originalBackground = url('uploads/levels/keep-bg.jpg');
        $level = Level::factory()->create([
            'name' => 'Before',
            'slug' => 'before',
            'score' => '10',
            'background_image' => $originalBackground,
        ]);
        $level->image()->create(['url' => 'levels/keep.png']);

        $this->putJson($this->levelPath($level), $this->validUpdatePayload([
            'name' => 'After',
            'slug' => 'after',
            'score' => 99,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.level.name', 'After')
            ->assertJsonPath('data.level.slug', 'after')
            ->assertJsonPath('data.level.score', 99)
            ->assertJsonPath('data.level.background_image', $originalBackground)
            ->assertJsonPath('data.level.image.url', 'levels/keep.png');

        $this->assertDatabaseHas('levels', [
            'id' => $level->id,
            'name' => 'After',
            'slug' => 'after',
            'score' => 99,
            'background_image' => $originalBackground,
        ]);
    }

    public function test_update_replaces_background_and_image_files(): void
    {
        $this->actingAsSuperAdmin();

        $oldBackgroundPath = 'levels/old-bg.jpg';
        Storage::disk('public')->put($oldBackgroundPath, 'old-bg');

        $level = Level::factory()->create([
            'name' => 'Replace Me',
            'slug' => 'replace-me',
            'background_image' => url('uploads/'.$oldBackgroundPath),
        ]);
        $level->image()->create(['url' => 'levels/old.png']);
        Storage::disk('public')->put('levels/old.png', 'old-image');

        $response = $this->post($this->levelPath($level), array_merge(
            $this->validUpdatePayload([
                'name' => 'Replaced',
                'slug' => 'replaced',
                'score' => 50,
            ]),
            [
                '_method' => 'PUT',
                'background_image' => UploadedFile::fake()->image('new-bg.jpg'),
                'image' => UploadedFile::fake()->image('new-badge.png'),
            ]
        ), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.level.name', 'Replaced')
            ->assertJsonPath('data.level.slug', 'replaced');

        $newBackgroundUrl = $response->json('data.level.background_image');
        $this->assertStringContainsString('/uploads/levels/', $newBackgroundUrl);
        $this->assertNotSame(url('uploads/'.$oldBackgroundPath), $newBackgroundUrl);

        Storage::disk('public')->assertMissing($oldBackgroundPath);
        Storage::disk('public')->assertMissing('levels/old.png');
        Storage::disk('public')->assertExists($this->extractPublicPathFromUploadsUrl($newBackgroundUrl));
        Storage::disk('public')->assertExists($response->json('data.level.image.url'));
    }

    public function test_update_creates_morph_image_when_none_existed(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create([
            'name' => 'No Image Yet',
            'slug' => 'no-image-yet',
        ]);

        $this->assertNull($level->image);

        $response = $this->post($this->levelPath($level), array_merge(
            $this->validUpdatePayload([
                'name' => 'Now Has Image',
                'slug' => 'now-has-image',
            ]),
            [
                '_method' => 'PUT',
                'image' => UploadedFile::fake()->image('fresh.png'),
            ]
        ), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.level.name', 'Now Has Image');

        $this->assertNotNull($response->json('data.level.image.url'));
        $level->refresh();
        $this->assertNotNull($level->image);
        Storage::disk('public')->assertExists($level->image->url);
    }

    public function test_update_allows_same_slug_for_same_level(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create([
            'name' => 'Keep Slug',
            'slug' => 'keep-slug',
        ]);

        $this->putJson($this->levelPath($level), $this->validUpdatePayload([
            'name' => 'Keep Slug Updated',
            'slug' => 'keep-slug',
            'score' => 20,
        ]))
            ->assertOk()
            ->assertJsonPath('data.level.slug', 'keep-slug');
    }

    // -------------------------------------------------------------------------
    // Update — validation / errors
    // -------------------------------------------------------------------------

    public function test_update_requires_name_slug_and_score(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->putJson($this->levelPath($level), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug', 'score']);
    }

    public function test_update_rejects_duplicate_slug_from_another_level(): void
    {
        $this->actingAsSuperAdmin();

        Level::factory()->create(['slug' => 'occupied-slug']);
        $level = Level::factory()->create(['slug' => 'my-slug']);

        $this->putJson($this->levelPath($level), $this->validUpdatePayload([
            'name' => 'Conflict',
            'slug' => 'occupied-slug',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_update_rejects_negative_score(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->putJson($this->levelPath($level), $this->validUpdatePayload([
            'score' => -10,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['score']);
    }

    public function test_update_rejects_oversized_images(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->post($this->levelPath($level), array_merge(
            $this->validUpdatePayload(),
            [
                '_method' => 'PUT',
                'background_image' => UploadedFile::fake()->image('bg.jpg')->size(1025),
                'image' => UploadedFile::fake()->image('img.jpg')->size(1025),
            ]
        ), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['background_image', 'image']);
    }

    public function test_update_rejects_invalid_image_mimes(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->post($this->levelPath($level), array_merge(
            $this->validUpdatePayload(),
            [
                '_method' => 'PUT',
                'image' => UploadedFile::fake()->create('badge.gif', 50, 'image/gif'),
            ]
        ), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_update_nonexistent_level_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson(self::INDEX_PATH.'/999999', $this->validUpdatePayload())
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_level_and_storage_files(): void
    {
        $this->actingAsSuperAdmin();

        $backgroundPath = 'levels/delete-bg.jpg';
        $imagePath = 'levels/delete.png';
        Storage::disk('public')->put($backgroundPath, 'bg');
        Storage::disk('public')->put($imagePath, 'img');

        $level = Level::factory()->create([
            'name' => 'To Delete',
            'slug' => 'to-delete',
            'background_image' => url('uploads/'.$backgroundPath),
        ]);
        $level->image()->create(['url' => $imagePath]);

        $this->deleteJson($this->levelPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('levels', ['id' => $level->id]);
        $this->assertDatabaseMissing('images', [
            'imageable_id' => $level->id,
            'imageable_type' => Level::class,
        ]);
        Storage::disk('public')->assertMissing($backgroundPath);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_destroy_also_deletes_associated_prize(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $prize = $level->prize()->create([
            'psc' => 10,
            'yellow' => 1,
            'blue' => 2,
            'red' => 3,
            'effect' => 4,
            'satisfaction' => 1.5,
        ]);

        $this->deleteJson($this->levelPath($level))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('levels', ['id' => $level->id]);
        $this->assertDatabaseMissing('level_prizes', ['id' => $prize->id]);
    }

    public function test_destroy_nonexistent_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson(self::INDEX_PATH.'/999999')->assertNotFound();
    }

    public function test_destroy_does_not_delete_other_levels(): void
    {
        $this->actingAsSuperAdmin();

        $target = Level::factory()->create(['name' => 'Target', 'slug' => 'target']);
        $other = Level::factory()->create(['name' => 'Other', 'slug' => 'other']);

        $this->deleteJson($this->levelPath($target))->assertOk();

        $this->assertDatabaseMissing('levels', ['id' => $target->id]);
        $this->assertDatabaseHas('levels', ['id' => $other->id, 'name' => 'Other']);
    }

    // -------------------------------------------------------------------------
    // Edge / security
    // -------------------------------------------------------------------------

    public function test_show_route_is_not_registered_on_api_resource(): void
    {
        $matching = collect(Route::getRoutes())
            ->filter(function ($route) {
                return $route->uri() === 'api/levels/{level}'
                    && in_array('GET', $route->methods(), true);
            });

        $this->assertTrue(
            $matching->isEmpty(),
            'GET api/levels/{level} (show) should be excluded from the apiResource.'
        );
        $this->assertFalse(Route::has('levels.show'));
    }

    public function test_store_ignores_unexpected_mass_assignment_fields(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::INDEX_PATH, array_merge(
            $this->validStorePayload([
                'name' => 'Safe Level',
                'slug' => 'safe-level',
                'score' => 5,
            ]),
            [
                'id' => 99999,
                'created_at' => '2000-01-01 00:00:00',
            ]
        ), ['Accept' => 'application/json'])
            ->assertCreated();

        $this->assertNotSame(99999, $response->json('data.level.id'));
        $this->assertDatabaseHas('levels', [
            'name' => 'Safe Level',
            'slug' => 'safe-level',
        ]);
        $this->assertDatabaseMissing('levels', ['id' => 99999]);
    }

    public function test_unicode_name_and_slug_are_persisted(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'سطح برنزی',
            'slug' => 'سطح-برنزی',
            'score' => 7,
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.level.name', 'سطح برنزی')
            ->assertJsonPath('data.level.slug', 'سطح-برنزی');

        $this->assertDatabaseHas('levels', [
            'name' => 'سطح برنزی',
            'slug' => 'سطح-برنزی',
        ]);
    }

    public function test_index_does_not_leak_internal_exception_payload(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $this->assertArrayNotHasKey('exception', $response->json());
        $this->assertArrayNotHasKey('trace', $response->json());
        $this->assertTrue($response->json('success'));
    }

    // -------------------------------------------------------------------------
    // Error paths / extractStoragePath edges
    // -------------------------------------------------------------------------

    public function test_store_returns_500_and_cleans_uploaded_files_when_create_fails(): void
    {
        $this->actingAsSuperAdmin();

        Level::creating(function () {
            throw new \RuntimeException('forced level create failure');
        });

        $this->post(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'Fail Store',
            'slug' => 'fail-store',
            'image' => UploadedFile::fake()->image('fail.png'),
            'background_image' => UploadedFile::fake()->image('fail-bg.jpg'),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در ایجاد سطح');

        $this->assertDatabaseMissing('levels', ['slug' => 'fail-store']);
        $this->assertSame([], Storage::disk('public')->allFiles('levels'));
    }

    public function test_update_returns_500_and_cleans_new_files_when_update_fails(): void
    {
        $this->actingAsSuperAdmin();

        $oldBackgroundPath = 'levels/keep-bg.jpg';
        Storage::disk('public')->put($oldBackgroundPath, 'old-bg');

        $level = Level::factory()->create([
            'background_image' => url('uploads/'.$oldBackgroundPath),
        ]);
        $level->image()->create(['url' => 'levels/keep.png']);
        Storage::disk('public')->put('levels/keep.png', 'old-image');

        Level::updating(function () {
            throw new \RuntimeException('forced level update failure');
        });

        $this->post($this->levelPath($level), array_merge(
            $this->validUpdatePayload([
                'name' => 'Fail Update',
                'slug' => 'fail-update',
            ]),
            [
                '_method' => 'PUT',
                'background_image' => UploadedFile::fake()->image('new-bg.jpg'),
                'image' => UploadedFile::fake()->image('new.png'),
            ]
        ), ['Accept' => 'application/json'])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بروزرسانی سطح');

        Storage::disk('public')->assertExists($oldBackgroundPath);
        Storage::disk('public')->assertExists('levels/keep.png');
    }

    public function test_destroy_returns_500_when_delete_fails(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        Level::deleting(function () {
            throw new \RuntimeException('forced level delete failure');
        });

        $this->deleteJson($this->levelPath($level))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در حذف سطح');

        $this->assertDatabaseHas('levels', ['id' => $level->id]);
    }

    public function test_destroy_handles_null_and_relative_background_paths(): void
    {
        $this->actingAsSuperAdmin();

        $emptyBg = Level::factory()->create([
            'name' => 'Empty Bg',
            'slug' => 'empty-bg',
            'background_image' => '',
        ]);

        $this->deleteJson($this->levelPath($emptyBg))
            ->assertOk()
            ->assertJsonPath('success', true);

        $relativePath = 'levels/relative-only.jpg';
        Storage::disk('public')->put($relativePath, 'relative');
        $relativeBg = Level::factory()->create([
            'name' => 'Relative Bg',
            'slug' => 'relative-bg',
            'background_image' => $relativePath,
        ]);

        $this->deleteJson($this->levelPath($relativeBg))
            ->assertOk()
            ->assertJsonPath('success', true);

        Storage::disk('public')->assertMissing($relativePath);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function levelPath(Level $level): string
    {
        return self::INDEX_PATH.'/'.$level->id;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validStorePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Valid Level Name',
            'slug' => 'valid-level-name',
            'score' => 10,
            'background_image' => UploadedFile::fake()->image('background.jpg'),
            'image' => UploadedFile::fake()->image('level.png'),
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Updated Level Name',
            'slug' => 'updated-level-name',
            'score' => 25,
        ], $overrides);
    }

    private function extractPublicPathFromUploadsUrl(string $url): string
    {
        $marker = '/uploads/';
        $pos = strpos($url, $marker);
        $this->assertNotFalse($pos, 'URL should contain /uploads/');

        return substr($url, $pos + strlen($marker));
    }
}

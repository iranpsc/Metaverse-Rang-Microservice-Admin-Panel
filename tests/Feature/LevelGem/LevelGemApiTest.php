<?php

namespace Tests\Feature\LevelGem;

use App\Models\Level\Level;
use App\Models\Level\LevelGem;
use App\Repositories\LevelGemRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesLevelGemApiSchema;
use Tests\TestCase;

class LevelGemApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesLevelGemApiSchema;

    private const SHOW_WITH_GEM_MESSAGE = 'گوهر سطح با موفقیت دریافت شد.';

    private const SHOW_WITHOUT_GEM_MESSAGE = 'برای این سطح تاکنون گوهری ثبت نشده است.';

    private const STORE_SUCCESS_MESSAGE = 'گوهر سطح با موفقیت ثبت شد.';

    private const STORE_ALREADY_EXISTS_MESSAGE = 'برای این سطح گوهری ثبت شده است. لطفاً از ویرایش استفاده کنید.';

    private const UPDATE_SUCCESS_MESSAGE = 'گوهر سطح با موفقیت بروزرسانی شد.';

    private const UPDATE_MISSING_MESSAGE = 'برای این سطح گوهری ثبت نشده است.';

    private const DESTROY_FILE_SUCCESS_MESSAGE = 'فایل با موفقیت حذف شد.';

    private const DESTROY_FILE_MISSING_GEM_MESSAGE = 'برای این سطح گوهری ثبت نشده است.';

    private const DESTROY_FILE_KEY_REQUIRED_MESSAGE = 'کلید فایل مدل برای حذف الزامی است.';

    private const DESTROY_FILE_NOT_FOUND_MESSAGE = 'فایل مورد نظر یافت نشد.';

    private const STORE_ERROR_MESSAGE = 'خطا در ثبت گوهر سطح';

    private const UPDATE_ERROR_MESSAGE = 'خطا در بروزرسانی گوهر سطح';

    private const DESTROY_FILE_ERROR_MESSAGE = 'خطا در حذف فایل گوهر سطح';

    private const MERGE_LIMIT_MESSAGE = 'حداکثر ۲۰ فایل مدل می‌توانید ذخیره کنید. ابتدا برخی فایل‌های قبلی را حذف کنید.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLevelGemApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_show_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->getJson($this->gemPath($level))->assertUnauthorized();
    }

    public function test_unauthenticated_store_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), $this->validStorePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->putJson($this->gemPath($level), $this->validStorePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_file_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->deleteJson($this->gemFilesPath($level), [
            'field' => 'png_file',
        ])->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->getJson($this->gemPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_WITHOUT_GEM_MESSAGE);

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'name' => 'Super admin gem',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $this->putJson($this->gemPath($level), $this->validStorePayload([
            'name' => 'Updated by super',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $level->gem->forceFill([
            'png_file' => url('uploads/levels/to-delete.png'),
        ])->save();
        Storage::disk('public')->put('levels/to-delete.png', 'png-bytes');

        $this->deleteJson($this->gemFilesPath($level), ['field' => 'png_file'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_FILE_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $level = Level::factory()->create();

        $this->getJson($this->gemPath($level))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'name' => 'Regular admin gem',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->putJson($this->gemPath($level), $this->validStorePayload([
            'name' => 'Updated by regular',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_null_gem_when_none_registered(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->getJson($this->gemPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.gem', null)
            ->assertJsonPath('message', self::SHOW_WITHOUT_GEM_MESSAGE);
    }

    public function test_show_returns_gem_resource_structure(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        $gem = LevelGem::factory()->withPng()->withFbx()->create([
            'level_id' => $level->id,
            'name' => 'Structure Gem',
            'points' => 42,
            'volume' => 1.25,
            'encryption' => true,
            'has_animation' => true,
            'lines' => 10,
        ]);

        $response = $this->getJson($this->gemPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_WITH_GEM_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'gem' => [
                        'id',
                        'name',
                        'description',
                        'thread',
                        'points',
                        'volume',
                        'color',
                        'png_file',
                        'fbx_file',
                        'encryption',
                        'designer',
                        'has_animation',
                        'lines',
                    ],
                ],
            ]);

        $this->assertSame($gem->id, $response->json('data.gem.id'));
        $this->assertSame('Structure Gem', $response->json('data.gem.name'));
        $this->assertSame(42, $response->json('data.gem.points'));
        $this->assertIsInt($response->json('data.gem.points'));
        $this->assertSame(1.25, $response->json('data.gem.volume'));
        $this->assertIsFloat($response->json('data.gem.volume'));
        $this->assertTrue($response->json('data.gem.encryption'));
        $this->assertIsBool($response->json('data.gem.encryption'));
        $this->assertTrue($response->json('data.gem.has_animation'));
        $this->assertIsBool($response->json('data.gem.has_animation'));
        $this->assertSame(10, $response->json('data.gem.lines'));
        $this->assertIsInt($response->json('data.gem.lines'));
        $this->assertIsArray($response->json('data.gem.fbx_file'));
    }

    public function test_show_nonexistent_level_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson($this->gemPath(999999))->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Store happy paths
    // -------------------------------------------------------------------------

    public function test_store_creates_gem_without_files(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $response = $this->postJson($this->gemPath($level), $this->validStorePayload([
            'name' => 'Plain Gem',
            'points' => 100,
            'volume' => 2.5,
            'encryption' => true,
            'has_animation' => false,
            'lines' => 7,
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.gem.name', 'Plain Gem')
            ->assertJsonPath('data.gem.points', 100)
            ->assertJsonPath('data.gem.volume', 2.5)
            ->assertJsonPath('data.gem.encryption', true)
            ->assertJsonPath('data.gem.has_animation', false)
            ->assertJsonPath('data.gem.lines', 7);

        $this->assertDatabaseHas('level_gems', [
            'level_id' => $level->id,
            'name' => 'Plain Gem',
            'points' => 100,
            'lines' => 7,
        ]);

        $this->assertNull($response->json('data.gem.png_file'));
        $this->assertNull($response->json('data.gem.fbx_file'));
    }

    public function test_store_trims_string_fields(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'name' => '  Trimmed Gem  ',
            'description' => '  desc  ',
            'thread' => '  thread  ',
            'color' => '  #ABCDEF  ',
            'designer' => '  Designer  ',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.gem.name', 'Trimmed Gem')
            ->assertJsonPath('data.gem.description', 'desc')
            ->assertJsonPath('data.gem.thread', 'thread')
            ->assertJsonPath('data.gem.color', '#ABCDEF')
            ->assertJsonPath('data.gem.designer', 'Designer');
    }

    public function test_store_with_png_upload_stores_public_url(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $response = $this->post($this->gemPath($level), $this->validStorePayload([
            'name' => 'Gem With PNG',
            'png_file' => UploadedFile::fake()->image('gem.png'),
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $pngUrl = $response->json('data.gem.png_file');
        $this->assertIsString($pngUrl);
        $this->assertStringContainsString('/uploads/levels/', $pngUrl);

        $path = $this->extractPublicPathFromUrl($pngUrl);
        Storage::disk('public')->assertExists($path);
    }

    public function test_store_with_fbx_file_map_persists_json_array(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $fbxUrl = url('uploads/levels/model.fbx');
        $glbUrl = url('uploads/levels/model.glb');

        $response = $this->postJson($this->gemPath($level), $this->validStorePayload([
            'name' => 'Gem With Models',
            'fbx_file' => [
                'fbx' => $fbxUrl,
                'glb' => $glbUrl,
            ],
        ]))
            ->assertCreated()
            ->assertJsonPath('data.gem.fbx_file.fbx', $fbxUrl)
            ->assertJsonPath('data.gem.fbx_file.glb', $glbUrl);

        $gem = LevelGem::query()->where('level_id', $level->id)->first();
        $this->assertIsArray($gem->fbx_file);
        $this->assertSame($fbxUrl, $gem->fbx_file['fbx']);
    }

    public function test_store_accepts_fbx_file_as_json_string(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $fbxUrl = url('uploads/levels/encoded.fbx');

        $this->post($this->gemPath($level), $this->validStorePayload([
            'name' => 'JSON String FBX',
            'fbx_file' => json_encode(['fbx' => $fbxUrl]),
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.gem.fbx_file.fbx', $fbxUrl);
    }

    public function test_store_accepts_compatible_jpeg_jpg_fbx_keys(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $jpegUrl = url('uploads/levels/texture.jpg');

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'fbx_file' => [
                'jpeg' => $jpegUrl,
            ],
        ]))
            ->assertCreated()
            ->assertJsonPath('data.gem.fbx_file.jpeg', $jpegUrl);
    }

    public function test_store_rejects_when_gem_already_exists(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        $this->createGem($level);

        $this->postJson($this->gemPath($level), $this->validStorePayload())
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::STORE_ALREADY_EXISTS_MESSAGE)
            ->assertJsonMissingPath('errors');
    }

    public function test_store_returns_500_when_repository_throws(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->mock(LevelGemRepository::class, function ($mock) {
            $mock->shouldReceive('createForLevel')
                ->once()
                ->andThrow(new \RuntimeException('db failure'));
        });

        $this->postJson($this->gemPath($level), $this->validStorePayload())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::STORE_ERROR_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Store validation
    // -------------------------------------------------------------------------

    public function test_store_requires_core_fields(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'description',
                'thread',
                'points',
                'volume',
                'color',
                'encryption',
                'designer',
                'has_animation',
                'lines',
            ]);
    }

    public function test_store_rejects_negative_points_and_lines(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'points' => -1,
            'lines' => -5,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['points', 'lines']);
    }

    public function test_store_rejects_negative_volume(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'volume' => -0.1,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['volume']);
    }

    public function test_store_accepts_zero_numeric_boundaries(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'points' => 0,
            'volume' => 0,
            'lines' => 0,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.gem.points', 0)
            ->assertJsonPath('data.gem.volume', 0)
            ->assertJsonPath('data.gem.lines', 0);
    }

    public function test_store_rejects_non_integer_points(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'points' => 'abc',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['points']);
    }

    public function test_store_rejects_oversized_strings(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'name' => str_repeat('a', 256),
            'description' => str_repeat('b', 6001),
            'thread' => str_repeat('c', 256),
            'color' => str_repeat('d', 256),
            'designer' => str_repeat('e', 256),
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'description', 'thread', 'color', 'designer']);
    }

    public function test_store_rejects_non_png_image(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->post($this->gemPath($level), $this->validStorePayload([
            'png_file' => UploadedFile::fake()->image('gem.jpg'),
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['png_file']);
    }

    public function test_store_rejects_non_image_png_file(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->post($this->gemPath($level), $this->validStorePayload([
            'png_file' => UploadedFile::fake()->create('gem.pdf', 100, 'application/pdf'),
        ]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['png_file']);
    }

    public function test_store_rejects_invalid_fbx_key(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'fbx_file' => [
                'exe' => url('uploads/levels/malware.exe'),
            ],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fbx_file']);
    }

    public function test_store_rejects_fbx_url_with_disallowed_extension(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'fbx_file' => [
                'fbx' => url('uploads/levels/model.txt'),
            ],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fbx_file']);
    }

    public function test_store_rejects_fbx_key_extension_mismatch(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'fbx_file' => [
                'fbx' => url('uploads/levels/model.glb'),
            ],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fbx_file']);
    }

    public function test_store_rejects_non_url_fbx_values(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'fbx_file' => [
                'fbx' => 'not-a-url',
            ],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fbx_file.fbx']);
    }

    public function test_store_rejects_more_than_twenty_fbx_entries(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $files = [];
        for ($i = 1; $i <= 21; $i++) {
            $key = $i === 1 ? 'fbx' : 'fbx_'.$i;
            $files[$key] = url("uploads/levels/model-{$i}.fbx");
        }

        $this->postJson($this->gemPath($level), $this->validStorePayload([
            'fbx_file' => $files,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fbx_file']);
    }

    public function test_store_coerces_boolean_string_inputs(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->post($this->gemPath($level), $this->validStorePayload([
            'encryption' => '1',
            'has_animation' => '0',
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.gem.encryption', true)
            ->assertJsonPath('data.gem.has_animation', false);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_existing_gem(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        $this->createGem($level, ['name' => 'Old Name']);

        $this->putJson($this->gemPath($level), $this->validStorePayload([
            'name' => 'New Name',
            'points' => 55,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.gem.name', 'New Name')
            ->assertJsonPath('data.gem.points', 55);

        $this->assertDatabaseHas('level_gems', [
            'level_id' => $level->id,
            'name' => 'New Name',
            'points' => 55,
        ]);
    }

    public function test_update_returns_404_when_gem_missing(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->putJson($this->gemPath($level), $this->validStorePayload())
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::UPDATE_MISSING_MESSAGE);
    }

    public function test_update_replaces_png_and_deletes_previous_file(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $oldPath = 'levels/old-gem.png';
        Storage::disk('public')->put($oldPath, 'old-png');
        LevelGem::factory()->create([
            'level_id' => $level->id,
            'png_file' => url('uploads/'.$oldPath),
        ]);

        $response = $this->put($this->gemPath($level), $this->validStorePayload([
            'name' => 'Replaced PNG',
            'png_file' => UploadedFile::fake()->image('new-gem.png'),
        ]), ['Accept' => 'application/json'])
            ->assertOk();

        Storage::disk('public')->assertMissing($oldPath);

        $newUrl = $response->json('data.gem.png_file');
        $this->assertIsString($newUrl);
        Storage::disk('public')->assertExists($this->extractPublicPathFromUrl($newUrl));
    }

    public function test_update_merges_new_fbx_links_without_dropping_existing(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $existingFbx = url('uploads/levels/existing.fbx');
        LevelGem::factory()->create([
            'level_id' => $level->id,
            'fbx_file' => ['fbx' => $existingFbx],
        ]);

        $newGlb = url('uploads/levels/new.glb');

        $this->putJson($this->gemPath($level), $this->validStorePayload([
            'fbx_file' => [
                'glb' => $newGlb,
            ],
        ]))
            ->assertOk()
            ->assertJsonPath('data.gem.fbx_file.fbx', $existingFbx)
            ->assertJsonPath('data.gem.fbx_file.glb', $newGlb);
    }

    public function test_update_assigns_unique_suffix_for_conflicting_fbx_keys(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $existing = url('uploads/levels/a.fbx');
        LevelGem::factory()->create([
            'level_id' => $level->id,
            'fbx_file' => ['fbx' => $existing],
        ]);

        $incoming = url('uploads/levels/b.fbx');

        $response = $this->putJson($this->gemPath($level), $this->validStorePayload([
            'fbx_file' => [
                'fbx' => $incoming,
            ],
        ]))->assertOk();

        $files = $response->json('data.gem.fbx_file');
        $this->assertSame($existing, $files['fbx']);
        $this->assertSame($incoming, $files['fbx_2']);
    }

    public function test_update_skips_duplicate_fbx_url(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $existing = url('uploads/levels/same.fbx');
        LevelGem::factory()->create([
            'level_id' => $level->id,
            'fbx_file' => ['fbx' => $existing],
        ]);

        $response = $this->putJson($this->gemPath($level), $this->validStorePayload([
            'fbx_file' => [
                'fbx' => $existing,
            ],
        ]))->assertOk();

        $this->assertCount(1, $response->json('data.gem.fbx_file'));
        $this->assertSame($existing, $response->json('data.gem.fbx_file.fbx'));
    }

    public function test_update_rejects_when_merged_fbx_files_exceed_limit(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $existing = [];
        for ($i = 1; $i <= 20; $i++) {
            $key = $i === 1 ? 'fbx' : 'fbx_'.$i;
            $existing[$key] = url("uploads/levels/existing-{$i}.fbx");
        }

        LevelGem::factory()->create([
            'level_id' => $level->id,
            'fbx_file' => $existing,
        ]);

        $this->putJson($this->gemPath($level), $this->validStorePayload([
            'fbx_file' => [
                'glb' => url('uploads/levels/overflow.glb'),
            ],
        ]))
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::MERGE_LIMIT_MESSAGE);
    }

    public function test_update_without_fbx_file_preserves_existing_map(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $existing = ['fbx' => url('uploads/levels/keep.fbx')];
        LevelGem::factory()->create([
            'level_id' => $level->id,
            'fbx_file' => $existing,
            'name' => 'Keep Fbx',
        ]);

        $payload = $this->validStorePayload(['name' => 'Updated Without Fbx']);
        unset($payload['fbx_file']);

        $this->putJson($this->gemPath($level), $payload)
            ->assertOk()
            ->assertJsonPath('data.gem.name', 'Updated Without Fbx')
            ->assertJsonPath('data.gem.fbx_file.fbx', $existing['fbx']);
    }

    public function test_update_returns_500_when_repository_throws(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        $this->createGem($level);

        $this->mock(LevelGemRepository::class, function ($mock) {
            $mock->shouldReceive('update')
                ->once()
                ->andThrow(new \RuntimeException('update failed'));
        });

        $this->putJson($this->gemPath($level), $this->validStorePayload())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::UPDATE_ERROR_MESSAGE);
    }

    public function test_update_requires_validation_same_as_store(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        $this->createGem($level);

        $this->putJson($this->gemPath($level), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'points', 'encryption']);
    }

    // -------------------------------------------------------------------------
    // Destroy file
    // -------------------------------------------------------------------------

    public function test_destroy_png_file_clears_field_and_deletes_storage(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $path = 'levels/delete-me.png';
        Storage::disk('public')->put($path, 'png-content');
        LevelGem::factory()->create([
            'level_id' => $level->id,
            'png_file' => url('uploads/'.$path),
        ]);

        $this->deleteJson($this->gemFilesPath($level), ['field' => 'png_file'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_FILE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.field', 'png_file')
            ->assertJsonPath('data.png_file', null)
            ->assertJsonPath('data.gem.png_file', null);

        Storage::disk('public')->assertMissing($path);
        $this->assertNull($level->fresh()->gem->png_file);
    }

    public function test_destroy_fbx_file_entry_by_key(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $path = 'levels/model.fbx';
        Storage::disk('public')->put($path, 'fbx-content');
        $keepUrl = url('uploads/levels/keep.glb');

        LevelGem::factory()->create([
            'level_id' => $level->id,
            'fbx_file' => [
                'fbx' => url('uploads/'.$path),
                'glb' => $keepUrl,
            ],
        ]);

        $response = $this->deleteJson($this->gemFilesPath($level), [
            'field' => 'fbx_file',
            'file_key' => 'fbx',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.field', 'fbx_file')
            ->assertJsonPath('data.fbx_file.glb', $keepUrl);

        $this->assertArrayNotHasKey('fbx', $response->json('data.fbx_file'));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_destroy_last_fbx_entry_sets_field_to_null(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $path = 'levels/only.fbx';
        Storage::disk('public')->put($path, 'fbx');
        LevelGem::factory()->create([
            'level_id' => $level->id,
            'fbx_file' => [
                'fbx' => url('uploads/'.$path),
            ],
        ]);

        $this->deleteJson($this->gemFilesPath($level), [
            'field' => 'fbx_file',
            'file_key' => 'fbx',
        ])
            ->assertOk()
            ->assertJsonPath('data.fbx_file', null)
            ->assertJsonPath('data.gem.fbx_file', null);

        $this->assertNull($level->fresh()->gem->fbx_file);
    }

    public function test_destroy_file_returns_404_when_gem_missing(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->deleteJson($this->gemFilesPath($level), ['field' => 'png_file'])
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::DESTROY_FILE_MISSING_GEM_MESSAGE);
    }

    public function test_destroy_fbx_requires_file_key(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGem::factory()->withFbx()->create(['level_id' => $level->id]);

        $this->deleteJson($this->gemFilesPath($level), ['field' => 'fbx_file'])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::DESTROY_FILE_KEY_REQUIRED_MESSAGE);

        $this->deleteJson($this->gemFilesPath($level), [
            'field' => 'fbx_file',
            'file_key' => '',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', self::DESTROY_FILE_KEY_REQUIRED_MESSAGE);
    }

    public function test_destroy_png_returns_404_when_field_empty(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        $this->createGem($level, ['png_file' => null]);

        $this->deleteJson($this->gemFilesPath($level), ['field' => 'png_file'])
            ->assertNotFound()
            ->assertJsonPath('message', self::DESTROY_FILE_NOT_FOUND_MESSAGE);
    }

    public function test_destroy_fbx_returns_404_when_key_missing(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGem::factory()->create([
            'level_id' => $level->id,
            'fbx_file' => ['fbx' => url('uploads/levels/a.fbx')],
        ]);

        $this->deleteJson($this->gemFilesPath($level), [
            'field' => 'fbx_file',
            'file_key' => 'glb',
        ])
            ->assertNotFound()
            ->assertJsonPath('message', self::DESTROY_FILE_NOT_FOUND_MESSAGE);
    }

    public function test_destroy_file_requires_valid_field(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        $this->createGem($level);

        $this->deleteJson($this->gemFilesPath($level), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['field']);

        $this->deleteJson($this->gemFilesPath($level), ['field' => 'gif_file'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['field']);
    }

    public function test_destroy_file_rejects_oversized_file_key(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGem::factory()->withFbx()->create(['level_id' => $level->id]);

        $this->deleteJson($this->gemFilesPath($level), [
            'field' => 'fbx_file',
            'file_key' => str_repeat('k', 65),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file_key']);
    }

    public function test_destroy_file_returns_500_when_repository_throws(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGem::factory()->withPng()->create(['level_id' => $level->id]);

        $this->mock(LevelGemRepository::class, function ($mock) {
            $mock->shouldReceive('clearFileField')
                ->once()
                ->andThrow(new \RuntimeException('delete failed'));
        });

        $this->deleteJson($this->gemFilesPath($level), ['field' => 'png_file'])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::DESTROY_FILE_ERROR_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Security / isolation
    // -------------------------------------------------------------------------

    public function test_operations_are_scoped_to_requested_level(): void
    {
        $this->actingAsSuperAdmin();

        $levelA = Level::factory()->create();
        $levelB = Level::factory()->create();
        $this->createGem($levelA, ['name' => 'Gem A']);
        $this->createGem($levelB, ['name' => 'Gem B']);

        $this->getJson($this->gemPath($levelA))
            ->assertOk()
            ->assertJsonPath('data.gem.name', 'Gem A');

        $this->putJson($this->gemPath($levelA), $this->validStorePayload([
            'name' => 'Gem A Updated',
        ]))->assertOk();

        $this->assertDatabaseHas('level_gems', [
            'level_id' => $levelA->id,
            'name' => 'Gem A Updated',
        ]);
        $this->assertDatabaseHas('level_gems', [
            'level_id' => $levelB->id,
            'name' => 'Gem B',
        ]);
    }

    public function test_destroy_fbx_key_does_not_interpret_path_traversal_as_file_path(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $safePath = 'levels/safe.fbx';
        Storage::disk('public')->put($safePath, 'safe');
        LevelGem::factory()->create([
            'level_id' => $level->id,
            'fbx_file' => [
                'fbx' => url('uploads/'.$safePath),
            ],
        ]);

        $this->deleteJson($this->gemFilesPath($level), [
            'field' => 'fbx_file',
            'file_key' => '../fbx',
        ])
            ->assertNotFound()
            ->assertJsonPath('message', self::DESTROY_FILE_NOT_FOUND_MESSAGE);

        Storage::disk('public')->assertExists($safePath);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function gemPath(Level|int $level): string
    {
        $id = $level instanceof Level ? $level->id : $level;

        return '/api/levels/'.$id.'/gem';
    }

    private function gemFilesPath(Level|int $level): string
    {
        return $this->gemPath($level).'/files';
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validStorePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Valid Gem Name',
            'description' => 'Valid gem description for testing.',
            'thread' => 'main-thread',
            'points' => 10,
            'volume' => 1.5,
            'color' => '#7C3AED',
            'encryption' => false,
            'designer' => 'Test Designer',
            'has_animation' => false,
            'lines' => 3,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createGem(Level $level, array $overrides = []): LevelGem
    {
        return LevelGem::factory()->create(array_merge([
            'level_id' => $level->id,
        ], $overrides));
    }

    private function extractPublicPathFromUrl(string $url): string
    {
        $marker = '/uploads/';
        $pos = strpos($url, $marker);
        $this->assertNotFalse($pos, 'URL should contain /uploads/');

        return substr($url, $pos + strlen($marker));
    }
}

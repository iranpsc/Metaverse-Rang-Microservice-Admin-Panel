<?php

namespace Tests\Feature\LevelGift;

use App\Models\Level\Level;
use App\Models\Level\LevelGift;
use App\Repositories\LevelGiftRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesLevelGiftApiSchema;
use Tests\TestCase;

class LevelGiftApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesLevelGiftApiSchema;

    private const SHOW_SUCCESS_MESSAGE = 'هدیه سطح با موفقیت دریافت شد.';

    private const SHOW_EMPTY_MESSAGE = 'برای این سطح تاکنون هدیه‌ای ثبت نشده است.';

    private const STORE_SUCCESS_MESSAGE = 'هدیه سطح با موفقیت ثبت شد.';

    private const STORE_ALREADY_EXISTS_MESSAGE = 'برای این سطح هدیه‌ای ثبت شده است. لطفاً از ویرایش استفاده کنید.';

    private const UPDATE_SUCCESS_MESSAGE = 'هدیه سطح با موفقیت بروزرسانی شد.';

    private const UPDATE_MISSING_MESSAGE = 'برای این سطح هدیه‌ای ثبت نشده است.';

    private const DESTROY_FILE_SUCCESS_MESSAGE = 'فایل با موفقیت حذف شد.';

    private const DESTROY_FILE_MISSING_GIFT_MESSAGE = 'برای این سطح هدیه‌ای ثبت نشده است.';

    private const DESTROY_FILE_KEY_REQUIRED_MESSAGE = 'کلید فایل مدل برای حذف الزامی است.';

    private const DESTROY_FILE_NOT_FOUND_MESSAGE = 'فایل مورد نظر یافت نشد.';

    private const STORE_ERROR_MESSAGE = 'خطا در ثبت هدیه سطح';

    private const UPDATE_ERROR_MESSAGE = 'خطا در بروزرسانی هدیه سطح';

    private const DESTROY_FILE_ERROR_MESSAGE = 'خطا در حذف فایل هدیه سطح';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLevelGiftApiSchema();
        Storage::fake('public');
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_show_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->getJson($this->giftPath($level))->assertUnauthorized();
    }

    public function test_unauthenticated_store_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->postJson($this->giftPath($level), $this->validPayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $level = Level::factory()->create();
        LevelGift::factory()->create(['level_id' => $level->id]);

        $this->putJson($this->giftPath($level), $this->validPayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_file_returns_unauthorized(): void
    {
        $level = Level::factory()->create();
        LevelGift::factory()->create(['level_id' => $level->id]);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'png_file',
        ])->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->getJson($this->giftPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_EMPTY_MESSAGE)
            ->assertJsonPath('data.gift', null);

        $this->postJson($this->giftPath($level), $this->validPayload([
            'name' => 'Super admin gift',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.gift.name', 'Super admin gift');

        $this->putJson($this->giftPath($level), $this->validPayload([
            'name' => 'Updated by super',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.gift.name', 'Updated by super');

        $gift = $level->fresh()->gift;
        $gift->update(['png_file' => url('uploads/levels/super.png')]);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'png_file',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_FILE_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $level = Level::factory()->create();

        $this->getJson($this->giftPath($level))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson($this->giftPath($level), $this->validPayload([
            'name' => 'Regular admin gift',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->putJson($this->giftPath($level), $this->validPayload([
            'name' => 'Updated by regular',
        ]))->assertOk();

        $level->fresh()->gift->update(['gif_file' => url('uploads/levels/regular.gif')]);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'gif_file',
        ])->assertOk();
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_null_gift_when_none_registered(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->getJson($this->giftPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.gift', null)
            ->assertJsonPath('message', self::SHOW_EMPTY_MESSAGE);
    }

    public function test_show_returns_full_gift_resource_structure(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $gift = LevelGift::factory()
            ->withPngFile(url('uploads/levels/gift.png'))
            ->withGifFile(url('uploads/levels/gift.gif'))
            ->withFbxFiles([
                'fbx' => 'https://cdn.example.com/models/gift.fbx',
                'glb' => 'https://cdn.example.com/models/gift.glb',
            ])
            ->create([
                'level_id' => $level->id,
                'name' => 'Structure gift',
                'description' => 'Structure description',
                'monthly_capacity_count' => 12,
                'store_capacity' => true,
                'sell_capacity' => false,
                'features' => 'feature-a',
                'sell' => true,
                'vod_document_registration' => false,
                'seller_link' => 'https://seller.example.com',
                'designer' => 'Designer Name',
                'three_d_model_volume' => 1.2500,
                'three_d_model_points' => 100,
                'three_d_model_lines' => 50,
                'has_animation' => true,
                'rent' => false,
                'vod_count' => 3,
                'start_vod_id' => 'vod-start',
                'end_vod_id' => 'vod-end',
            ]);

        $response = $this->getJson($this->giftPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'gift' => [
                        'id',
                        'name',
                        'description',
                        'monthly_capacity_count',
                        'store_capacity',
                        'sell_capacity',
                        'features',
                        'sell',
                        'vod_document_registration',
                        'seller_link',
                        'designer',
                        'three_d_model_volume',
                        'three_d_model_points',
                        'three_d_model_lines',
                        'has_animation',
                        'png_file',
                        'fbx_file',
                        'gif_file',
                        'rent',
                        'vod_count',
                        'start_vod_id',
                        'end_vod_id',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $payload = $response->json('data.gift');

        $this->assertSame($gift->id, $payload['id']);
        $this->assertSame('Structure gift', $payload['name']);
        $this->assertSame(12, $payload['monthly_capacity_count']);
        $this->assertTrue($payload['store_capacity']);
        $this->assertFalse($payload['sell_capacity']);
        $this->assertIsFloat($payload['three_d_model_volume']);
        $this->assertSame(1.25, $payload['three_d_model_volume']);
        $this->assertIsInt($payload['three_d_model_points']);
        $this->assertIsBool($payload['has_animation']);
        $this->assertSame([
            'fbx' => 'https://cdn.example.com/models/gift.fbx',
            'glb' => 'https://cdn.example.com/models/gift.glb',
        ], $payload['fbx_file']);
    }

    public function test_show_unknown_level_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson('/api/levels/999999/gift')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Store — happy path
    // -------------------------------------------------------------------------

    public function test_store_creates_gift_without_files(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $response = $this->postJson($this->giftPath($level), $this->validPayload([
            'name' => '  Trimmed gift  ',
            'description' => '  Trimmed description  ',
            'seller_link' => '  https://seller.test  ',
            'designer' => '  Designer  ',
            'features' => '  features  ',
            'start_vod_id' => '  start  ',
            'end_vod_id' => '  end  ',
            'fbx_file' => [
                'fbx' => 'https://cdn.example.com/a.fbx',
            ],
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.gift.name', 'Trimmed gift')
            ->assertJsonPath('data.gift.description', 'Trimmed description')
            ->assertJsonPath('data.gift.seller_link', 'https://seller.test')
            ->assertJsonPath('data.gift.designer', 'Designer')
            ->assertJsonPath('data.gift.features', 'features')
            ->assertJsonPath('data.gift.start_vod_id', 'start')
            ->assertJsonPath('data.gift.end_vod_id', 'end')
            ->assertJsonPath('data.gift.fbx_file.fbx', 'https://cdn.example.com/a.fbx');

        $this->assertDatabaseHas('level_gifts', [
            'level_id' => $level->id,
            'name' => 'Trimmed gift',
            'designer' => 'Designer',
        ]);

        $this->assertTrue($response->json('data.gift.store_capacity'));
        $this->assertIsInt($response->json('data.gift.vod_count'));
    }

    public function test_store_uploads_png_and_gif_files(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $response = $this->post($this->giftPath($level), $this->validMultipartPayload([
            'name' => 'Gift with files',
            'png_file' => UploadedFile::fake()->image('gift.png'),
            'gif_file' => UploadedFile::fake()->create('gift.gif', 100, 'image/gif'),
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.gift.name', 'Gift with files');

        $pngUrl = $response->json('data.gift.png_file');
        $gifUrl = $response->json('data.gift.gif_file');

        $this->assertIsString($pngUrl);
        $this->assertIsString($gifUrl);
        $this->assertStringContainsString('/uploads/levels/', $pngUrl);
        $this->assertStringContainsString('/uploads/levels/', $gifUrl);

        $pngPath = $this->extractPublicPathFromUrl($pngUrl);
        $gifPath = $this->extractPublicPathFromUrl($gifUrl);

        Storage::disk('public')->assertExists($pngPath);
        Storage::disk('public')->assertExists($gifPath);
    }

    public function test_store_accepts_compatible_jpeg_fbx_key_and_jpg_url(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->giftPath($level), $this->validPayload([
            'fbx_file' => [
                'jpeg' => 'https://cdn.example.com/textures/model.jpg',
            ],
        ]))
            ->assertCreated()
            ->assertJsonPath('data.gift.fbx_file.jpeg', 'https://cdn.example.com/textures/model.jpg');
    }

    public function test_store_rejects_when_gift_already_exists(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGift::factory()->create(['level_id' => $level->id]);

        $this->postJson($this->giftPath($level), $this->validPayload())
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::STORE_ALREADY_EXISTS_MESSAGE)
            ->assertJsonMissingPath('data');
    }

    public function test_store_returns_server_error_when_repository_throws(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->mock(LevelGiftRepository::class, function ($mock) {
            $mock->shouldReceive('createForLevel')
                ->once()
                ->andThrow(new \RuntimeException('db failure'));
        });

        $this->postJson($this->giftPath($level), $this->validPayload())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::STORE_ERROR_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Store — validation
    // -------------------------------------------------------------------------

    public function test_store_requires_core_fields(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->giftPath($level), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'description',
                'monthly_capacity_count',
                'store_capacity',
                'sell_capacity',
                'features',
                'sell',
                'vod_document_registration',
                'seller_link',
                'designer',
                'three_d_model_volume',
                'three_d_model_points',
                'three_d_model_lines',
                'has_animation',
                'rent',
                'vod_count',
            ]);
    }

    public function test_store_rejects_negative_numeric_fields(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->giftPath($level), $this->validPayload([
            'monthly_capacity_count' => -1,
            'three_d_model_volume' => -0.1,
            'three_d_model_points' => -5,
            'three_d_model_lines' => -2,
            'vod_count' => -1,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'monthly_capacity_count',
                'three_d_model_volume',
                'three_d_model_points',
                'three_d_model_lines',
                'vod_count',
            ]);
    }

    public function test_store_rejects_non_boolean_flags(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->giftPath($level), $this->validPayload([
            'store_capacity' => 'yes',
            'sell_capacity' => 'no',
            'sell' => 'maybe',
            'vod_document_registration' => 'trueish',
            'has_animation' => 'on',
            'rent' => 'off',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'store_capacity',
                'sell_capacity',
                'sell',
                'vod_document_registration',
                'has_animation',
                'rent',
            ]);
    }

    public function test_store_rejects_oversized_string_fields(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->giftPath($level), $this->validPayload([
            'name' => Str::random(256),
            'description' => Str::random(6001),
            'features' => Str::random(5001),
            'seller_link' => Str::random(256),
            'designer' => Str::random(256),
            'start_vod_id' => Str::random(256),
            'end_vod_id' => Str::random(256),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'description',
                'features',
                'seller_link',
                'designer',
                'start_vod_id',
                'end_vod_id',
            ]);
    }

    public function test_store_rejects_invalid_png_and_gif_uploads(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->post($this->giftPath($level), $this->validMultipartPayload([
            'png_file' => UploadedFile::fake()->create('not-png.jpg', 100, 'image/jpeg'),
            'gif_file' => UploadedFile::fake()->create('not-gif.png', 100, 'image/png'),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['png_file', 'gif_file']);
    }

    public function test_store_rejects_invalid_fbx_file_key(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->giftPath($level), $this->validPayload([
            'fbx_file' => [
                'exe' => 'https://cdn.example.com/malware.exe',
            ],
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['fbx_file']);
    }

    public function test_store_rejects_fbx_url_extension_mismatch(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->giftPath($level), $this->validPayload([
            'fbx_file' => [
                'fbx' => 'https://cdn.example.com/models/gift.glb',
            ],
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['fbx_file']);
    }

    public function test_store_rejects_non_url_fbx_values(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->giftPath($level), $this->validPayload([
            'fbx_file' => [
                'fbx' => 'not-a-url',
            ],
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['fbx_file.fbx']);
    }

    public function test_store_rejects_more_than_twenty_fbx_entries(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $fbxFile = [];
        for ($i = 1; $i <= 21; $i++) {
            $key = $i === 1 ? 'fbx' : "fbx_{$i}";
            $fbxFile[$key] = "https://cdn.example.com/models/gift_{$i}.fbx";
        }

        $this->postJson($this->giftPath($level), $this->validPayload([
            'fbx_file' => $fbxFile,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['fbx_file']);
    }

    public function test_store_decodes_json_string_fbx_file_from_form_request(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $payload = $this->validMultipartPayload([
            'name' => 'JSON fbx gift',
            'fbx_file' => json_encode([
                'glb' => 'https://cdn.example.com/models/decoded.glb',
            ], JSON_UNESCAPED_SLASHES),
        ]);

        $this->post($this->giftPath($level), $payload, ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.gift.fbx_file.glb', 'https://cdn.example.com/models/decoded.glb');
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_existing_gift(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGift::factory()->create([
            'level_id' => $level->id,
            'name' => 'Original',
            'vod_count' => 1,
        ]);

        $this->putJson($this->giftPath($level), $this->validPayload([
            'name' => 'Updated gift',
            'vod_count' => 9,
            'three_d_model_volume' => 3.5,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.gift.name', 'Updated gift')
            ->assertJsonPath('data.gift.vod_count', 9)
            ->assertJsonPath('data.gift.three_d_model_volume', 3.5);

        $this->assertDatabaseHas('level_gifts', [
            'level_id' => $level->id,
            'name' => 'Updated gift',
            'vod_count' => 9,
        ]);
    }

    public function test_update_merges_incoming_fbx_files_with_existing(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGift::factory()
            ->withFbxFiles([
                'fbx' => 'https://cdn.example.com/old.fbx',
            ])
            ->create(['level_id' => $level->id]);

        $response = $this->putJson($this->giftPath($level), $this->validPayload([
            'fbx_file' => [
                'glb' => 'https://cdn.example.com/new.glb',
            ],
        ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $fbxFile = $response->json('data.gift.fbx_file');

        $this->assertSame('https://cdn.example.com/old.fbx', $fbxFile['fbx']);
        $this->assertSame('https://cdn.example.com/new.glb', $fbxFile['glb']);
    }

    public function test_update_suffixes_conflicting_fbx_keys(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGift::factory()
            ->withFbxFiles([
                'fbx' => 'https://cdn.example.com/old.fbx',
            ])
            ->create(['level_id' => $level->id]);

        $response = $this->putJson($this->giftPath($level), $this->validPayload([
            'fbx_file' => [
                'fbx' => 'https://cdn.example.com/new.fbx',
            ],
        ]))->assertOk();

        $fbxFile = $response->json('data.gift.fbx_file');

        $this->assertSame('https://cdn.example.com/old.fbx', $fbxFile['fbx']);
        $this->assertSame('https://cdn.example.com/new.fbx', $fbxFile['fbx_2']);
    }

    public function test_update_replaces_uploaded_png_and_cleans_previous_file(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        Storage::disk('public')->put('levels/old.png', 'old-png');
        $oldUrl = url('uploads/levels/old.png');

        LevelGift::factory()
            ->withPngFile($oldUrl)
            ->create(['level_id' => $level->id]);

        $response = $this->post($this->giftPath($level), array_merge(
            $this->validMultipartPayload([
                'name' => 'Replaced png gift',
                'png_file' => UploadedFile::fake()->image('new.png'),
                '_method' => 'PUT',
            ])
        ), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $newUrl = $response->json('data.gift.png_file');
        $this->assertNotSame($oldUrl, $newUrl);
        Storage::disk('public')->assertMissing('levels/old.png');
        Storage::disk('public')->assertExists($this->extractPublicPathFromUrl($newUrl));
    }

    public function test_update_returns_not_found_when_gift_missing(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->putJson($this->giftPath($level), $this->validPayload())
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::UPDATE_MISSING_MESSAGE);
    }

    public function test_update_rejects_when_merged_fbx_files_exceed_limit(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $existing = [];
        for ($i = 1; $i <= 20; $i++) {
            $key = $i === 1 ? 'fbx' : "fbx_{$i}";
            $existing[$key] = "https://cdn.example.com/existing_{$i}.fbx";
        }

        LevelGift::factory()
            ->withFbxFiles($existing)
            ->create(['level_id' => $level->id]);

        $this->putJson($this->giftPath($level), $this->validPayload([
            'fbx_file' => [
                'glb' => 'https://cdn.example.com/extra.glb',
            ],
        ]))
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'حداکثر ۲۰ فایل مدل می‌توانید ذخیره کنید. ابتدا برخی فایل‌های قبلی را حذف کنید.'
            );
    }

    public function test_update_returns_server_error_when_repository_throws(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGift::factory()->create(['level_id' => $level->id]);

        $this->mock(LevelGiftRepository::class, function ($mock) {
            $mock->shouldReceive('update')
                ->once()
                ->andThrow(new \RuntimeException('update failed'));
        });

        $this->putJson($this->giftPath($level), $this->validPayload())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::UPDATE_ERROR_MESSAGE);
    }

    public function test_update_requires_same_validation_as_store(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGift::factory()->create(['level_id' => $level->id]);

        $this->putJson($this->giftPath($level), [
            'name' => '',
            'monthly_capacity_count' => -1,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description', 'monthly_capacity_count']);
    }

    // -------------------------------------------------------------------------
    // Destroy file
    // -------------------------------------------------------------------------

    public function test_destroy_png_file_clears_field_and_storage(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        Storage::disk('public')->put('levels/delete-me.png', 'png-bytes');
        $url = url('uploads/levels/delete-me.png');

        LevelGift::factory()
            ->withPngFile($url)
            ->create(['level_id' => $level->id]);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'png_file',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_FILE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.field', 'png_file')
            ->assertJsonPath('data.png_file', null)
            ->assertJsonPath('data.gift.png_file', null);

        Storage::disk('public')->assertMissing('levels/delete-me.png');
        $this->assertDatabaseHas('level_gifts', [
            'level_id' => $level->id,
            'png_file' => null,
        ]);
    }

    public function test_destroy_gif_file_clears_field(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        LevelGift::factory()
            ->withGifFile(url('uploads/levels/anim.gif'))
            ->create(['level_id' => $level->id]);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'gif_file',
        ])
            ->assertOk()
            ->assertJsonPath('data.field', 'gif_file')
            ->assertJsonPath('data.gif_file', null);
    }

    public function test_destroy_fbx_file_entry_by_key(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        Storage::disk('public')->put('levels/model.fbx', 'fbx-bytes');
        $fbxUrl = url('uploads/levels/model.fbx');

        LevelGift::factory()
            ->withFbxFiles([
                'fbx' => $fbxUrl,
                'glb' => 'https://cdn.example.com/keep.glb',
            ])
            ->create(['level_id' => $level->id]);

        $response = $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'fbx_file',
            'file_key' => 'fbx',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.field', 'fbx_file');

        $remaining = $response->json('data.fbx_file');
        $this->assertArrayNotHasKey('fbx', $remaining);
        $this->assertSame('https://cdn.example.com/keep.glb', $remaining['glb']);
        Storage::disk('public')->assertMissing('levels/model.fbx');
    }

    public function test_destroy_last_fbx_entry_sets_fbx_file_to_null(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        LevelGift::factory()
            ->withFbxFiles([
                'fbx' => 'https://cdn.example.com/only.fbx',
            ])
            ->create(['level_id' => $level->id]);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'fbx_file',
            'file_key' => 'fbx',
        ])
            ->assertOk()
            ->assertJsonPath('data.fbx_file', null)
            ->assertJsonPath('data.gift.fbx_file', null);
    }

    public function test_destroy_file_requires_file_key_for_fbx_field(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGift::factory()
            ->withFbxFiles(['fbx' => 'https://cdn.example.com/a.fbx'])
            ->create(['level_id' => $level->id]);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'fbx_file',
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::DESTROY_FILE_KEY_REQUIRED_MESSAGE);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'fbx_file',
            'file_key' => '',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', self::DESTROY_FILE_KEY_REQUIRED_MESSAGE);
    }

    public function test_destroy_file_returns_not_found_for_missing_fbx_key(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGift::factory()
            ->withFbxFiles(['fbx' => 'https://cdn.example.com/a.fbx'])
            ->create(['level_id' => $level->id]);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'fbx_file',
            'file_key' => 'missing',
        ])
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::DESTROY_FILE_NOT_FOUND_MESSAGE);
    }

    public function test_destroy_file_returns_not_found_when_png_already_empty(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGift::factory()->create([
            'level_id' => $level->id,
            'png_file' => null,
        ]);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'png_file',
        ])
            ->assertNotFound()
            ->assertJsonPath('message', self::DESTROY_FILE_NOT_FOUND_MESSAGE);
    }

    public function test_destroy_file_returns_not_found_when_gift_missing(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'png_file',
        ])
            ->assertNotFound()
            ->assertJsonPath('message', self::DESTROY_FILE_MISSING_GIFT_MESSAGE);
    }

    public function test_destroy_file_validates_field_and_file_key(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGift::factory()->create(['level_id' => $level->id]);

        $this->deleteJson($this->giftFilesPath($level), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['field']);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'unknown_field',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['field']);

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'png_file',
            'file_key' => Str::random(65),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file_key']);
    }

    public function test_destroy_file_returns_server_error_when_repository_throws(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGift::factory()
            ->withPngFile(url('uploads/levels/x.png'))
            ->create(['level_id' => $level->id]);

        $this->mock(LevelGiftRepository::class, function ($mock) {
            $mock->shouldReceive('clearFileField')
                ->once()
                ->andThrow(new \RuntimeException('delete failed'));
        });

        $this->deleteJson($this->giftFilesPath($level), [
            'field' => 'png_file',
        ])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::DESTROY_FILE_ERROR_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Edge / security
    // -------------------------------------------------------------------------

    public function test_store_accepts_boundary_zero_numeric_values(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->giftPath($level), $this->validPayload([
            'monthly_capacity_count' => 0,
            'three_d_model_volume' => 0,
            'three_d_model_points' => 0,
            'three_d_model_lines' => 0,
            'vod_count' => 0,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.gift.monthly_capacity_count', 0)
            ->assertJsonPath('data.gift.three_d_model_volume', 0)
            ->assertJsonPath('data.gift.vod_count', 0);
    }

    public function test_store_accepts_unicode_text_fields(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->giftPath($level), $this->validPayload([
            'name' => 'هدیه ویژه سطح',
            'description' => 'توضیحات فارسی با ایموجی 🎁',
            'features' => 'قابلیت‌های یونیکد',
            'designer' => 'طراح متاورس',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.gift.name', 'هدیه ویژه سطح')
            ->assertJsonPath('data.gift.designer', 'طراح متاورس');
    }

    public function test_store_does_not_leak_exception_details_on_server_error(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->mock(LevelGiftRepository::class, function ($mock) {
            $mock->shouldReceive('createForLevel')
                ->once()
                ->andThrow(new \RuntimeException('secret-db-password-leaked'));
        });

        $response = $this->postJson($this->giftPath($level), $this->validPayload())
            ->assertStatus(500)
            ->assertJsonPath('message', self::STORE_ERROR_MESSAGE);

        $this->assertStringNotContainsString(
            'secret-db-password-leaked',
            (string) $response->getContent()
        );
    }

    public function test_store_skips_duplicate_fbx_url_on_update_merge(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        $url = 'https://cdn.example.com/same.fbx';

        LevelGift::factory()
            ->withFbxFiles(['fbx' => $url])
            ->create(['level_id' => $level->id]);

        $response = $this->putJson($this->giftPath($level), $this->validPayload([
            'fbx_file' => [
                'fbx' => $url,
            ],
        ]))->assertOk();

        $this->assertCount(1, $response->json('data.gift.fbx_file'));
        $this->assertSame($url, $response->json('data.gift.fbx_file.fbx'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function giftPath(Level $level): string
    {
        return '/api/levels/'.$level->id.'/gift';
    }

    private function giftFilesPath(Level $level): string
    {
        return '/api/levels/'.$level->id.'/gift/files';
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Valid gift name',
            'description' => 'Valid gift description',
            'monthly_capacity_count' => 10,
            'store_capacity' => true,
            'sell_capacity' => false,
            'features' => 'feature list',
            'sell' => true,
            'vod_document_registration' => false,
            'seller_link' => 'https://seller.example.com/gift',
            'designer' => 'Valid Designer',
            'three_d_model_volume' => 1.5,
            'three_d_model_points' => 100,
            'three_d_model_lines' => 50,
            'has_animation' => false,
            'rent' => false,
            'vod_count' => 2,
            'start_vod_id' => 'start-1',
            'end_vod_id' => 'end-1',
        ], $overrides);
    }

    /**
     * Multipart-friendly payload (booleans as 0/1 strings).
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validMultipartPayload(array $overrides = []): array
    {
        $payload = $this->validPayload($overrides);

        foreach ([
            'store_capacity',
            'sell_capacity',
            'sell',
            'vod_document_registration',
            'has_animation',
            'rent',
        ] as $booleanField) {
            if (array_key_exists($booleanField, $payload) && is_bool($payload[$booleanField])) {
                $payload[$booleanField] = $payload[$booleanField] ? '1' : '0';
            }
        }

        return $payload;
    }

    private function extractPublicPathFromUrl(string $url): string
    {
        $marker = '/uploads/';
        $pos = strpos($url, $marker);
        $this->assertNotFalse($pos, 'URL should contain /uploads/');

        return substr($url, $pos + strlen($marker));
    }
}

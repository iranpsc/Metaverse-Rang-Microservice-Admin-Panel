<?php

namespace Tests\Feature\LevelGeneralInfo;

use App\Models\Level\Level;
use App\Models\Level\LevelGeneralInfo;
use App\Repositories\LevelGeneralInfoRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesLevelGeneralInfoApiSchema;
use Tests\TestCase;

class LevelGeneralInfoApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesLevelGeneralInfoApiSchema;

    private const SHOW_SUCCESS_MESSAGE = 'اطلاعات کلی سطح با موفقیت دریافت شد.';

    private const SHOW_EMPTY_MESSAGE = 'برای این سطح تاکنون اطلاعات کلی ثبت نشده است.';

    private const STORE_SUCCESS_MESSAGE = 'اطلاعات کلی سطح با موفقیت ثبت شد.';

    private const STORE_ALREADY_EXISTS_MESSAGE = 'برای این سطح اطلاعات کلی ثبت شده است. لطفاً از ویرایش استفاده کنید.';

    private const UPDATE_SUCCESS_MESSAGE = 'اطلاعات کلی سطح با موفقیت بروزرسانی شد.';

    private const UPDATE_MISSING_MESSAGE = 'برای این سطح اطلاعات کلی ثبت نشده است.';

    private const DESTROY_FILE_SUCCESS_MESSAGE = 'فایل با موفقیت حذف شد.';

    private const DESTROY_FILE_MISSING_ENTITY_MESSAGE = 'برای این سطح اطلاعات کلی ثبت نشده است.';

    private const DESTROY_FILE_KEY_REQUIRED_MESSAGE = 'کلید فایل مدل برای حذف الزامی است.';

    private const DESTROY_FILE_NOT_FOUND_MESSAGE = 'فایل مورد نظر یافت نشد.';

    private const STORE_ERROR_MESSAGE = 'خطا در ثبت اطلاعات کلی سطح';

    private const UPDATE_ERROR_MESSAGE = 'خطا در بروزرسانی اطلاعات کلی سطح';

    private const DESTROY_FILE_ERROR_MESSAGE = 'خطا در حذف فایل اطلاعات کلی سطح';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLevelGeneralInfoApiSchema();
        Storage::fake('public');
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_show_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->getJson($this->generalInfoPath($level))->assertUnauthorized();
    }

    public function test_unauthenticated_store_returns_unauthorized(): void
    {
        $level = Level::factory()->create();

        $this->postJson($this->generalInfoPath($level), $this->validPayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $level = Level::factory()->create();
        LevelGeneralInfo::factory()->create(['level_id' => $level->id]);

        $this->putJson($this->generalInfoPath($level), $this->validPayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_file_returns_unauthorized(): void
    {
        $level = Level::factory()->create();
        LevelGeneralInfo::factory()->create(['level_id' => $level->id]);

        $this->deleteJson($this->generalInfoFilesPath($level), [
            'field' => 'png_file',
        ])->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();

        $this->getJson($this->generalInfoPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_EMPTY_MESSAGE)
            ->assertJsonPath('data.general_info', null);

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
            'designer' => 'Super admin designer',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.general_info.designer', 'Super admin designer');

        $this->putJson($this->generalInfoPath($level), $this->validPayload([
            'designer' => 'Updated by super',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.general_info.designer', 'Updated by super');

        $generalInfo = $level->fresh()->generalInfo;
        $generalInfo->update(['png_file' => url('uploads/levels/super.png')]);

        $this->deleteJson($this->generalInfoFilesPath($level), [
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

        $this->getJson($this->generalInfoPath($level))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
            'designer' => 'Regular admin designer',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->putJson($this->generalInfoPath($level), $this->validPayload([
            'designer' => 'Updated by regular',
        ]))->assertOk();

        $level->fresh()->generalInfo->update(['gif_file' => url('uploads/levels/regular.gif')]);

        $this->deleteJson($this->generalInfoFilesPath($level), [
            'field' => 'gif_file',
        ])->assertOk();
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_null_general_info_when_none_registered(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->getJson($this->generalInfoPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.general_info', null)
            ->assertJsonPath('message', self::SHOW_EMPTY_MESSAGE);
    }

    public function test_show_returns_full_general_info_resource_structure(): void
    {
        $this->actingAsSuperAdmin();

        $level = Level::factory()->create();
        $generalInfo = LevelGeneralInfo::factory()
            ->withPngFile(url('uploads/levels/info.png'))
            ->withGifFile(url('uploads/levels/info.gif'))
            ->withFbxFiles([
                'fbx' => 'https://cdn.example.com/models/info.fbx',
                'glb' => 'https://cdn.example.com/models/info.glb',
            ])
            ->create([
                'level_id' => $level->id,
                'score' => 150,
                'description' => 'Structure description',
                'rank' => 3,
                'subcategories' => 5,
                'persian_font' => 'IranSans',
                'english_font' => 'Inter',
                'file_volume' => 1.250,
                'used_colors' => '#7C3AED,#06B6D4',
                'points' => 100,
                'designer' => 'Designer Name',
                'model_designer' => 'Model Designer',
                'creation_date' => '2024-01-15',
                'has_animation' => true,
                'lines' => 50,
            ]);

        $response = $this->getJson($this->generalInfoPath($level))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'general_info' => [
                        'id',
                        'score',
                        'description',
                        'rank',
                        'subcategories',
                        'persian_font',
                        'english_font',
                        'file_volume',
                        'used_colors',
                        'points',
                        'designer',
                        'model_designer',
                        'creation_date',
                        'has_animation',
                        'lines',
                        'png_file',
                        'fbx_file',
                        'gif_file',
                    ],
                ],
            ]);

        $payload = $response->json('data.general_info');

        $this->assertSame($generalInfo->id, $payload['id']);
        $this->assertSame(150, $payload['score']);
        $this->assertSame('Structure description', $payload['description']);
        $this->assertSame(3, $payload['rank']);
        $this->assertSame(5, $payload['subcategories']);
        $this->assertIsFloat($payload['file_volume']);
        $this->assertSame(1.25, $payload['file_volume']);
        $this->assertIsInt($payload['points']);
        $this->assertIsBool($payload['has_animation']);
        $this->assertSame([
            'fbx' => 'https://cdn.example.com/models/info.fbx',
            'glb' => 'https://cdn.example.com/models/info.glb',
        ], $payload['fbx_file']);
    }

    public function test_show_unknown_level_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson('/api/levels/999999/general-info')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Store — happy path
    // -------------------------------------------------------------------------

    public function test_store_creates_general_info_without_files(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $response = $this->postJson($this->generalInfoPath($level), $this->validPayload([
            'description' => '  Trimmed description  ',
            'used_colors' => '  #fff,#000  ',
            'persian_font' => '  IranSans  ',
            'english_font' => '  Inter  ',
            'designer' => '  Designer  ',
            'model_designer' => '  Model Designer  ',
            'creation_date' => '  2024-06-01  ',
            'fbx_file' => [
                'fbx' => 'https://cdn.example.com/a.fbx',
            ],
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.general_info.description', 'Trimmed description')
            ->assertJsonPath('data.general_info.used_colors', '#fff,#000')
            ->assertJsonPath('data.general_info.persian_font', 'IranSans')
            ->assertJsonPath('data.general_info.english_font', 'Inter')
            ->assertJsonPath('data.general_info.designer', 'Designer')
            ->assertJsonPath('data.general_info.model_designer', 'Model Designer')
            ->assertJsonPath('data.general_info.creation_date', '2024-06-01')
            ->assertJsonPath('data.general_info.fbx_file.fbx', 'https://cdn.example.com/a.fbx');

        $this->assertDatabaseHas('level_general_infos', [
            'level_id' => $level->id,
            'designer' => 'Designer',
            'model_designer' => 'Model Designer',
        ]);

        $this->assertIsInt($response->json('data.general_info.score'));
        $this->assertIsInt($response->json('data.general_info.points'));
    }

    public function test_store_uploads_png_and_gif_files(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $response = $this->post($this->generalInfoPath($level), $this->validMultipartPayload([
            'designer' => 'Info with files',
            'png_file' => UploadedFile::fake()->image('info.png'),
            'gif_file' => UploadedFile::fake()->create('info.gif', 100, 'image/gif'),
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.general_info.designer', 'Info with files');

        $pngUrl = $response->json('data.general_info.png_file');
        $gifUrl = $response->json('data.general_info.gif_file');

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

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
            'fbx_file' => [
                'jpeg' => 'https://cdn.example.com/textures/model.jpg',
            ],
        ]))
            ->assertCreated()
            ->assertJsonPath('data.general_info.fbx_file.jpeg', 'https://cdn.example.com/textures/model.jpg');
    }

    public function test_store_rejects_when_general_info_already_exists(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGeneralInfo::factory()->create(['level_id' => $level->id]);

        $this->postJson($this->generalInfoPath($level), $this->validPayload())
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::STORE_ALREADY_EXISTS_MESSAGE)
            ->assertJsonMissingPath('data');
    }

    public function test_store_returns_server_error_when_repository_throws(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->mock(LevelGeneralInfoRepository::class, function ($mock) {
            $mock->shouldReceive('createForLevel')
                ->once()
                ->andThrow(new \RuntimeException('db failure'));
        });

        $this->postJson($this->generalInfoPath($level), $this->validPayload())
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

        $this->postJson($this->generalInfoPath($level), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'score',
                'description',
                'rank',
                'subcategories',
                'persian_font',
                'english_font',
                'file_volume',
                'used_colors',
                'points',
                'designer',
                'model_designer',
                'creation_date',
                'has_animation',
                'lines',
            ]);
    }

    public function test_store_rejects_negative_numeric_fields(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
            'score' => -1,
            'rank' => -2,
            'subcategories' => -3,
            'file_volume' => -0.1,
            'points' => -5,
            'lines' => -2,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'score',
                'rank',
                'subcategories',
                'file_volume',
                'points',
                'lines',
            ]);
    }

    public function test_store_rejects_non_boolean_flags(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
            'has_animation' => 'on',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'has_animation',
            ]);
    }

    public function test_store_rejects_oversized_string_fields(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
            'description' => Str::random(6001),
            'persian_font' => Str::random(256),
            'english_font' => Str::random(256),
            'used_colors' => Str::random(501),
            'designer' => Str::random(256),
            'model_designer' => Str::random(256),
            'creation_date' => Str::random(256),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'description',
                'persian_font',
                'english_font',
                'used_colors',
                'designer',
                'model_designer',
                'creation_date',
            ]);
    }

    public function test_store_rejects_invalid_png_and_gif_uploads(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->post($this->generalInfoPath($level), $this->validMultipartPayload([
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

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
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

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
            'fbx_file' => [
                'fbx' => 'https://cdn.example.com/models/info.glb',
            ],
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['fbx_file']);
    }

    public function test_store_rejects_non_url_fbx_values(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
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
            $fbxFile[$key] = "https://cdn.example.com/models/info_{$i}.fbx";
        }

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
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
            'designer' => 'JSON fbx info',
            'fbx_file' => json_encode([
                'glb' => 'https://cdn.example.com/models/decoded.glb',
            ], JSON_UNESCAPED_SLASHES),
        ]);

        $this->post($this->generalInfoPath($level), $payload, ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.general_info.fbx_file.glb', 'https://cdn.example.com/models/decoded.glb');
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_existing_general_info(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGeneralInfo::factory()->create([
            'level_id' => $level->id,
            'designer' => 'Original',
            'points' => 1,
        ]);

        $this->putJson($this->generalInfoPath($level), $this->validPayload([
            'designer' => 'Updated info',
            'points' => 9,
            'file_volume' => 3.5,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.general_info.designer', 'Updated info')
            ->assertJsonPath('data.general_info.points', 9)
            ->assertJsonPath('data.general_info.file_volume', 3.5);

        $this->assertDatabaseHas('level_general_infos', [
            'level_id' => $level->id,
            'designer' => 'Updated info',
            'points' => 9,
        ]);
    }

    public function test_update_merges_incoming_fbx_files_with_existing(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGeneralInfo::factory()
            ->withFbxFiles([
                'fbx' => 'https://cdn.example.com/old.fbx',
            ])
            ->create(['level_id' => $level->id]);

        $response = $this->putJson($this->generalInfoPath($level), $this->validPayload([
            'fbx_file' => [
                'glb' => 'https://cdn.example.com/new.glb',
            ],
        ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $fbxFile = $response->json('data.general_info.fbx_file');

        $this->assertSame('https://cdn.example.com/old.fbx', $fbxFile['fbx']);
        $this->assertSame('https://cdn.example.com/new.glb', $fbxFile['glb']);
    }

    public function test_update_suffixes_conflicting_fbx_keys(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGeneralInfo::factory()
            ->withFbxFiles([
                'fbx' => 'https://cdn.example.com/old.fbx',
            ])
            ->create(['level_id' => $level->id]);

        $response = $this->putJson($this->generalInfoPath($level), $this->validPayload([
            'fbx_file' => [
                'fbx' => 'https://cdn.example.com/new.fbx',
            ],
        ]))->assertOk();

        $fbxFile = $response->json('data.general_info.fbx_file');

        $this->assertSame('https://cdn.example.com/old.fbx', $fbxFile['fbx']);
        $this->assertSame('https://cdn.example.com/new.fbx', $fbxFile['fbx_2']);
    }

    public function test_update_replaces_uploaded_png_and_cleans_previous_file(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        Storage::disk('public')->put('levels/old.png', 'old-png');
        $oldUrl = url('uploads/levels/old.png');

        LevelGeneralInfo::factory()
            ->withPngFile($oldUrl)
            ->create(['level_id' => $level->id]);

        $response = $this->post($this->generalInfoPath($level), array_merge(
            $this->validMultipartPayload([
                'designer' => 'Replaced png info',
                'png_file' => UploadedFile::fake()->image('new.png'),
                '_method' => 'PUT',
            ])
        ), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $newUrl = $response->json('data.general_info.png_file');
        $this->assertNotSame($oldUrl, $newUrl);
        Storage::disk('public')->assertMissing('levels/old.png');
        Storage::disk('public')->assertExists($this->extractPublicPathFromUrl($newUrl));
    }

    public function test_update_returns_not_found_when_general_info_missing(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->putJson($this->generalInfoPath($level), $this->validPayload())
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

        LevelGeneralInfo::factory()
            ->withFbxFiles($existing)
            ->create(['level_id' => $level->id]);

        $this->putJson($this->generalInfoPath($level), $this->validPayload([
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
        LevelGeneralInfo::factory()->create(['level_id' => $level->id]);

        $this->mock(LevelGeneralInfoRepository::class, function ($mock) {
            $mock->shouldReceive('update')
                ->once()
                ->andThrow(new \RuntimeException('update failed'));
        });

        $this->putJson($this->generalInfoPath($level), $this->validPayload())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::UPDATE_ERROR_MESSAGE);
    }

    public function test_update_requires_same_validation_as_store(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGeneralInfo::factory()->create(['level_id' => $level->id]);

        $this->putJson($this->generalInfoPath($level), [
            'designer' => '',
            'score' => -1,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['designer', 'description', 'score']);
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

        LevelGeneralInfo::factory()
            ->withPngFile($url)
            ->create(['level_id' => $level->id]);

        $this->deleteJson($this->generalInfoFilesPath($level), [
            'field' => 'png_file',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_FILE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.field', 'png_file')
            ->assertJsonPath('data.png_file', null)
            ->assertJsonPath('data.general_info.png_file', null);

        Storage::disk('public')->assertMissing('levels/delete-me.png');
        $this->assertDatabaseHas('level_general_infos', [
            'level_id' => $level->id,
            'png_file' => null,
        ]);
    }

    public function test_destroy_gif_file_clears_field(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        LevelGeneralInfo::factory()
            ->withGifFile(url('uploads/levels/anim.gif'))
            ->create(['level_id' => $level->id]);

        $this->deleteJson($this->generalInfoFilesPath($level), [
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

        LevelGeneralInfo::factory()
            ->withFbxFiles([
                'fbx' => $fbxUrl,
                'glb' => 'https://cdn.example.com/keep.glb',
            ])
            ->create(['level_id' => $level->id]);

        $response = $this->deleteJson($this->generalInfoFilesPath($level), [
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

        LevelGeneralInfo::factory()
            ->withFbxFiles([
                'fbx' => 'https://cdn.example.com/only.fbx',
            ])
            ->create(['level_id' => $level->id]);

        $this->deleteJson($this->generalInfoFilesPath($level), [
            'field' => 'fbx_file',
            'file_key' => 'fbx',
        ])
            ->assertOk()
            ->assertJsonPath('data.fbx_file', null)
            ->assertJsonPath('data.general_info.fbx_file', null);
    }

    public function test_destroy_file_requires_file_key_for_fbx_field(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGeneralInfo::factory()
            ->withFbxFiles(['fbx' => 'https://cdn.example.com/a.fbx'])
            ->create(['level_id' => $level->id]);

        $this->deleteJson($this->generalInfoFilesPath($level), [
            'field' => 'fbx_file',
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::DESTROY_FILE_KEY_REQUIRED_MESSAGE);

        $this->deleteJson($this->generalInfoFilesPath($level), [
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
        LevelGeneralInfo::factory()
            ->withFbxFiles(['fbx' => 'https://cdn.example.com/a.fbx'])
            ->create(['level_id' => $level->id]);

        $this->deleteJson($this->generalInfoFilesPath($level), [
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
        LevelGeneralInfo::factory()->create([
            'level_id' => $level->id,
            'png_file' => null,
        ]);

        $this->deleteJson($this->generalInfoFilesPath($level), [
            'field' => 'png_file',
        ])
            ->assertNotFound()
            ->assertJsonPath('message', self::DESTROY_FILE_NOT_FOUND_MESSAGE);
    }

    public function test_destroy_file_returns_not_found_when_general_info_missing(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->deleteJson($this->generalInfoFilesPath($level), [
            'field' => 'png_file',
        ])
            ->assertNotFound()
            ->assertJsonPath('message', self::DESTROY_FILE_MISSING_ENTITY_MESSAGE);
    }

    public function test_destroy_file_validates_field_and_file_key(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();
        LevelGeneralInfo::factory()->create(['level_id' => $level->id]);

        $this->deleteJson($this->generalInfoFilesPath($level), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['field']);

        $this->deleteJson($this->generalInfoFilesPath($level), [
            'field' => 'unknown_field',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['field']);

        $this->deleteJson($this->generalInfoFilesPath($level), [
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
        LevelGeneralInfo::factory()
            ->withPngFile(url('uploads/levels/x.png'))
            ->create(['level_id' => $level->id]);

        $this->mock(LevelGeneralInfoRepository::class, function ($mock) {
            $mock->shouldReceive('clearFileField')
                ->once()
                ->andThrow(new \RuntimeException('delete failed'));
        });

        $this->deleteJson($this->generalInfoFilesPath($level), [
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

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
            'score' => 0,
            'rank' => 0,
            'subcategories' => 0,
            'file_volume' => 0,
            'points' => 0,
            'lines' => 0,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.general_info.score', 0)
            ->assertJsonPath('data.general_info.file_volume', 0)
            ->assertJsonPath('data.general_info.points', 0)
            ->assertJsonPath('data.general_info.lines', 0);
    }

    public function test_store_accepts_unicode_text_fields(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->postJson($this->generalInfoPath($level), $this->validPayload([
            'description' => 'توضیحات فارسی با ایموجی ✨',
            'persian_font' => 'ایران‌سنس',
            'used_colors' => 'بنفش و فیروزه‌ای',
            'designer' => 'طراح متاورس',
            'model_designer' => 'مدل‌ساز سه‌بعدی',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.general_info.designer', 'طراح متاورس')
            ->assertJsonPath('data.general_info.model_designer', 'مدل‌ساز سه‌بعدی');
    }

    public function test_store_does_not_leak_exception_details_on_server_error(): void
    {
        $this->actingAsSuperAdmin();
        $level = Level::factory()->create();

        $this->mock(LevelGeneralInfoRepository::class, function ($mock) {
            $mock->shouldReceive('createForLevel')
                ->once()
                ->andThrow(new \RuntimeException('secret-db-password-leaked'));
        });

        $response = $this->postJson($this->generalInfoPath($level), $this->validPayload())
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

        LevelGeneralInfo::factory()
            ->withFbxFiles(['fbx' => $url])
            ->create(['level_id' => $level->id]);

        $response = $this->putJson($this->generalInfoPath($level), $this->validPayload([
            'fbx_file' => [
                'fbx' => $url,
            ],
        ]))->assertOk();

        $this->assertCount(1, $response->json('data.general_info.fbx_file'));
        $this->assertSame($url, $response->json('data.general_info.fbx_file.fbx'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function generalInfoPath(Level $level): string
    {
        return '/api/levels/'.$level->id.'/general-info';
    }

    private function generalInfoFilesPath(Level $level): string
    {
        return '/api/levels/'.$level->id.'/general-info/files';
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'score' => 100,
            'description' => 'Valid general info description',
            'rank' => 1,
            'subcategories' => 2,
            'persian_font' => 'IranSans',
            'english_font' => 'Inter',
            'file_volume' => 1.5,
            'used_colors' => '#7C3AED,#06B6D4',
            'points' => 100,
            'designer' => 'Valid Designer',
            'model_designer' => 'Valid Model Designer',
            'creation_date' => '2024-01-01',
            'has_animation' => false,
            'lines' => 50,
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

        foreach (['has_animation'] as $booleanField) {
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

<?php

namespace Tests\Feature\FileUpload;

use App\Http\Controllers\FileUploadController;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class FileUploadApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesAuthApiSchema;

    private const CHUNK_PATH = '/api/upload/chunk';

    private const FINISHED_MESSAGE = 'بارگذاری فایل با موفقیت انجام شد.';

    private const PARTIAL_MESSAGE = 'بخشی از فایل بارگذاری شد.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthApiSchema();
        Storage::fake('public');
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_chunk_upload_returns_unauthorized(): void
    {
        $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('asset.png', 100, 'image/png'),
        ], ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // Happy path — finished upload
    // -------------------------------------------------------------------------

    public function test_finished_upload_stores_to_levels_on_public_disk_and_returns_created(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('hero.png', 200, 'image/png'),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', self::FINISHED_MESSAGE)
            ->assertJsonPath('file_type', 'png')
            ->assertJsonStructure([
                'success',
                'status',
                'file_name',
                'file_path',
                'file_url',
                'file_type',
                'data' => ['file_name', 'file_path', 'file_url', 'file_type'],
                'message',
            ]);

        $fileName = $response->json('file_name');
        $filePath = $response->json('file_path');
        $fileUrl = $response->json('file_url');

        $this->assertIsString($fileName);
        $this->assertStringEndsWith('.png', $fileName);
        $this->assertSame('levels/'.$fileName, $filePath);
        $this->assertSame($filePath, $response->json('data.file_path'));
        $this->assertSame($fileUrl, $response->json('data.file_url'));
        $this->assertStringContainsString('uploads/'.$filePath, $fileUrl);

        Storage::disk('public')->assertExists($filePath);
    }

    public function test_finished_upload_accepts_fbx_extension(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('model.fbx', 150, 'application/octet-stream'),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('file_type', 'fbx');

        Storage::disk('public')->assertExists($response->json('file_path'));
    }

    // -------------------------------------------------------------------------
    // Chunked / partial upload
    // -------------------------------------------------------------------------

    public function test_partial_chunk_upload_returns_percentage_progress(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('part.png', 100, 'image/png'),
            'resumableChunkNumber' => 1,
            'resumableTotalChunks' => 3,
            'resumableIdentifier' => 'file-upload-test-id',
            'resumableFilename' => 'part.png',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', self::PARTIAL_MESSAGE)
            ->assertJsonStructure([
                'success',
                'status',
                'done',
                'data' => ['percentage'],
                'message',
            ]);

        $percentage = $response->json('data.percentage');
        $this->assertIsNumeric($percentage);
        $this->assertGreaterThanOrEqual(0, $percentage);
        $this->assertLessThan(100, $percentage);
        $this->assertSame($percentage, $response->json('done'));
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_finished_upload_rejects_disallowed_extension(): void
    {
        $this->actingAsSuperAdmin();

        $allowed = implode(', ', FileUploadController::ALLOWED_EXTENSIONS);

        $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', false)
            ->assertJsonPath(
                'message',
                'فرمت فایل مجاز نیست. فرمت‌های مجاز: '.$allowed
            );
    }

    public function test_upload_requires_file(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::CHUNK_PATH, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_rejects_non_file_value(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::CHUNK_PATH, [
            'file' => 'not-a-file',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }
}

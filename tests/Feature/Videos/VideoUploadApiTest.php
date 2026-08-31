<?php

namespace Tests\Feature\Videos;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesVideosApiSchema;
use Tests\TestCase;

class VideoUploadApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesVideosApiSchema;

    private const CHUNK_PATH = '/api/videos/chunk';

    private const FINISHED_MESSAGE = 'بارگذاری ویدیو با موفقیت انجام شد.';

    private const PARTIAL_MESSAGE = 'بخشی از ویدیو بارگذاری شد.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpVideosApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_chunk_upload_returns_unauthorized(): void
    {
        $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
        ], ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_upload(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::FINISHED_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_upload(): void
    {
        $this->actingAsRegularAdmin();

        $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true);
    }

    // -------------------------------------------------------------------------
    // Happy path — single finished upload
    // -------------------------------------------------------------------------

    public function test_single_upload_stores_file_in_resumable_tmp_and_returns_created_payload(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('lesson.mp4', 250, 'video/mp4'),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::FINISHED_MESSAGE)
            ->assertJsonStructure([
                'success',
                'file_name',
                'file_path',
                'data' => ['file_name', 'file_path'],
                'message',
            ]);

        $fileName = $response->json('file_name');
        $filePath = $response->json('file_path');

        $this->assertIsString($fileName);
        $this->assertStringEndsWith('.mp4', $fileName);
        $this->assertSame($fileName, $response->json('data.file_name'));
        $this->assertSame('resumable-tmp/'.$fileName, $filePath);
        $this->assertSame($filePath, $response->json('data.file_path'));

        Storage::disk('local')->assertExists($filePath);
    }

    public function test_single_upload_preserves_original_extension(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('archive.webm', 50, 'video/webm'),
        ], ['Accept' => 'application/json'])
            ->assertCreated();

        $this->assertStringEndsWith('.webm', $response->json('file_name'));
    }

    // -------------------------------------------------------------------------
    // Chunked / partial upload
    // -------------------------------------------------------------------------

    public function test_partial_chunk_upload_returns_percentage_progress(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('part.mp4', 100, 'video/mp4'),
            'resumableChunkNumber' => 1,
            'resumableTotalChunks' => 3,
            'resumableIdentifier' => 'video-upload-test-id',
            'resumableFilename' => 'part.mp4',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::PARTIAL_MESSAGE)
            ->assertJsonStructure([
                'success',
                'data' => ['percentage'],
                'message',
            ]);

        $percentage = $response->json('data.percentage');
        $this->assertIsNumeric($percentage);
        $this->assertGreaterThanOrEqual(0, $percentage);
        $this->assertLessThan(100, $percentage);
    }

    public function test_final_chunk_completes_upload(): void
    {
        $this->actingAsSuperAdmin();

        $identifier = 'final-chunk-'.uniqid();

        $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('chunk.mp4', 50, 'video/mp4'),
            'resumableChunkNumber' => 1,
            'resumableTotalChunks' => 2,
            'resumableIdentifier' => $identifier,
            'resumableFilename' => 'chunk.mp4',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $response = $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('chunk.mp4', 50, 'video/mp4'),
            'resumableChunkNumber' => 2,
            'resumableTotalChunks' => 2,
            'resumableIdentifier' => $identifier,
            'resumableFilename' => 'chunk.mp4',
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::FINISHED_MESSAGE);

        Storage::disk('local')->assertExists($response->json('file_path'));
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

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

    public function test_upload_rejects_disallowed_extension(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_upload_rejects_file_larger_than_max(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('huge.mp4', 5121, 'video/mp4'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_accepts_file_at_max_size_boundary(): void
    {
        $this->actingAsSuperAdmin();

        $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('edge.mp4', 5120, 'video/mp4'),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('success', true);
    }

    // -------------------------------------------------------------------------
    // Integration with video store
    // -------------------------------------------------------------------------

    public function test_uploaded_chunk_file_can_be_used_to_store_video(): void
    {
        $this->actingAsSuperAdmin();

        $upload = $this->post(self::CHUNK_PATH, [
            'file' => UploadedFile::fake()->create('ready.mp4', 80, 'video/mp4'),
        ], ['Accept' => 'application/json'])
            ->assertCreated();

        $fileName = $upload->json('file_name');
        $category = $this->createVideoCategory(['slug' => 'upload-cat']);
        $sub = $this->createVideoSubCategory($category, ['slug' => 'upload-sub']);
        $creator = $this->createCreatorUser();

        $this->post('/api/videos', $this->validVideoStorePayload($category, $sub, $creator, [
            'title' => 'From Chunk Upload',
            'video_file_name' => $fileName,
        ]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.title', 'From Chunk Upload')
            ->assertJsonPath('data.file_name', 'tutorials/upload-cat/upload-sub/'.$fileName);

        Storage::disk('local')->assertMissing('resumable-tmp/'.$fileName);
        Storage::disk('public')->assertExists('tutorials/upload-cat/upload-sub/'.$fileName);
    }
}

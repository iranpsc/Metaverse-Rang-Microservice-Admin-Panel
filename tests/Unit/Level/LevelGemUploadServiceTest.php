<?php

namespace Tests\Unit\Level;

use App\Services\Level\LevelGemUploadService;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class LevelGemUploadServiceTest extends TestCase
{
    private LevelGemUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = new LevelGemUploadService;
    }

    public function test_validate_fbx_file_extensions_rejects_more_than_twenty_files(): void
    {
        $files = [];
        for ($i = 1; $i <= 21; $i++) {
            $files["png_{$i}"] = "https://example.com/file{$i}.png";
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('حداکثر ۲۰ فایل مدل');

        $this->service->validateFbxFileExtensions($files);
    }

    public function test_validate_fbx_file_extensions_rejects_blank_url(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('نامعتبر');

        $this->service->validateFbxFileExtensions(['png' => '   ']);
    }

    public function test_validate_fbx_file_extensions_rejects_disallowed_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('فرمت فایل مدل مجاز نیست');

        $this->service->validateFbxFileExtensions(['exe' => 'https://example.com/a.exe']);
    }

    public function test_validate_fbx_file_extensions_rejects_disallowed_url_extension(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('پسوند لینک فایل مدل مجاز نیست');

        $this->service->validateFbxFileExtensions(['png' => 'https://example.com/a']);
    }

    public function test_validate_fbx_file_extensions_rejects_type_extension_mismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('هم‌خوانی ندارد');

        $this->service->validateFbxFileExtensions(['png' => 'https://example.com/a.fbx']);
    }

    public function test_validate_fbx_file_extensions_allows_jpeg_jpg_family(): void
    {
        $normalized = $this->service->validateFbxFileExtensions([
            'jpeg' => 'https://example.com/photo.jpg',
            'jpg_2' => 'https://example.com/photo2.jpeg',
        ]);

        $this->assertSame('https://example.com/photo.jpg', $normalized['jpeg']);
        $this->assertSame('https://example.com/photo2.jpeg', $normalized['jpg_2']);
    }

    public function test_merge_fbx_file_links_skips_blank_and_duplicate_urls_and_suffixes_keys(): void
    {
        $merged = $this->service->mergeFbxFileLinks(
            ['png' => 'https://cdn.example/a.png', 'empty' => ''],
            [
                'png' => 'https://cdn.example/b.png',
                'fbx' => 'https://cdn.example/a.png',
                'bad' => '  ',
            ]
        );

        $this->assertSame([
            'png' => 'https://cdn.example/a.png',
            'png_2' => 'https://cdn.example/b.png',
        ], $merged);
    }

    public function test_merge_fbx_file_links_rejects_more_than_twenty_total(): void
    {
        $existing = [];
        for ($i = 1; $i <= 20; $i++) {
            $existing["png_{$i}"] = "https://cdn.example/old{$i}.png";
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ابتدا برخی فایل‌های قبلی را حذف کنید');

        $this->service->mergeFbxFileLinks($existing, [
            'fbx' => 'https://cdn.example/new.fbx',
        ]);
    }

    public function test_extract_storage_path_handles_null_uploads_url_and_relative_path(): void
    {
        $this->assertNull($this->service->extractStoragePath(null));
        $this->assertNull($this->service->extractStoragePath(''));

        $uploadsPath = 'gems/model.fbx';
        $this->assertSame(
            $uploadsPath,
            $this->service->extractStoragePath(url('uploads/'.$uploadsPath))
        );
        $this->assertSame(
            'relative/path.fbx',
            $this->service->extractStoragePath('/relative/path.fbx')
        );
    }

    public function test_cleanup_files_deletes_non_null_paths(): void
    {
        Storage::disk('public')->put('gems/a.fbx', 'a');
        Storage::disk('public')->put('gems/b.fbx', 'b');

        $this->service->cleanupFiles(['gems/a.fbx', null, 'gems/b.fbx']);

        Storage::disk('public')->assertMissing('gems/a.fbx');
        Storage::disk('public')->assertMissing('gems/b.fbx');
    }

    public function test_unique_fbx_file_key_increments_when_base_suffix_exists(): void
    {
        $merged = $this->service->mergeFbxFileLinks(
            [
                'png' => 'https://cdn.example/1.png',
                'png_2' => 'https://cdn.example/2.png',
            ],
            ['png' => 'https://cdn.example/3.png']
        );

        $this->assertArrayHasKey('png_3', $merged);
        $this->assertSame('https://cdn.example/3.png', $merged['png_3']);
    }
}

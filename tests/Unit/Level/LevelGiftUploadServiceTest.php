<?php

namespace Tests\Unit\Level;

use App\Services\Level\LevelGiftUploadService;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class LevelGiftUploadServiceTest extends TestCase
{
    private LevelGiftUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = new LevelGiftUploadService;
    }

    public function test_validate_fbx_file_extensions_rejects_more_than_twenty_files(): void
    {
        $files = [];
        for ($i = 1; $i <= 21; $i++) {
            $files["png_{$i}"] = "https://example.com/file{$i}.png";
        }

        $this->expectException(InvalidArgumentException::class);
        $this->service->validateFbxFileExtensions($files);
    }

    public function test_validate_fbx_file_extensions_rejects_blank_disallowed_and_mismatched(): void
    {
        try {
            $this->service->validateFbxFileExtensions(['png' => '']);
            $this->fail('Expected blank url exception');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('نامعتبر', $e->getMessage());
        }

        try {
            $this->service->validateFbxFileExtensions(['exe' => 'https://example.com/a.exe']);
            $this->fail('Expected disallowed type exception');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('فرمت فایل مدل مجاز نیست', $e->getMessage());
        }

        try {
            $this->service->validateFbxFileExtensions(['png' => 'https://example.com/a']);
            $this->fail('Expected missing extension exception');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('پسوند لینک فایل مدل مجاز نیست', $e->getMessage());
        }

        try {
            $this->service->validateFbxFileExtensions(['png' => 'https://example.com/a.fbx']);
            $this->fail('Expected mismatch exception');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('هم‌خوانی ندارد', $e->getMessage());
        }
    }

    public function test_validate_fbx_file_extensions_allows_jpeg_jpg_family(): void
    {
        $normalized = $this->service->validateFbxFileExtensions([
            'jpeg' => 'https://example.com/photo.jpg',
        ]);

        $this->assertSame('https://example.com/photo.jpg', $normalized['jpeg']);
    }

    public function test_merge_fbx_file_links_and_extract_storage_path_edges(): void
    {
        $merged = $this->service->mergeFbxFileLinks(
            ['png' => 'https://cdn.example/a.png', 'png_2' => 'https://cdn.example/b.png'],
            ['png' => 'https://cdn.example/c.png']
        );

        $this->assertSame('https://cdn.example/c.png', $merged['png_3']);
        $this->assertNull($this->service->extractStoragePath(null));
        $this->assertSame('relative/x.fbx', $this->service->extractStoragePath('/relative/x.fbx'));
        $this->assertSame(
            'gifts/x.fbx',
            $this->service->extractStoragePath(url('uploads/gifts/x.fbx'))
        );
    }

    public function test_merge_fbx_file_links_rejects_overflow_and_cleanup_deletes_files(): void
    {
        $existing = [];
        for ($i = 1; $i <= 20; $i++) {
            $existing["png_{$i}"] = "https://cdn.example/old{$i}.png";
        }

        try {
            $this->service->mergeFbxFileLinks($existing, ['fbx' => 'https://cdn.example/new.fbx']);
            $this->fail('Expected overflow exception');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('حداکثر ۲۰ فایل مدل', $e->getMessage());
        }

        Storage::disk('public')->put('gifts/a.fbx', 'a');
        $this->service->cleanupFiles(['gifts/a.fbx', null]);
        Storage::disk('public')->assertMissing('gifts/a.fbx');
    }
}

<?php

namespace Tests\Unit\Level;

use App\Services\Level\LevelGeneralInfoUploadService;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class LevelGeneralInfoUploadServiceTest extends TestCase
{
    private LevelGeneralInfoUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = new LevelGeneralInfoUploadService;
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

    public function test_merge_fbx_file_links_skips_blank_incoming_urls(): void
    {
        $merged = $this->service->mergeFbxFileLinks(
            ['png' => 'https://cdn.example/a.png'],
            [
                'fbx' => '  ',
                'glb' => 'https://cdn.example/b.glb',
            ]
        );

        $this->assertSame([
            'png' => 'https://cdn.example/a.png',
            'glb' => 'https://cdn.example/b.glb',
        ], $merged);
    }

    public function test_extract_storage_path_returns_null_for_empty_url(): void
    {
        $this->assertNull($this->service->extractStoragePath(null));
        $this->assertNull($this->service->extractStoragePath(''));
    }

    public function test_unique_fbx_file_key_uses_file_base_when_desired_key_empty(): void
    {
        $merged = $this->service->mergeFbxFileLinks(
            ['' => 'https://cdn.example/1.bin'],
            ['' => 'https://cdn.example/2.bin']
        );

        $this->assertArrayHasKey('file_2', $merged);
        $this->assertSame('https://cdn.example/2.bin', $merged['file_2']);
    }

    public function test_unique_fbx_file_key_increments_when_suffix_exists(): void
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

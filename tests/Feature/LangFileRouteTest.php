<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class LangFileRouteTest extends TestCase
{
    private string $filePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filePath = storage_path('app/lang/zz-route-check.json');
        File::ensureDirectoryExists(dirname($this->filePath));
        File::put($this->filePath, '{"1":"سلام"}');
    }

    protected function tearDown(): void
    {
        if (is_file($this->filePath)) {
            @unlink($this->filePath);
        }

        parent::tearDown();
    }

    public function test_lang_file_is_served_with_security_headers(): void
    {
        $response = $this->get('/lang/zz-route-check.json');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'same-origin');
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        $fileResponse = $response->baseResponse;
        $this->assertInstanceOf(BinaryFileResponse::class, $fileResponse);
        $this->assertSame($this->filePath, $fileResponse->getFile()->getPathname());
    }

    public function test_missing_lang_file_returns_not_found(): void
    {
        $this->get('/lang/missing-locale.json')->assertNotFound();
    }
}

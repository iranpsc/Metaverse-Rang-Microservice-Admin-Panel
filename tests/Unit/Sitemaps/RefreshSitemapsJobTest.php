<?php

namespace Tests\Unit\Sitemaps;

use App\Jobs\RefreshSitemapsForTranslationStatus;
use App\Services\Sitemaps\SitemapGeneratorClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RefreshSitemapsJobTest extends TestCase
{
    public function test_it_requests_a_full_generation_from_the_generator(): void
    {
        config([
            'sitemap.url' => 'http://sitemap.test',
            'sitemap.token' => 'test-token',
        ]);
        Http::fake([
            'http://sitemap.test/management/generate' => Http::response(['data' => ['status' => 'succeeded']]),
        ]);

        (new RefreshSitemapsForTranslationStatus)->handle(app(SitemapGeneratorClient::class));

        Http::assertSent(function ($request) {
            return $request->url() === 'http://sitemap.test/management/generate'
                && $request->hasHeader('X-Service-Token', 'test-token')
                && $request->method() === 'POST';
        });
    }
}

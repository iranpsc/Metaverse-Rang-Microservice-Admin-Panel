<?php

namespace Tests\Unit\Sitemaps;

use App\Services\Sitemaps\SitemapGeneratorClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SitemapGeneratorClientTest extends TestCase
{
    public function test_it_fetches_files_with_filters(): void
    {
        $this->configureClient();
        Http::fake(['http://sitemap.test/management/sitemaps*' => Http::response(['data' => ['items' => 1]])]);

        $result = app(SitemapGeneratorClient::class)->files(['language' => 'fa']);

        $this->assertSame(['items' => 1], $result);
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request->url() === 'http://sitemap.test/management/sitemaps?language=fa'
            && $request->hasHeader('X-Service-Token', 'test-token'));
    }

    public function test_it_fetches_paginated_logs(): void
    {
        $this->configureClient();
        Http::fake(['http://sitemap.test/management/logs*' => Http::response(['data' => ['logs' => []]])]);

        $result = app(SitemapGeneratorClient::class)->logs(2, 50);

        $this->assertSame(['logs' => []], $result);
        Http::assertSent(fn ($request) => $request->url() === 'http://sitemap.test/management/logs?page=2&per_page=50');
    }

    public function test_it_generates_with_non_empty_options_only(): void
    {
        $this->configureClient();
        Http::fake(['http://sitemap.test/management/generate' => Http::response(['data' => ['status' => 'queued']])]);

        $result = app(SitemapGeneratorClient::class)->generate('full', 'fa');

        $this->assertSame(['status' => 'queued'], $result);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->data() === ['target' => 'full', 'language' => 'fa']);
    }

    public function test_it_uploads_a_sitemap_file(): void
    {
        $this->configureClient();
        Http::fake(['http://sitemap.test/management/upload' => Http::response(['data' => ['filename' => 'sitemap.xml']])]);

        $result = app(SitemapGeneratorClient::class)->upload(
            UploadedFile::fake()->createWithContent('sitemap.xml', '<urlset/>'),
            'fa'
        );

        $this->assertSame(['filename' => 'sitemap.xml'], $result);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'http://sitemap.test/management/upload'
            && $request->hasFile('file'));
    }

    public function test_it_deletes_a_sitemap_with_encoded_segments(): void
    {
        $this->configureClient();
        Http::fake(['http://sitemap.test/management/delete/*' => Http::response([])]);

        app(SitemapGeneratorClient::class)->delete('fa-IR', 'site map.xml');

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && $request->url() === 'http://sitemap.test/management/delete/fa-IR/site%20map.xml');
    }

    private function configureClient(): void
    {
        config([
            'sitemap.url' => 'http://sitemap.test',
            'sitemap.token' => 'test-token',
        ]);
    }
}

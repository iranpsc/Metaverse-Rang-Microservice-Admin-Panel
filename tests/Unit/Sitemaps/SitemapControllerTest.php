<?php

namespace Tests\Unit\Sitemaps;

use App\Http\Controllers\Api\SitemapController;
use App\Http\Requests\Sitemaps\GenerateSitemapRequest;
use App\Http\Requests\Sitemaps\UploadSitemapRequest;
use App\Services\Sitemaps\SitemapGeneratorClient;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class SitemapControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_filtered_sitemaps(): void
    {
        $client = Mockery::mock(SitemapGeneratorClient::class);
        $client->expects('files')->with(['language' => 'fa', 'type' => 'full', 'status' => 'ready'])->andReturn(['items' => 1]);

        $response = (new SitemapController($client))->index(Request::create('/', 'GET', [
            'language' => 'fa', 'type' => 'full', 'status' => 'ready',
        ]));

        $this->assertSame(['items' => 1], $response->getData(true)['data']['sitemaps']);
    }

    public function test_logs_normalizes_pagination(): void
    {
        $client = Mockery::mock(SitemapGeneratorClient::class);
        $client->expects('logs')->with(1, 100)->andReturn(['logs' => []]);

        $response = (new SitemapController($client))->logs(Request::create('/', 'GET', [
            'page' => 0, 'per_page' => 200,
        ]));

        $this->assertSame(['logs' => []], $response->getData(true)['data']);
    }

    public function test_generate_upload_and_destroy_delegate_to_client(): void
    {
        $client = Mockery::mock(SitemapGeneratorClient::class);
        $client->expects('generate')->with('full', 'fa')->andReturn(['status' => 'queued']);
        $file = UploadedFile::fake()->createWithContent('sitemap.xml', '<urlset/>');
        $client->expects('upload')->with($file, 'fa')->andReturn(['filename' => 'sitemap.xml']);
        $client->expects('delete')->with('fa-IR', 'site.xml');
        $controller = new SitemapController($client);

        $generate = Mockery::mock(GenerateSitemapRequest::class)->makePartial();
        $generate->expects('validated')->andReturn(['target' => 'full', 'language' => 'fa']);
        $upload = Mockery::mock(UploadSitemapRequest::class)->makePartial();
        $upload->expects('validated')->andReturn(['file' => $file, 'language' => 'fa']);

        $generateResponse = $controller->generate($generate);
        $uploadResponse = $controller->upload($upload);
        $deleteResponse = $controller->destroy(Request::create('/'), 'fa-IR', 'site.xml');

        $this->assertSame('queued', $generateResponse->getData(true)['data']['run']['status']);
        $this->assertSame('sitemap.xml', $uploadResponse->getData(true)['data']['sitemap']['filename']);
        $this->assertTrue($deleteResponse->getData(true)['success']);
    }

    public function test_sitemap_requests_are_authorized_and_define_validation_rules(): void
    {
        $generate = new GenerateSitemapRequest;
        $upload = new UploadSitemapRequest;

        $this->assertTrue($generate->authorize());
        $this->assertTrue($upload->authorize());
        $this->assertArrayHasKey('target', $generate->rules());
        $this->assertArrayHasKey('language', $upload->rules());
        $this->assertArrayHasKey('file', $upload->rules());
    }
}

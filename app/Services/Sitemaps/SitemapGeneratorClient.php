<?php

namespace App\Services\Sitemaps;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class SitemapGeneratorClient
{
    private function request()
    {
        return Http::baseUrl(rtrim(config('sitemap.url'), '/'))
            ->withHeaders(['X-Service-Token' => config('sitemap.token')])
            ->acceptJson()
            ->timeout(120);
    }

    public function files(array $filters = []): array
    {
        $response = $this->request()->get('/management/sitemaps', $filters)->throw();

        return $response->json('data', []);
    }

    public function logs(int $page = 1, int $perPage = 20): array
    {
        return $this->request()->get('/management/logs', ['page' => $page, 'per_page' => $perPage])->throw()->json('data', []);
    }

    public function generate(?string $target = null, ?string $language = null): array
    {
        $response = $this->request()->post('/management/generate', array_filter([
            'target' => $target,
            'language' => $language,
        ], static fn ($value) => $value !== null && $value !== ''))->throw();

        return $response->json('data', []);
    }

    public function upload(UploadedFile $file, string $language): array
    {
        return $this->request()->attach('file', $file->getContent(), $file->getClientOriginalName())
            ->post('/management/upload', ['language' => $language])->throw()->json('data', []);
    }

    public function delete(string $language, string $filename): void
    {
        $this->request()->delete('/management/delete/'.rawurlencode($language).'/'.rawurlencode($filename))->throw();
    }
}

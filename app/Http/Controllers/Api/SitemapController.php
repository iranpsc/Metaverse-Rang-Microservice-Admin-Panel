<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sitemaps\GenerateSitemapRequest;
use App\Http\Requests\Sitemaps\UploadSitemapRequest;
use App\Services\Sitemaps\SitemapGeneratorClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function __construct(private readonly SitemapGeneratorClient $client) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => ['sitemaps' => $this->client->files($request->only(['language', 'type', 'status']))]]);
    }

    public function logs(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = min(100, max(1, (int) $request->integer('per_page', 20)));

        return response()->json(['success' => true, 'data' => $this->client->logs($page, $perPage)]);
    }

    public function generate(GenerateSitemapRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json(['success' => true, 'data' => ['run' => $this->client->generate($data['target'] ?? null, $data['language'] ?? null)], 'message' => 'بازسازی سایت مپ آغاز شد.']);
    }

    public function upload(UploadSitemapRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json(['success' => true, 'data' => ['sitemap' => $this->client->upload($data['file'], $data['language'])], 'message' => 'سایت مپ با موفقیت بارگذاری شد.']);
    }

    public function destroy(Request $request, string $language, string $filename): JsonResponse
    {
        $this->client->delete($language, $filename);

        return response()->json(['success' => true, 'data' => null, 'message' => 'سایت مپ حذف شد.']);
    }
}

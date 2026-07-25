<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Videos\StoreVideoRequest;
use App\Http\Requests\Videos\UpdateVideoRequest;
use App\Http\Resources\Videos\VideoResource;
use App\Models\Video;
use App\Models\VideoCategory;
use App\Services\Videos\VideoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class VideosController extends Controller
{
    public function __construct(private readonly VideoService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? $perPage : 10;

        $videos = $this->service->getVideos(
            $perPage,
            $request->get('search'),
            $request->integer('video_category_id') ?: null,
            $request->integer('video_sub_category_id') ?: null
        );

        return response()->json([
            'success' => true,
            'data' => [
                'videos' => VideoResource::collection(collect($videos->items()))->resolve($request),
                'pagination' => [
                    'current_page' => $videos->currentPage(),
                    'last_page' => $videos->lastPage(),
                    'per_page' => $videos->perPage(),
                    'total' => $videos->total(),
                    'from' => $videos->firstItem(),
                    'to' => $videos->lastItem(),
                ],
            ],
            'message' => 'فهرست ویدیوها با موفقیت دریافت شد.',
        ]);
    }

    public function store(StoreVideoRequest $request): JsonResponse
    {
        try {
            $video = $this->service->create($request->validated());
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'success' => true,
            'data' => (new VideoResource($video))->toArray($request),
            'message' => 'ویدیو با موفقیت ایجاد شد.',
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateVideoRequest $request, Video $video): JsonResponse
    {
        try {
            $video = $this->service->update($video, $request->validated());
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'success' => true,
            'data' => (new VideoResource($video))->toArray($request),
            'message' => 'ویدیو با موفقیت به روزرسانی شد.',
        ]);
    }

    public function destroy(Video $video): JsonResponse
    {
        $this->service->delete($video);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'ویدیو با موفقیت حذف شد.',
        ]);
    }

    public function meta(): JsonResponse
    {
        $categories = VideoCategory::query()
            ->with(['subCategories:id,video_category_id,name,slug'])
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'sub_categories' => $category->subCategories->map(fn ($subCategory) => [
                        'id' => $subCategory->id,
                        'name' => $subCategory->name,
                        'slug' => $subCategory->slug,
                    ])->values(),
                ])->values(),
            ],
            'message' => 'اطلاعات کمکی ویدیوها با موفقیت دریافت شد.',
        ]);
    }
}

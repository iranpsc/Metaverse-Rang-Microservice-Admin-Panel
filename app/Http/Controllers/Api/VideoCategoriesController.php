<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Videos\StoreVideoCategoryRequest;
use App\Http\Requests\Videos\UpdateVideoCategoryRequest;
use App\Http\Resources\Videos\VideoCategoryResource;
use App\Models\VideoCategory;
use App\Services\Videos\VideoCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoCategoriesController extends Controller
{
    public function __construct(private readonly VideoCategoryService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search');

        $categories = $this->service->getCategories($perPage, $search);

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => VideoCategoryResource::collection(collect($categories->items()))->resolve($request),
                'pagination' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'from' => $categories->firstItem(),
                    'to' => $categories->lastItem(),
                ],
            ],
            'message' => 'دسته بندی ها با موفقیت دریافت شد.',
        ]);
    }

    public function store(StoreVideoCategoryRequest $request): JsonResponse
    {
        $category = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => (new VideoCategoryResource($category))->toArray($request),
            'message' => 'دسته بندی با موفقیت ایجاد شد.',
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateVideoCategoryRequest $request, VideoCategory $videoCategory): JsonResponse
    {
        $category = $this->service->update($videoCategory, $request->validated());

        return response()->json([
            'success' => true,
            'data' => (new VideoCategoryResource($category))->toArray($request),
            'message' => 'دسته بندی با موفقیت به روزرسانی شد.',
        ]);
    }

    public function destroy(VideoCategory $videoCategory): JsonResponse
    {
        $this->service->delete($videoCategory);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'دسته بندی با موفقیت حذف شد.',
        ]);
    }
}



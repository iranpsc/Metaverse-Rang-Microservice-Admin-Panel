<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Videos\StoreVideoSubCategoryRequest;
use App\Http\Requests\Videos\UpdateVideoSubCategoryRequest;
use App\Http\Resources\Videos\VideoSubCategoryResource;
use App\Models\VideoSubCategory;
use App\Services\Videos\VideoSubCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoSubCategoriesController extends Controller
{
    public function __construct(private readonly VideoSubCategoryService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $categoryId = $request->get('video_category_id');
        $search = $request->get('search');

        $subCategories = $this->service->getSubCategories($perPage, $categoryId, $search);

        return response()->json([
            'success' => true,
            'data' => [
                'sub_categories' => VideoSubCategoryResource::collection(collect($subCategories->items()))->resolve($request),
                'pagination' => [
                    'current_page' => $subCategories->currentPage(),
                    'last_page' => $subCategories->lastPage(),
                    'per_page' => $subCategories->perPage(),
                    'total' => $subCategories->total(),
                    'from' => $subCategories->firstItem(),
                    'to' => $subCategories->lastItem(),
                ],
            ],
            'message' => 'زیر دسته ها با موفقیت دریافت شد.',
        ]);
    }

    public function store(StoreVideoSubCategoryRequest $request): JsonResponse
    {
        $subCategory = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => (new VideoSubCategoryResource($subCategory))->toArray($request),
            'message' => 'زیر دسته با موفقیت ایجاد شد.',
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateVideoSubCategoryRequest $request, VideoSubCategory $videoSubCategory): JsonResponse
    {
        $subCategory = $this->service->update($videoSubCategory, $request->validated());

        return response()->json([
            'success' => true,
            'data' => (new VideoSubCategoryResource($subCategory))->toArray($request),
            'message' => 'زیر دسته با موفقیت به روزرسانی شد.',
        ]);
    }

    public function destroy(VideoSubCategory $videoSubCategory): JsonResponse
    {
        $this->service->delete($videoSubCategory);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'زیر دسته با موفقیت حذف شد.',
        ]);
    }
}



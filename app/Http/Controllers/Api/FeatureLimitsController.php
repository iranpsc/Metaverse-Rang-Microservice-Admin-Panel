<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeatureLimitsRequest;
use App\Services\Lands\FeatureLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureLimitsController extends Controller
{
    public function __construct(
        private readonly FeatureLimitService $featureLimitService
    ) {}

    /**
     * Get paginated feature limits
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->featureLimitService->getPaginated(
            (int) $request->input('per_page', 10),
            (int) $request->input('page', 1)
        );

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Feature limits retrieved successfully.',
        ]);
    }

    /**
     * Store a new feature limit
     */
    public function store(StoreFeatureLimitsRequest $request): JsonResponse
    {
        try {
            $this->featureLimitService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'محدودیت املاک با موفقیت ایجاد شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد محدودیت: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a feature limit
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->featureLimitService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'محدودیت املاک با موفقیت حذف شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف محدودیت',
            ], 500);
        }
    }
}

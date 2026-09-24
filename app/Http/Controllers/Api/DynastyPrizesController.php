<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dynasty\StoreDynastyPrizeRequest;
use App\Http\Requests\Dynasty\UpdateDynastyPrizeRequest;
use App\Http\Resources\Dynasty\DynastyPrizeResource;
use App\Services\Dynasty\DynastyPrizeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DynastyPrizesController extends Controller
{
    public function __construct(private readonly DynastyPrizeService $service) {}

    /**
     * Get paginated dynasty prizes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $result = $this->service->list((int) $request->input('per_page', 10));
            $prizes = $result['prizes'];

            return response()->json([
                'success' => true,
                'data' => [
                    'prizes' => DynastyPrizeResource::collection(collect($prizes->items()))->resolve($request),
                    'total_paid_amount' => $result['total_paid_amount'],
                    'pagination' => [
                        'current_page' => $prizes->currentPage(),
                        'last_page' => $prizes->lastPage(),
                        'per_page' => $prizes->perPage(),
                        'total' => $prizes->total(),
                        'from' => $prizes->firstItem(),
                        'to' => $prizes->lastItem(),
                    ],
                ],
                'message' => 'جوایز سلسله با موفقیت بارگذاری شدند.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری جوایز سلسله',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new dynasty prize
     */
    public function store(StoreDynastyPrizeRequest $request): JsonResponse
    {
        try {
            $prize = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت ثبت شد',
                'data' => (new DynastyPrizeResource($prize))->toArray($request),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت اطلاعات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing dynasty prize
     */
    public function update(UpdateDynastyPrizeRequest $request, int $id): JsonResponse
    {
        try {
            $prize = $this->service->findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'پاداش یافت نشد',
            ], 404);
        }

        try {
            $prize = $this->service->update($prize, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت ثبت شد',
                'data' => (new DynastyPrizeResource($prize))->toArray($request),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی اطلاعات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a dynasty prize
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $prize = $this->service->findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'پاداش یافت نشد',
            ], 404);
        }

        try {
            $this->service->delete($prize);

            return response()->json([
                'success' => true,
                'message' => 'پاداش با موفقیت حذف شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف پاداش',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

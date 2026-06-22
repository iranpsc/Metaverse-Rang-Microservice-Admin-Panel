<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\FeatureProperties;
use App\Models\User;
use App\Services\Lands\LandOwnerTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LandsController extends Controller
{
    public function __construct(
        private readonly LandOwnerTransferService $landOwnerTransferService
    ) {
    }
    /**
     * Get paginated lands/properties with search
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = trim($request->get('search', ''));
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $query = FeatureProperties::with(['feature', 'feature.map', 'feature.owner:id,name,code', 'feature.geometry.coordinates']);

        if ($searchTerm !== '') {
            $owner = User::query()
                ->where('code', $searchTerm)
                ->first(['id']);

            if ($owner) {
                $query->whereHas('feature', function ($featureQuery) use ($owner) {
                    $featureQuery->where('owner_id', $owner->id);
                });
            } else {
                $query->where('id', 'like', '%' . $searchTerm . '%');
            }
        }

        $properties = $query->orderBy('id', 'asc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'properties' => $properties->items(),
                'pagination' => [
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage(),
                    'total' => $properties->total(),
                    'from' => $properties->firstItem(),
                    'to' => $properties->lastItem(),
                ],
            ],
            'message' => 'Properties retrieved successfully.',
        ]);
    }

    /**
     * Update feature properties
     *
     * @param Request $request
     * @param int $id Feature ID
     * @return JsonResponse
     */
    public function updateProperties(Request $request, int $id): JsonResponse
    {
        $feature = Feature::with('properties')->findOrFail($id);

        if (!$feature->properties) {
            return response()->json([
                'success' => false,
                'message' => 'Feature properties not found',
            ], 404);
        }

        $validated = $request->validate([
            'area' => 'required|numeric',
            'density' => 'required|numeric',
            'karbari' => 'required|string',
            'address' => 'required|string',
            'rgb' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $feature->properties->update([
                'area' => $validated['area'],
                'density' => $validated['density'],
                'karbari' => $validated['karbari'],
                'address' => $validated['address'],
                'rgb' => $validated['rgb'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت ثبت شد',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت اطلاعات',
            ], 500);
        }
    }

    /**
     * Update feature coordinates
     *
     * @param Request $request
     * @param int $id Feature ID
     * @return JsonResponse
     */
    public function updateCoordinates(Request $request, int $id): JsonResponse
    {
        $feature = Feature::with('geometry.coordinates')->findOrFail($id);

        if (!$feature->geometry || !$feature->geometry->coordinates) {
            return response()->json([
                'success' => false,
                'message' => 'Feature coordinates not found',
            ], 404);
        }

        $validated = $request->validate([
            'coordinates' => 'required|array',
            'coordinates.*.x' => 'required|numeric',
            'coordinates.*.y' => 'required|numeric',
        ]);

        // Validate coordinates count matches
        if (count($validated['coordinates']) !== $feature->geometry->coordinates->count()) {
            return response()->json([
                'success' => false,
                'message' => 'تعداد مختصات با تعداد موجود همخوانی ندارد',
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($feature->geometry->coordinates as $index => $coordinate) {
                $coordinate->update([
                    'x' => $validated['coordinates'][$index]['x'],
                    'y' => $validated['coordinates'][$index]['y'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت ثبت شد',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت اطلاعات',
            ], 500);
        }
    }

    /**
     * Get paginated lands or users for the owner transfer modal
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function ownerTransferOptions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:lands,users',
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $result = $this->landOwnerTransferService->getOptions(
            $validated['type'],
            trim($validated['search'] ?? ''),
            $validated['page'] ?? 1,
            $validated['per_page'] ?? 20
        );

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => $validated['type'] === 'lands'
                ? 'Land options retrieved successfully.'
                : 'User options retrieved successfully.',
        ]);
    }

    /**
     * Transfer land ownership from system (owner_id = 1) to a user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function transferOwner(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'feature_id' => 'required|integer|exists:features,id',
            'new_owner_id' => 'required|integer|exists:users,id|not_in:1',
        ]);

        try {
            $this->landOwnerTransferService->transferOwner(
                $validated['feature_id'],
                $validated['new_owner_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'مالکیت زمین با موفقیت منتقل شد',
            ]);
        } catch (HttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در انتقال مالکیت',
            ], 500);
        }
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\FeatureProperties;
use App\Models\Coordinate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LandsController extends Controller
{
    /**
     * Get paginated lands/properties with search
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->get('search', '');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $query = FeatureProperties::with(['feature', 'feature.map', 'feature.geometry.coordinates']);

        if ($searchTerm) {
            $query->where('id', 'like', '%' . trim($searchTerm) . '%');
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
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'integer',
                'digits:6',
                'is_valid_verify_code',
            ],
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
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'integer',
                'digits:6',
                'is_valid_verify_code',
            ],
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
}


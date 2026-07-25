<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricesController extends Controller
{
    /**
     * Get paginated land prices
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->get('search', '');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $query = Feature::with('properties');

        if ($searchTerm) {
            $query->whereHas('properties', function ($q) use ($searchTerm) {
                $q->where('id', 'like', '%' . trim($searchTerm) . '%');
            });
        }

        $features = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'features' => $features->items(),
                'pagination' => [
                    'current_page' => $features->currentPage(),
                    'last_page' => $features->lastPage(),
                    'per_page' => $features->perPage(),
                    'total' => $features->total(),
                    'from' => $features->firstItem(),
                    'to' => $features->lastItem(),
                ],
            ],
            'message' => 'Land prices retrieved successfully.',
        ]);
    }
}


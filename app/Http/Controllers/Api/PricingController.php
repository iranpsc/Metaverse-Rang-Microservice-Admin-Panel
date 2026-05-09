<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellFeatureRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Get paginated pricing requests
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->get('search', '');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $query = SellFeatureRequest::with('feature.properties')
            ->where('status', 0);

        if ($searchTerm) {
            $query->whereHas('feature.properties', function ($q) use ($searchTerm) {
                $q->where('id', 'like', '%' . trim($searchTerm) . '%');
            });
        }

        $pricings = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'pricings' => $pricings->items(),
                'pagination' => [
                    'current_page' => $pricings->currentPage(),
                    'last_page' => $pricings->lastPage(),
                    'per_page' => $pricings->perPage(),
                    'total' => $pricings->total(),
                    'from' => $pricings->firstItem(),
                    'to' => $pricings->lastItem(),
                ],
            ],
            'message' => 'Pricing requests retrieved successfully.',
        ]);
    }
}


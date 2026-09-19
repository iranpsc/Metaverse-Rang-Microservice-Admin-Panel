<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellFeatureRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    private const SORTABLE_COLUMNS = [
        'price_irr',
        'price_psc',
    ];

    /**
     * Get paginated pricing requests
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search', '');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = strtolower((string) $request->input('sort', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = SellFeatureRequest::with('feature.properties')
            ->where('status', 0);

        if ($searchTerm) {
            $query->whereHas('feature.properties', function ($q) use ($searchTerm) {
                $q->where('id', 'like', '%'.trim($searchTerm).'%');
            });
        }

        if (in_array($sortBy, self::SORTABLE_COLUMNS, true)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $pricings = $query->paginate($perPage, ['*'], 'page', $page);

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

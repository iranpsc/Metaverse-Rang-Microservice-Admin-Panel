<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoldController extends Controller
{
    /**
     * Get paginated sold lands
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->get('search', '');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $query = Trade::with(['feature.properties', 'buyer'])
            ->where('seller_id', 1);

        if ($searchTerm) {
            $query->whereHas('feature.properties', function ($q) use ($searchTerm) {
                $q->where('id', 'like', '%' . trim($searchTerm) . '%');
            });
        }

        $trades = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'trades' => $trades->items(),
                'pagination' => [
                    'current_page' => $trades->currentPage(),
                    'last_page' => $trades->lastPage(),
                    'per_page' => $trades->perPage(),
                    'total' => $trades->total(),
                    'from' => $trades->firstItem(),
                    'to' => $trades->lastItem(),
                ],
            ],
            'message' => 'Sold lands retrieved successfully.',
        ]);
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WithdrawController extends Controller
{
    /**
     * Get withdraw information
     * Currently returns empty data as per Livewire component
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'withdraws' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                    'from' => null,
                    'to' => null,
                ],
            ],
            'message' => 'Withdraws retrieved successfully.',
        ]);
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature\FeaturePricingLimit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeaturePricingLimitsController extends Controller
{
    /**
     * Get pricing limits
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $priceLimits = FeaturePricingLimit::first();

        $data = null;
        if ($priceLimits) {
            $data = [
                'id' => $priceLimits->id,
                'public_price_limit' => $priceLimits->public_price_limit ?? 0,
                'under_eighteen_price_limit' => $priceLimits->under_eighteen_price_limit ?? 0,
                'updated_at' => $priceLimits->updated_at,
                'changer_name' => Auth::guard('admin')->user()->name ?? '-',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'price_limits' => $data,
            ],
            'message' => 'Pricing limits retrieved successfully.',
        ]);
    }

    /**
     * Update pricing limits
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'public_price_limit' => 'required|integer',
            'under_eighteen_price_limit' => 'required|integer',
        ]);

        $priceLimits = FeaturePricingLimit::first();

        if (!$priceLimits) {
            $priceLimits = FeaturePricingLimit::create([
                'public_price_limit' => $validated['public_price_limit'],
                'under_eighteen_price_limit' => $validated['under_eighteen_price_limit'],
            ]);
        } else {
            $priceLimits->update([
                'public_price_limit' => $validated['public_price_limit'],
                'under_eighteen_price_limit' => $validated['under_eighteen_price_limit'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'محدودیت‌های قیمت با موفقیت به‌روزرسانی شدند',
        ]);
    }
}


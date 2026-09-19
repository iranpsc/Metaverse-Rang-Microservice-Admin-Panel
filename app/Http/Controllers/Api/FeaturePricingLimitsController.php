<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature\FeaturePricingLimit;
use App\Services\ActivityLogCategoryResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\Jalalian;
use Spatie\Activitylog\Models\Activity;

class FeaturePricingLimitsController extends Controller
{
    /**
     * Get pricing limits and related activity logs
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
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'price_limits' => $data,
                'activity_logs' => $this->getActivityLogs(),
            ],
            'message' => 'Pricing limits retrieved successfully.',
        ]);
    }

    /**
     * Update pricing limits
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'public_price_limit' => 'required|integer',
            'under_eighteen_price_limit' => 'required|integer',
        ]);

        $priceLimits = FeaturePricingLimit::first();

        if (! $priceLimits) {
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
            'data' => [
                'price_limits' => [
                    'id' => $priceLimits->id,
                    'public_price_limit' => $priceLimits->public_price_limit ?? 0,
                    'under_eighteen_price_limit' => $priceLimits->under_eighteen_price_limit ?? 0,
                    'updated_at' => $priceLimits->updated_at,
                ],
                'activity_logs' => $this->getActivityLogs(),
            ],
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getActivityLogs(): array
    {
        if (! Schema::hasTable('activity_log')) {
            return [];
        }

        return Activity::query()
            ->with('causer')
            ->where('subject_type', (new FeaturePricingLimit)->getMorphClass())
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn (Activity $activity) => $this->formatActivity($activity))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatActivity(Activity $activity): array
    {
        $properties = $activity->properties?->toArray() ?? [];
        $category = $properties['category'] ?? $activity->log_name;
        $jalali = $activity->created_at
            ? Jalalian::fromCarbon($activity->created_at)
            : null;

        return [
            'id' => $activity->id,
            'description' => $activity->description,
            'event' => $activity->event,
            'category' => $category,
            'category_label' => ActivityLogCategoryResolver::label((string) $category),
            'causer_name' => $activity->causer?->name ?? 'سیستم',
            'properties' => $properties,
            'created_at' => $activity->created_at,
            'created_at_jalali' => $jalali?->format('Y/m/d'),
            'created_at_time' => $jalali?->format('H:i:s'),
        ];
    }
}

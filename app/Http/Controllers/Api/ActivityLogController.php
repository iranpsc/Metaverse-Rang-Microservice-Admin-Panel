<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogCategoryResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || (! $user->can('view-activity-logs') && ! $user->hasRole('super-admin'))) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی مشاهده گزارش فعالیت‌ها را ندارید.',
            ], 403);
        }

        $categories = collect(ActivityLogCategoryResolver::categories())
            ->map(fn ($label, $id) => ['id' => $id, 'label' => $label])
            ->values();

        return response()->json([
            'success' => true,
            'data' => ['categories' => $categories],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || (! $user->can('view-activity-logs') && ! $user->hasRole('super-admin'))) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی مشاهده گزارش فعالیت‌ها را ندارید.',
            ], 403);
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $page = (int) $request->get('page', 1);
        $search = trim((string) $request->get('search', ''));
        $category = $request->get('category');
        $event = $request->get('event');

        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest('id');

        if ($category && $category !== 'all') {
            $query->where('log_name', $category);
        }

        if ($event && $event !== 'all') {
            $query->where('event', $event);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('description', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%")
                    ->orWhere('properties', 'like', "%{$search}%")
                    ->orWhereHas('causer', function ($causerQuery) use ($search) {
                        $causerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $activities = $query->paginate($perPage, ['*'], 'page', $page);

        $activities->getCollection()->transform(
            fn (Activity $activity) => $this->formatActivity($activity)
        );

        return response()->json([
            'success' => true,
            'data' => [
                'activities' => $activities->items(),
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                    'from' => $activities->firstItem(),
                    'to' => $activities->lastItem(),
                ],
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user || (! $user->can('view-activity-logs') && ! $user->hasRole('super-admin'))) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی مشاهده گزارش فعالیت‌ها را ندارید.',
            ], 403);
        }

        $activity = Activity::with(['causer', 'subject'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'activity' => $this->formatActivity($activity, includeSubject: true),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatActivity(Activity $activity, bool $includeSubject = false): array
    {
        $properties = $activity->properties?->toArray() ?? [];
        $category = $properties['category'] ?? $activity->log_name;
        $jalali = $activity->created_at
            ? Jalalian::fromCarbon($activity->created_at)
            : null;

        $formatted = [
            'id' => $activity->id,
            'description' => $activity->description,
            'event' => $activity->event,
            'log_name' => $activity->log_name,
            'category' => $category,
            'category_label' => ActivityLogCategoryResolver::label((string) $category),
            'subject_type' => $activity->subject_type ? class_basename($activity->subject_type) : null,
            'subject_id' => $activity->subject_id,
            'causer' => $activity->causer ? [
                'id' => $activity->causer->id,
                'name' => $activity->causer->name,
                'email' => $activity->causer->email,
            ] : null,
            'properties' => $properties,
            'created_at' => $activity->created_at,
            'created_at_jalali' => $jalali?->format('Y/m/d'),
            'created_at_time' => $jalali?->format('H:i:s'),
        ];

        if ($includeSubject) {
            $formatted['subject'] = $activity->subject;
        }

        return $formatted;
    }
}

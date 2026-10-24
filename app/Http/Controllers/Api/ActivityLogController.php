<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogCategoryResolver;
use App\Services\ContentWebhooks\ChangeRecorder;
use App\Services\ContentWebhooks\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\Jalalian;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function webhookSummary(Request $request): JsonResponse
    {
        abort_unless($this->canViewActivityLogs($request->user()), 403);
        $ready = Schema::hasTable('content_delivery_state');
        $state = $ready ? DB::table('content_delivery_state')->where('id', 1)->first() : null;
        $failed = $ready ? DB::table('content_webhook_batches')->where('status', 'failed')->orderBy('created_at')->first(['id', 'version', 'error', 'http_status']) : null;
        $pending = $ready ? DB::table('content_changes')->leftJoin('content_webhook_batches as b', 'b.id', '=', 'content_changes.batch_id')
            ->where(fn ($q) => $q->whereNull('batch_id')->orWhere('b.status', 'pending')) : null;

        return response()->json(['success' => true, 'data' => [
            'enabled' => $ready && (bool) config('content_webhooks.enabled'),
            'can_send' => $this->canSend($request->user()),
            'pending_count' => $pending ? (clone $pending)->count() : 0,
            'oldest_pending_at' => $pending ? (clone $pending)->min('content_changes.created_at') : null,
            'blocked_batch' => $failed, 'active_cycle' => $state?->active_cycle,
            'next_version' => 'V1.1.'.($state?->next_patch ?? 1),
            'last_delivered_at' => $ready ? DB::table('content_webhook_batches')->max('delivered_at') : null,
        ]]);
    }

    public function webhookDispatch(Request $request): JsonResponse
    {
        abort_unless($this->canSend($request->user()), 403);
        $cycle = app(DeliveryService::class)->trigger('manual', ChangeRecorder::actor());

        return response()->json(['success' => true, 'data' => ['cycle_id' => $cycle]], 202);
    }

    public function webhookRetry(Request $request, string $batch): JsonResponse
    {
        abort_unless($this->canSend($request->user()), 403);
        $cycle = app(DeliveryService::class)->trigger('manual', ChangeRecorder::actor(), $batch);

        return response()->json(['success' => true, 'data' => ['cycle_id' => $cycle]], 202);
    }

    private function canSend(mixed $user): bool
    {
        return $user && method_exists($user, 'getAllPermissions') &&
            ($user->hasRole('super-admin') || $user->getAllPermissions()->contains('name', 'send-content-webhooks'));
    }

    private function deliveryDetails(int $activityId): ?array
    {
        if (! Schema::hasTable('content_changes')) {
            return null;
        }
        $change = DB::table('content_changes')->where('activity_id', $activityId)->first();
        if (! $change) {
            return null;
        }
        $batch = $change->batch_id ? DB::table('content_webhook_batches')->where('id', $change->batch_id)->first() : null;

        return ['status' => $batch?->status ?? 'pending', 'batch_id' => $batch?->id,
            'version' => $batch?->version, 'attempts' => $batch?->attempts ?? 0,
            'http_status' => $batch?->http_status, 'error' => $batch?->error,
            'delivered_at' => $batch?->delivered_at,
            'affected_routes' => json_decode($change->data, true)['affected_routes']];
    }

    public function categories(Request $request): JsonResponse
    {
        if (! $this->canViewActivityLogs($request->user())) {
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
        if (! $this->canViewActivityLogs($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی مشاهده گزارش فعالیت‌ها را ندارید.',
            ], 403);
        }

        $perPage = min((int) $request->input('per_page', 15), 50);
        $page = (int) $request->input('page', 1);
        $search = trim((string) $request->input('search', ''));
        $category = $request->input('category');
        $event = $request->input('event');

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
        if (! $this->canViewActivityLogs($request->user())) {
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

    private function canViewActivityLogs(mixed $user): bool
    {
        if (! $user || ! method_exists($user, 'hasRole') || ! method_exists($user, 'getAllPermissions')) {
            return false;
        }

        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Permission names are stored on web, admin, or sanctum. The request
        // default guard is admin, so a name lookup via can() misses the others.
        return $user->getAllPermissions()->contains('name', 'view-activity-logs');
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
            'delivery' => $this->deliveryDetails($activity->id),
            'created_at' => $activity->created_at,
            'created_at_jalali' => $jalali?->format('Y/m/d'),
            'created_at_time' => $jalali?->format('H:i:s'),
        ];

        if (array_key_exists('modifier', $properties)) {
            $formatted['causer'] = $properties['modifier'];
        }

        if ($includeSubject) {
            $formatted['subject'] = $activity->subject;
        }

        return $formatted;
    }
}

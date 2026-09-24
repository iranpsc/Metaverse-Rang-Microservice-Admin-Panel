<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KycResource;
use App\Models\Kyc;
use App\Notifications\KycDeniedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

class KycController extends Controller
{
    /**
     * Get paginated KYC records with search
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search', '');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $query = Kyc::query()->with('user:id,name,code')->latest();

        if ($searchTerm) {
            $normalized = preg_replace('/\s+/u', ' ', trim($searchTerm));
            $term = '%'.$normalized.'%';
            $fullNameExpression = $this->fullNameSqlExpression();

            $query->where(function ($q) use ($term, $fullNameExpression) {
                $q->where('melli_code', 'like', $term)
                    ->orWhere('fname', 'like', $term)
                    ->orWhere('lname', 'like', $term)
                    ->orWhereRaw("{$fullNameExpression} LIKE ?", [$term])
                    ->orWhereHas('user', function ($userQuery) use ($term) {
                        $userQuery->where('name', 'like', $term)
                            ->orWhere('code', 'like', $term);
                    });
            });
        }

        $kycs = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'kycs' => KycResource::collection($kycs->items()),
                'pagination' => [
                    'current_page' => $kycs->currentPage(),
                    'last_page' => $kycs->lastPage(),
                    'per_page' => $kycs->perPage(),
                    'total' => $kycs->total(),
                    'from' => $kycs->firstItem(),
                    'to' => $kycs->lastItem(),
                ],
            ],
            'message' => 'KYC records retrieved successfully.',
        ]);
    }

    /**
     * Get single KYC record details
     */
    public function show(int $id): JsonResponse
    {
        $kyc = Kyc::with(['verifyText', 'user'])->findOrFail($id);

        $payload = (new KycResource($kyc))->resolve();
        $payload['rejected_by'] = $this->resolveRejectedBy($kyc);

        return response()->json([
            'success' => true,
            'data' => $payload,
            'message' => 'KYC record retrieved successfully.',
        ]);
    }

    /**
     * Update KYC status and errors
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'kyc_errors' => 'nullable|array',
        ]);

        $kyc = Kyc::findOrFail($id);

        // Get kyc_errors from request (default to empty array if not provided)
        $kycErrors = $validated['kyc_errors'] ?? [];

        if (! empty($kycErrors)) {
            // Has errors - reject KYC
            $kyc->update([
                'status' => -1,
                'errors' => $kycErrors,
            ]);

            $user = $kyc->user;
            $message = 'احراز هویت شما تایید نشد';
            $user->notify(new KycDeniedNotification($message));
        } else {
            // No errors - verify KYC
            $kyc->update([
                'status' => 1,
                'errors' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => new KycResource($kyc->fresh()),
            'message' => 'اطلاعات با موفقیت ثبت شد',
        ]);
    }

    /**
     * @return array{id: int|string, name: string}|null
     */
    private function resolveRejectedBy(Kyc $kyc): ?array
    {
        if ((int) $kyc->status !== -1 || ! Schema::hasTable('activity_log')) {
            return null;
        }

        $activity = Activity::query()
            ->where('subject_type', $kyc->getMorphClass())
            ->where('subject_id', $kyc->getKey())
            ->where('event', 'updated')
            ->with('causer')
            ->latest('id')
            ->get()
            ->first(function (Activity $activity) {
                return (int) data_get($activity->properties, 'attributes.status') === -1;
            });

        if (! $activity?->causer) {
            return null;
        }

        return [
            'id' => $activity->causer->id,
            'name' => $activity->causer->name,
        ];
    }

    private function fullNameSqlExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "TRIM(COALESCE(fname, '') || ' ' || COALESCE(lname, ''))"
            : "TRIM(CONCAT(COALESCE(fname, ''), ' ', COALESCE(lname, '')))";
    }
}

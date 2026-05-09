<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KycResource;
use App\Models\Kyc;
use App\Notifications\KycDeniedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KycController extends Controller
{
    /**
     * Get paginated KYC records with search
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->get('search', '');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $query = Kyc::query()->latest();

        if ($searchTerm) {
            $query->where('melli_code', 'like', '%' . trim($searchTerm) . '%');
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
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $kyc = Kyc::with(['verifyText', 'user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new KycResource($kyc),
            'message' => 'KYC record retrieved successfully.',
        ]);
    }

    /**
     * Update KYC status and errors
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'kyc_errors' => 'nullable|array',
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_verify_code'
            ],
        ]);

        $kyc = Kyc::findOrFail($id);

        // Get kyc_errors from request (default to empty array if not provided)
        $kycErrors = $validated['kyc_errors'] ?? [];

        if (!empty($kycErrors)) {
            // Has errors - reject KYC
            $kyc->update([
                'status' => -1,
                'errors' => $kycErrors
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
}


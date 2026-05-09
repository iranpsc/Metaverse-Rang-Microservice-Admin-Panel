<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;
use App\Notifications\KycDeniedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{
    /**
     * Get paginated Bank Account records with search
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->get('search', '');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $query = BankAccount::query()->with('bankable.kyc')->latest();

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('card_num', 'like', '%' . trim($searchTerm) . '%')
                    ->orWhere('shaba_num', 'like', '%' . trim($searchTerm) . '%');
            });
        }

        $bankAccounts = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'bankAccounts' => BankAccountResource::collection($bankAccounts->items()),
                'pagination' => [
                    'current_page' => $bankAccounts->currentPage(),
                    'last_page' => $bankAccounts->lastPage(),
                    'per_page' => $bankAccounts->perPage(),
                    'total' => $bankAccounts->total(),
                    'from' => $bankAccounts->firstItem(),
                    'to' => $bankAccounts->lastItem(),
                ],
            ],
            'message' => 'Bank account records retrieved successfully.',
        ]);
    }

    /**
     * Get single Bank Account record details
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $bankAccount = BankAccount::with(['bankable.kyc'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new BankAccountResource($bankAccount),
            'message' => 'Bank account record retrieved successfully.',
        ]);
    }

    /**
     * Update Bank Account status and errors
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'bank_account_errors' => 'nullable|array',
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(app()->environment('production')),
                'is_valid_verify_code'
            ],
        ]);

        $bankAccount = BankAccount::findOrFail($id);

        // Get bank_account_errors from request (default to empty array if not provided)
        $bankAccountErrors = $validated['bank_account_errors'] ?? [];

        if (!empty($bankAccountErrors)) {
            // Has errors - reject Bank Account
            $bankAccount->update([
                'status' => -1,
                'errors' => $bankAccountErrors
            ]);

            $user = $bankAccount->bankable;
            $message = 'حساب بانکی تایید نشد.';
            $user->notify(new KycDeniedNotification($message));
        } else {
            // No errors - verify Bank Account
            $bankAccount->update([
                'status' => 1,
                'errors' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => new BankAccountResource($bankAccount->fresh()->load('bankable.kyc')),
            'message' => 'اطلاعات با موفقیت ثبت شد',
        ]);
    }
}


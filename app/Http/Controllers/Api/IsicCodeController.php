<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IsicCode\ImportIsicCodeRequest;
use App\Http\Requests\IsicCode\StoreIsicCodeRequest;
use App\Http\Resources\IsicCodeResource;
use App\Imports\IsicCodesImport;
use App\Models\IsicCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class IsicCodeController extends Controller
{
    /**
     * Display a paginated listing of ISIC codes.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 50) : 10;
        $search = trim((string) $request->get('search', ''));

        $query = IsicCode::query()
            ->orderBy('verified')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(static function (Builder $builder) use ($search) {
                $builder->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $isicCodes = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'success' => true,
            'data' => [
                'isic_codes' => IsicCodeResource::collection($isicCodes->items())->resolve($request),
                'pagination' => [
                    'current_page' => $isicCodes->currentPage(),
                    'last_page' => $isicCodes->lastPage(),
                    'per_page' => $isicCodes->perPage(),
                    'total' => $isicCodes->total(),
                    'from' => $isicCodes->firstItem(),
                    'to' => $isicCodes->lastItem(),
                ],
            ],
            'message' => 'لیست کدهای ISIC با موفقیت دریافت شد.',
        ]);
    }

    /**
     * Store a newly created ISIC code in storage.
     */
    public function store(StoreIsicCodeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $isicCode = IsicCode::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'verified' => $data['verified'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'isic_code' => (new IsicCodeResource($isicCode))->resolve($request),
            ],
            'message' => 'کد ISIC جدید با موفقیت ایجاد شد.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Import ISIC codes from the uploaded file.
     */
    public function import(ImportIsicCodeRequest $request): JsonResponse
    {
        try {
            $path = $request->file('file')->store('isic_codes/imports', 'public');

            Excel::queueImport(new IsicCodesImport(), $path, 'public');

            return response()->json([
                'success' => true,
                'message' => 'درون‌ریزی کدهای ISIC در صف پردازش قرار گرفت.',
            ], Response::HTTP_ACCEPTED);
        } catch (Throwable $exception) {
            Log::error('ISIC codes import failed', [
                'exception' => $exception,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'بروز خطا در پردازش فایل درون‌ریزی.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Approve the specified ISIC code.
     */
    public function approve(Request $request, IsicCode $isicCode): JsonResponse
    {
        $isicCode->update([
            'code' => random_int(1_000_000, 9_999_999),
            'verified' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'isic_code' => (new IsicCodeResource($isicCode->fresh()))->resolve($request),
            ],
            'message' => 'کد ISIC با موفقیت تایید شد.',
        ]);
    }

    /**
     * Mark the specified ISIC code as not verified.
     */
    public function deny(Request $request, IsicCode $isicCode): JsonResponse
    {
        $isicCode->update(['verified' => false]);

        return response()->json([
            'success' => true,
            'data' => [
                'isic_code' => (new IsicCodeResource($isicCode->fresh()))->resolve($request),
            ],
            'message' => 'کد ISIC در وضعیت انتظار تایید قرار گرفت.',
        ]);
    }

    /**
     * Remove the specified ISIC code from storage.
     */
    public function destroy(IsicCode $isicCode): JsonResponse
    {
        $isicCode->delete();

        return response()->json([
            'success' => true,
            'message' => 'کد ISIC با موفقیت حذف شد.',
        ]);
    }
}



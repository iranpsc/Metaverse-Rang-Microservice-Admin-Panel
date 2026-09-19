<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KycVideoTextResource;
use App\Models\Kyc;
use App\Models\KycVerifyText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class KycVideoTextController extends Controller
{
    /**
     * Get paginated KYC video verification texts
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $texts = KycVerifyText::latest()->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => KycVideoTextResource::collection($texts->items()),
            'pagination' => [
                'current_page' => $texts->currentPage(),
                'last_page' => $texts->lastPage(),
                'per_page' => $texts->perPage(),
                'total' => $texts->total(),
                'from' => $texts->firstItem(),
                'to' => $texts->lastItem(),
                'has_more' => $texts->hasMorePages(),
            ],
            'message' => 'KYC video texts retrieved successfully.',
        ]);
    }

    /**
     * Store a new KYC video verification text
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        $text = KycVerifyText::create([
            'text' => $validated['text'],
        ]);

        return response()->json([
            'success' => true,
            'data' => new KycVideoTextResource($text),
            'message' => 'متن احراز ویدیویی با موفقیت ثبت شد.',
        ]);
    }

    /**
     * Update a KYC video verification text
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        $text = KycVerifyText::findOrFail($id);
        $text->update([
            'text' => $validated['text'],
        ]);

        return response()->json([
            'success' => true,
            'data' => new KycVideoTextResource($text),
            'message' => 'متن احراز ویدیویی با موفقیت به‌روزرسانی شد.',
        ]);
    }

    /**
     * Delete a KYC video verification text
     */
    public function destroy(int $id): JsonResponse
    {
        $text = KycVerifyText::findOrFail($id);

        if ($this->isVerifyTextInUse($text)) {
            return response()->json([
                'success' => false,
                'message' => 'این متن در احراز هویت استفاده شده و قابل حذف نیست.',
            ], 422);
        }

        $text->delete();

        return response()->json([
            'success' => true,
            'message' => 'متن احراز ویدیویی با موفقیت حذف شد.',
        ]);
    }

    private function isVerifyTextInUse(KycVerifyText $text): bool
    {
        if (! Schema::hasTable('kycs')) {
            return false;
        }

        return Kyc::query()->where('verify_text_id', $text->id)->exists();
    }
}

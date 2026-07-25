<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KycVideoTextResource;
use App\Models\KycVerifyText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycVideoTextController extends Controller
{
    /**
     * Get paginated KYC video verification texts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string'
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
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
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
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $text = KycVerifyText::findOrFail($id);
        $text->delete();

        return response()->json([
            'success' => true,
            'message' => 'متن احراز ویدیویی با موفقیت حذف شد.',
        ]);
    }
}


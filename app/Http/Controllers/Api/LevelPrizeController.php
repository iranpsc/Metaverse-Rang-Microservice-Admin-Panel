<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Level\LevelPrizeRequest;
use App\Http\Resources\Level\LevelPrizeResource;
use App\Models\Level\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class LevelPrizeController extends Controller
{
    public function show(Level $level): JsonResponse
    {
        $prize = $level->prize;

        return response()->json([
            'success' => true,
            'data' => [
                'prize' => $prize ? new LevelPrizeResource($prize) : null,
            ],
            'message' => $prize
                ? 'اطلاعات پاداش سطح با موفقیت دریافت شد.'
                : 'برای این سطح تاکنون پاداشی ثبت نشده است.',
        ]);
    }

    public function store(LevelPrizeRequest $request, Level $level): JsonResponse
    {
        if ($level->prize) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح پاداشی ثبت شده است. لطفاً از ویرایش استفاده کنید.',
            ], 422);
        }

        $validated = $request->validated();
        unset($validated['phone_verification']);

        try {
            $prize = DB::transaction(function () use ($level, $validated) {
                return $level->prize()->create($validated);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'prize' => new LevelPrizeResource($prize),
                ],
                'message' => 'پاداش سطح با موفقیت ثبت شد.',
            ], 201);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت پاداش سطح',
            ], 500);
        }
    }

    public function update(LevelPrizeRequest $request, Level $level): JsonResponse
    {
        $prize = $level->prize;

        if (!$prize) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح پاداشی ثبت نشده است.',
            ], 404);
        }

        $validated = $request->validated();
        unset($validated['phone_verification']);

        try {
            DB::transaction(function () use ($prize, $validated) {
                $prize->update($validated);
            });

            $prize->refresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'prize' => new LevelPrizeResource($prize),
                ],
                'message' => 'پاداش سطح با موفقیت بروزرسانی شد.',
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی پاداش سطح',
            ], 500);
        }
    }
}



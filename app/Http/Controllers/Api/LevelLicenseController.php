<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Level\LevelLicenseRequest;
use App\Http\Resources\Level\LevelLicenseResource;
use App\Models\Level\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class LevelLicenseController extends Controller
{
    public function show(Level $level): JsonResponse
    {
        $licenses = $level->licenses;

        return response()->json([
            'success' => true,
            'data' => [
                'licenses' => $licenses ? new LevelLicenseResource($licenses) : null,
            ],
            'message' => $licenses
                ? 'مجوزهای سطح با موفقیت دریافت شد.'
                : 'برای این سطح تاکنون مجوزی ثبت نشده است.',
        ]);
    }

    public function store(LevelLicenseRequest $request, Level $level): JsonResponse
    {
        if ($level->licenses) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح مجوز ثبت شده است. لطفاً از ویرایش استفاده کنید.',
            ], 422);
        }

        $validated = $request->validated();
        unset($validated['phone_verification']);

        try {
            $licenses = DB::transaction(function () use ($level, $validated) {
                return $level->licenses()->create($validated);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'licenses' => new LevelLicenseResource($licenses),
                ],
                'message' => 'مجوزهای سطح با موفقیت ثبت شد.',
            ], 201);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت مجوزهای سطح',
            ], 500);
        }
    }

    public function update(LevelLicenseRequest $request, Level $level): JsonResponse
    {
        $licenses = $level->licenses;

        if (!$licenses) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح مجوزی ثبت نشده است.',
            ], 404);
        }

        $validated = $request->validated();
        unset($validated['phone_verification']);

        try {
            DB::transaction(function () use ($licenses, $validated) {
                $licenses->update($validated);
            });

            $licenses->refresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'licenses' => new LevelLicenseResource($licenses),
                ],
                'message' => 'مجوزهای سطح با موفقیت بروزرسانی شد.',
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی مجوزهای سطح',
            ], 500);
        }
    }
}



<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Level\LevelGiftRequest;
use App\Http\Resources\Level\LevelGiftResource;
use App\Models\Level\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class LevelGiftController extends Controller
{
    public function show(Level $level): JsonResponse
    {
        $gift = $level->gift;

        return response()->json([
            'success' => true,
            'data' => [
                'gift' => $gift ? new LevelGiftResource($gift) : null,
            ],
            'message' => $gift
                ? 'هدیه سطح با موفقیت دریافت شد.'
                : 'برای این سطح تاکنون هدیه‌ای ثبت نشده است.',
        ]);
    }

    public function store(LevelGiftRequest $request, Level $level): JsonResponse
    {
        if ($level->gift) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح هدیه‌ای ثبت شده است. لطفاً از ویرایش استفاده کنید.',
            ], 422);
        }

        $validated = $this->preparePayload($request->validated());
        unset($validated['phone_verification']);

        $storedFiles = [];

        try {
            $fileUploads = $this->handleFileUploads($request);
            $storedFiles = array_column($fileUploads, 'path');

            foreach ($fileUploads as $field => $fileData) {
                $validated[$field] = $fileData['url'];
            }

            $gift = DB::transaction(function () use ($level, $validated) {
                return $level->gift()->create($validated);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'gift' => new LevelGiftResource($gift),
                ],
                'message' => 'هدیه سطح با موفقیت ثبت شد.',
            ], 201);
        } catch (Throwable $throwable) {
            $this->cleanupFiles($storedFiles);
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت هدیه سطح',
            ], 500);
        }
    }

    public function update(LevelGiftRequest $request, Level $level): JsonResponse
    {
        $gift = $level->gift;

        if (!$gift) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح هدیه‌ای ثبت نشده است.',
            ], 404);
        }

        $validated = $this->preparePayload($request->validated());
        unset($validated['phone_verification']);

        $storedFiles = [];
        $replacedFiles = [];

        try {
            $fileUploads = $this->handleFileUploads($request);
            $storedFiles = array_column($fileUploads, 'path');

            foreach ($fileUploads as $field => $fileData) {
                if ($gift->{$field}) {
                    $replacedFiles[] = $this->extractStoragePath($gift->{$field});
                }
                $validated[$field] = $fileData['url'];
            }

            DB::transaction(function () use ($gift, $validated) {
                $gift->update($validated);
            });

            $gift->refresh();

            $this->cleanupFiles($replacedFiles);

            return response()->json([
                'success' => true,
                'data' => [
                    'gift' => new LevelGiftResource($gift),
                ],
                'message' => 'هدیه سطح با موفقیت بروزرسانی شد.',
            ]);
        } catch (Throwable $throwable) {
            $this->cleanupFiles($storedFiles);
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی هدیه سطح',
            ], 500);
        }
    }

    private function preparePayload(array $data): array
    {
        $payload = $data;

        $stringFields = [
            'name',
            'description',
            'features',
            'seller_link',
            'designer',
            'start_vod_id',
            'end_vod_id',
        ];

        foreach ($stringFields as $field) {
            if (array_key_exists($field, $payload) && $payload[$field] !== null) {
                $payload[$field] = trim((string) $payload[$field]);
            }
        }

        $booleanFields = [
            'store_capacity',
            'sell_capacity',
            'sell',
            'vod_document_registration',
            'has_animation',
            'rent',
        ];

        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = filter_var($payload[$field], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }

        $integerFields = [
            'monthly_capacity_count',
            'three_d_model_points',
            'three_d_model_lines',
            'vod_count',
        ];

        foreach ($integerFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = (int) $payload[$field];
            }
        }

        if (array_key_exists('three_d_model_volume', $payload)) {
            $payload['three_d_model_volume'] = (float) $payload['three_d_model_volume'];
        }

        return $payload;
    }

    private function handleFileUploads(LevelGiftRequest $request): array
    {
        $uploads = [];

        $fileFields = [
            'png_file',
            'fbx_file',
            'gif_file',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                if ($field === 'fbx_file') {
                    $filename = $file->getClientOriginalName() ?: (Str::uuid() . '.' . $file->getClientOriginalExtension());
                    $path = $file->storeAs('levels', $filename, 'public');
                } else {
                    $path = $file->store('levels', 'public');
                }
                $uploads[$field] = [
                    'path' => $path,
                    'url' => url('uploads/' . $path),
                ];
            }
        }

        return $uploads;
    }

    private function cleanupFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function extractStoragePath(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $baseUrl = rtrim(url('uploads'), '/');

        if ($baseUrl && str_starts_with($url, $baseUrl)) {
            return ltrim(substr($url, strlen($baseUrl)), '/');
        }

        return ltrim($url, '/');
    }
}



<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Level\LevelGeneralInfoRequest;
use App\Http\Resources\Level\LevelGeneralInfoResource;
use App\Models\Level\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class LevelGeneralInfoController extends Controller
{
    public function show(Level $level): JsonResponse
    {
        $generalInfo = $level->generalInfo;

        return response()->json([
            'success' => true,
            'data' => [
                'general_info' => $generalInfo ? new LevelGeneralInfoResource($generalInfo) : null,
            ],
            'message' => $generalInfo
                ? 'اطلاعات کلی سطح با موفقیت دریافت شد.'
                : 'برای این سطح تاکنون اطلاعات کلی ثبت نشده است.',
        ]);
    }

    public function store(LevelGeneralInfoRequest $request, Level $level): JsonResponse
    {
        if ($level->generalInfo) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح اطلاعات کلی ثبت شده است. لطفاً از ویرایش استفاده کنید.',
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

            $generalInfo = DB::transaction(function () use ($level, $validated) {
                return $level->generalInfo()->create($validated);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'general_info' => new LevelGeneralInfoResource($generalInfo),
                ],
                'message' => 'اطلاعات کلی سطح با موفقیت ثبت شد.',
            ], 201);
        } catch (Throwable $throwable) {
            $this->cleanupFiles($storedFiles);
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت اطلاعات کلی سطح',
            ], 500);
        }
    }

    public function update(LevelGeneralInfoRequest $request, Level $level): JsonResponse
    {
        $generalInfo = $level->generalInfo;

        if (!$generalInfo) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح اطلاعات کلی ثبت نشده است.',
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
                if ($generalInfo->{$field}) {
                    $replacedFiles[] = $this->extractStoragePath($generalInfo->{$field});
                }

                $validated[$field] = $fileData['url'];
            }

            DB::transaction(function () use ($generalInfo, $validated) {
                $generalInfo->update($validated);
            });

            $generalInfo->refresh();

            $this->cleanupFiles($replacedFiles);

            return response()->json([
                'success' => true,
                'data' => [
                    'general_info' => new LevelGeneralInfoResource($generalInfo),
                ],
                'message' => 'اطلاعات کلی سطح با موفقیت بروزرسانی شد.',
            ]);
        } catch (Throwable $throwable) {
            $this->cleanupFiles($storedFiles);
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی اطلاعات کلی سطح',
            ], 500);
        }
    }

    private function preparePayload(array $data): array
    {
        $payload = $data;

        $stringFields = [
            'description',
            'used_colors',
            'persian_font',
            'english_font',
            'designer',
            'model_designer',
            'creation_date',
        ];

        foreach ($stringFields as $field) {
            if (array_key_exists($field, $payload) && $payload[$field] !== null) {
                $payload[$field] = trim((string) $payload[$field]);
            }
        }

        $integerFields = [
            'score',
            'rank',
            'subcategories',
            'points',
            'lines',
        ];

        foreach ($integerFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = (int) $payload[$field];
            }
        }

        $booleanFields = [
            'has_animation',
        ];

        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = filter_var($payload[$field], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }

        if (array_key_exists('file_volume', $payload)) {
            $payload['file_volume'] = (float) $payload['file_volume'];
        }

        return $payload;
    }

    private function handleFileUploads(LevelGeneralInfoRequest $request): array
    {
        $uploads = [];

        $fileFields = ['png_file', 'fbx_file', 'gif_file'];

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



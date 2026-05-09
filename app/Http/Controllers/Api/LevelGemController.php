<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Level\LevelGemRequest;
use App\Http\Resources\Level\LevelGemResource;
use App\Models\Level\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class LevelGemController extends Controller
{
    public function show(Level $level): JsonResponse
    {
        $gem = $level->gem;

        return response()->json([
            'success' => true,
            'data' => [
                'gem' => $gem ? new LevelGemResource($gem) : null,
            ],
            'message' => $gem
                ? 'گوهر سطح با موفقیت دریافت شد.'
                : 'برای این سطح تاکنون گوهری ثبت نشده است.',
        ]);
    }

    public function store(LevelGemRequest $request, Level $level): JsonResponse
    {
        if ($level->gem) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح گوهری ثبت شده است. لطفاً از ویرایش استفاده کنید.',
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

            $gem = DB::transaction(function () use ($level, $validated) {
                return $level->gem()->create($validated);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'gem' => new LevelGemResource($gem),
                ],
                'message' => 'گوهر سطح با موفقیت ثبت شد.',
            ], 201);
        } catch (Throwable $throwable) {
            $this->cleanupFiles($storedFiles);
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت گوهر سطح',
            ], 500);
        }
    }

    public function update(LevelGemRequest $request, Level $level): JsonResponse
    {
        $gem = $level->gem;

        if (!$gem) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح گوهری ثبت نشده است.',
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
                if ($gem->{$field}) {
                    $replacedFiles[] = $this->extractStoragePath($gem->{$field});
                }

                $validated[$field] = $fileData['url'];
            }

            DB::transaction(function () use ($gem, $validated) {
                $gem->update($validated);
            });

            $gem->refresh();

            $this->cleanupFiles($replacedFiles);

            return response()->json([
                'success' => true,
                'data' => [
                    'gem' => new LevelGemResource($gem),
                ],
                'message' => 'گوهر سطح با موفقیت بروزرسانی شد.',
            ]);
        } catch (Throwable $throwable) {
            $this->cleanupFiles($storedFiles);
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی گوهر سطح',
            ], 500);
        }
    }

    private function preparePayload(array $data): array
    {
        $payload = $data;

        $stringFields = [
            'name',
            'description',
            'thread',
            'color',
            'designer',
        ];

        foreach ($stringFields as $field) {
            if (array_key_exists($field, $payload) && $payload[$field] !== null) {
                $payload[$field] = trim((string) $payload[$field]);
            }
        }

        $integerFields = [
            'points',
            'lines',
        ];

        foreach ($integerFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = (int) $payload[$field];
            }
        }

        $booleanFields = [
            'encryption',
            'has_animation',
        ];

        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = filter_var($payload[$field], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }

        if (array_key_exists('volume', $payload)) {
            $payload['volume'] = (float) $payload['volume'];
        }

        return $payload;
    }

    private function handleFileUploads(LevelGemRequest $request): array
    {
        $uploads = [];

        $fileFields = ['png_file', 'fbx_file'];

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



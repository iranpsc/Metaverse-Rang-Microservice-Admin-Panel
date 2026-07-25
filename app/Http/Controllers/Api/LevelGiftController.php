<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Level\LevelGiftRequest;
use App\Http\Resources\Level\LevelGiftResource;
use App\Models\Level\Level;
use App\Repositories\LevelGiftRepository;
use App\Services\Level\LevelGiftUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class LevelGiftController extends Controller
{
    public function __construct(
        private LevelGiftRepository $levelGiftRepository,
        private LevelGiftUploadService $levelGiftUploadService,
    ) {}

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

        $validated = $this->levelGiftUploadService->preparePayload($request->validated());
        $storedFiles = [];

        try {
            if (isset($validated['fbx_file'])) {
                $validated['fbx_file'] = $this->levelGiftUploadService->validateFbxFileExtensions($validated['fbx_file']);
            }

            $fileUploads = $this->levelGiftUploadService->handleFileUploads($request);
            $storedFiles = array_column($fileUploads, 'path');
            $validated = $this->levelGiftUploadService->applyUploadedFiles($fileUploads, $validated);

            if (! array_key_exists('fbx_file', $validated) || ! is_array($validated['fbx_file'])) {
                unset($validated['fbx_file']);
            }

            $gift = $this->levelGiftRepository->createForLevel($level, $validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'gift' => new LevelGiftResource($gift),
                ],
                'message' => 'هدیه سطح با موفقیت ثبت شد.',
            ], 201);
        } catch (InvalidArgumentException $exception) {
            $this->levelGiftUploadService->cleanupFiles($storedFiles);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $throwable) {
            $this->levelGiftUploadService->cleanupFiles($storedFiles);
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

        if (! $gift) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح هدیه‌ای ثبت نشده است.',
            ], 404);
        }

        $validated = $this->levelGiftUploadService->preparePayload($request->validated());
        $storedFiles = [];
        $replacedFiles = [];

        try {
            if (isset($validated['fbx_file'])) {
                $validated['fbx_file'] = $this->levelGiftUploadService->validateFbxFileExtensions($validated['fbx_file']);
            }

            $fileUploads = $this->levelGiftUploadService->handleFileUploads($request);
            $storedFiles = array_column($fileUploads, 'path');
            $previousFbxFiles = is_array($gift->fbx_file) ? $gift->fbx_file : [];
            $replacedFiles = $this->levelGiftUploadService->collectReplacedFilePaths($gift, $fileUploads);
            $validated = $this->levelGiftUploadService->applyUploadedFiles($fileUploads, $validated);

            if (array_key_exists('fbx_file', $validated) && is_array($validated['fbx_file'])) {
                $validated['fbx_file'] = $this->levelGiftUploadService->mergeFbxFileLinks(
                    $previousFbxFiles,
                    $validated['fbx_file']
                );
            } else {
                unset($validated['fbx_file']);
            }

            $gift = $this->levelGiftRepository->update($gift, $validated);

            $this->levelGiftUploadService->cleanupFiles(array_filter($replacedFiles));

            return response()->json([
                'success' => true,
                'data' => [
                    'gift' => new LevelGiftResource($gift),
                ],
                'message' => 'هدیه سطح با موفقیت بروزرسانی شد.',
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->levelGiftUploadService->cleanupFiles($storedFiles);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $throwable) {
            $this->levelGiftUploadService->cleanupFiles($storedFiles);
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی هدیه سطح',
            ], 500);
        }
    }

    public function destroyFile(Request $request, Level $level): JsonResponse
    {
        $gift = $level->gift;

        if (! $gift) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح هدیه‌ای ثبت نشده است.',
            ], 404);
        }

        $validated = $request->validate([
            'field' => ['required', 'string', 'in:png_file,gif_file,fbx_file'],
            'file_key' => ['nullable', 'string', 'max:64'],
        ]);

        $field = $validated['field'];

        try {
            if ($field === 'fbx_file') {
                $fileKey = $validated['file_key'] ?? null;

                if (! is_string($fileKey) || $fileKey === '') {
                    return response()->json([
                        'success' => false,
                        'message' => 'کلید فایل مدل برای حذف الزامی است.',
                    ], 422);
                }

                $result = $this->levelGiftRepository->removeFbxFileEntry($gift, $fileKey);

                if (! $result['found']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'فایل مورد نظر یافت نشد.',
                    ], 404);
                }

                $deletedPath = $this->levelGiftUploadService->extractStoragePath($result['url']);
            } else {
                $result = $this->levelGiftRepository->clearFileField($gift, $field);

                if (! $result['found']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'فایل مورد نظر یافت نشد.',
                    ], 404);
                }

                $deletedPath = $this->levelGiftUploadService->extractStoragePath($result['url']);
            }

            $gift->refresh();

            if ($deletedPath) {
                $this->levelGiftUploadService->cleanupFiles([$deletedPath]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'gift' => new LevelGiftResource($gift),
                    'field' => $field,
                    $field => $gift->{$field},
                ],
                'message' => 'فایل با موفقیت حذف شد.',
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف فایل هدیه سطح',
            ], 500);
        }
    }
}

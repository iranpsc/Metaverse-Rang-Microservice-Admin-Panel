<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Level\LevelGemRequest;
use App\Http\Resources\Level\LevelGemResource;
use App\Models\Level\Level;
use App\Repositories\LevelGemRepository;
use App\Services\Level\LevelGemUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class LevelGemController extends Controller
{
    public function __construct(
        private LevelGemRepository $levelGemRepository,
        private LevelGemUploadService $levelGemUploadService,
    ) {}

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

        $validated = $this->levelGemUploadService->preparePayload($request->validated());
        $storedFiles = [];

        try {
            if (isset($validated['fbx_file'])) {
                $validated['fbx_file'] = $this->levelGemUploadService->validateFbxFileExtensions($validated['fbx_file']);
            }

            $fileUploads = $this->levelGemUploadService->handleFileUploads($request);
            $storedFiles = array_column($fileUploads, 'path');
            $validated = $this->levelGemUploadService->applyUploadedFiles($fileUploads, $validated);

            if (! array_key_exists('fbx_file', $validated) || ! is_array($validated['fbx_file'])) {
                unset($validated['fbx_file']);
            }

            $gem = $this->levelGemRepository->createForLevel($level, $validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'gem' => new LevelGemResource($gem),
                ],
                'message' => 'گوهر سطح با موفقیت ثبت شد.',
            ], 201);
        } catch (InvalidArgumentException $exception) {
            $this->levelGemUploadService->cleanupFiles($storedFiles);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $throwable) {
            $this->levelGemUploadService->cleanupFiles($storedFiles);
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

        if (! $gem) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح گوهری ثبت نشده است.',
            ], 404);
        }

        $validated = $this->levelGemUploadService->preparePayload($request->validated());
        $storedFiles = [];
        $replacedFiles = [];

        try {
            if (isset($validated['fbx_file'])) {
                $validated['fbx_file'] = $this->levelGemUploadService->validateFbxFileExtensions($validated['fbx_file']);
            }

            $fileUploads = $this->levelGemUploadService->handleFileUploads($request);
            $storedFiles = array_column($fileUploads, 'path');
            $previousFbxFiles = is_array($gem->fbx_file) ? $gem->fbx_file : [];
            $replacedFiles = $this->levelGemUploadService->collectReplacedFilePaths($gem, $fileUploads);
            $validated = $this->levelGemUploadService->applyUploadedFiles($fileUploads, $validated);

            if (array_key_exists('fbx_file', $validated) && is_array($validated['fbx_file'])) {
                $validated['fbx_file'] = $this->levelGemUploadService->mergeFbxFileLinks(
                    $previousFbxFiles,
                    $validated['fbx_file']
                );
            } else {
                unset($validated['fbx_file']);
            }

            $gem = $this->levelGemRepository->update($gem, $validated);

            $this->levelGemUploadService->cleanupFiles(array_filter($replacedFiles));

            return response()->json([
                'success' => true,
                'data' => [
                    'gem' => new LevelGemResource($gem),
                ],
                'message' => 'گوهر سطح با موفقیت بروزرسانی شد.',
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->levelGemUploadService->cleanupFiles($storedFiles);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $throwable) {
            $this->levelGemUploadService->cleanupFiles($storedFiles);
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی گوهر سطح',
            ], 500);
        }
    }

    public function destroyFile(Request $request, Level $level): JsonResponse
    {
        $gem = $level->gem;

        if (! $gem) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح گوهری ثبت نشده است.',
            ], 404);
        }

        $validated = $request->validate([
            'field' => ['required', 'string', 'in:png_file,fbx_file'],
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

                $result = $this->levelGemRepository->removeFbxFileEntry($gem, $fileKey);

                if (! $result['found']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'فایل مورد نظر یافت نشد.',
                    ], 404);
                }

                $deletedPath = $this->levelGemUploadService->extractStoragePath($result['url']);
            } else {
                $result = $this->levelGemRepository->clearFileField($gem, $field);

                if (! $result['found']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'فایل مورد نظر یافت نشد.',
                    ], 404);
                }

                $deletedPath = $this->levelGemUploadService->extractStoragePath($result['url']);
            }

            $gem->refresh();

            if ($deletedPath) {
                $this->levelGemUploadService->cleanupFiles([$deletedPath]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'gem' => new LevelGemResource($gem),
                    'field' => $field,
                    $field => $gem->{$field},
                ],
                'message' => 'فایل با موفقیت حذف شد.',
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف فایل گوهر سطح',
            ], 500);
        }
    }
}

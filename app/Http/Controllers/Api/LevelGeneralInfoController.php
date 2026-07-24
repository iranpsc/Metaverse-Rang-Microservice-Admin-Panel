<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Level\LevelGeneralInfoRequest;
use App\Http\Resources\Level\LevelGeneralInfoResource;
use App\Models\Level\Level;
use App\Repositories\LevelGeneralInfoRepository;
use App\Services\Level\LevelGeneralInfoUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class LevelGeneralInfoController extends Controller
{
    public function __construct(
        private LevelGeneralInfoRepository $levelGeneralInfoRepository,
        private LevelGeneralInfoUploadService $levelGeneralInfoUploadService,
    ) {}

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

        $validated = $this->levelGeneralInfoUploadService->preparePayload($request->validated());
        $storedFiles = [];

        try {
            if (isset($validated['fbx_file'])) {
                $validated['fbx_file'] = $this->levelGeneralInfoUploadService->validateFbxFileExtensions($validated['fbx_file']);
            }

            $fileUploads = $this->levelGeneralInfoUploadService->handleFileUploads($request);
            $storedFiles = array_column($fileUploads, 'path');
            $validated = $this->levelGeneralInfoUploadService->applyUploadedFiles($fileUploads, $validated);

            if (! array_key_exists('fbx_file', $validated) || ! is_array($validated['fbx_file'])) {
                unset($validated['fbx_file']);
            }

            $generalInfo = $this->levelGeneralInfoRepository->createForLevel($level, $validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'general_info' => new LevelGeneralInfoResource($generalInfo),
                ],
                'message' => 'اطلاعات کلی سطح با موفقیت ثبت شد.',
            ], 201);
        } catch (InvalidArgumentException $exception) {
            $this->levelGeneralInfoUploadService->cleanupFiles($storedFiles);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $throwable) {
            $this->levelGeneralInfoUploadService->cleanupFiles($storedFiles);
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

        if (! $generalInfo) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح اطلاعات کلی ثبت نشده است.',
            ], 404);
        }

        $validated = $this->levelGeneralInfoUploadService->preparePayload($request->validated());
        $storedFiles = [];
        $replacedFiles = [];

        try {
            if (isset($validated['fbx_file'])) {
                $validated['fbx_file'] = $this->levelGeneralInfoUploadService->validateFbxFileExtensions($validated['fbx_file']);
            }

            $fileUploads = $this->levelGeneralInfoUploadService->handleFileUploads($request);
            $storedFiles = array_column($fileUploads, 'path');
            $previousFbxFiles = is_array($generalInfo->fbx_file) ? $generalInfo->fbx_file : [];
            $replacedFiles = $this->levelGeneralInfoUploadService->collectReplacedFilePaths($generalInfo, $fileUploads);
            $validated = $this->levelGeneralInfoUploadService->applyUploadedFiles($fileUploads, $validated);

            if (array_key_exists('fbx_file', $validated) && is_array($validated['fbx_file'])) {
                $validated['fbx_file'] = $this->levelGeneralInfoUploadService->mergeFbxFileLinks(
                    $previousFbxFiles,
                    $validated['fbx_file']
                );
            } else {
                unset($validated['fbx_file']);
            }

            $generalInfo = $this->levelGeneralInfoRepository->update($generalInfo, $validated);

            $this->levelGeneralInfoUploadService->cleanupFiles(array_filter($replacedFiles));

            return response()->json([
                'success' => true,
                'data' => [
                    'general_info' => new LevelGeneralInfoResource($generalInfo),
                ],
                'message' => 'اطلاعات کلی سطح با موفقیت بروزرسانی شد.',
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->levelGeneralInfoUploadService->cleanupFiles($storedFiles);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $throwable) {
            $this->levelGeneralInfoUploadService->cleanupFiles($storedFiles);
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی اطلاعات کلی سطح',
            ], 500);
        }
    }

    public function destroyFile(Request $request, Level $level): JsonResponse
    {
        $generalInfo = $level->generalInfo;

        if (! $generalInfo) {
            return response()->json([
                'success' => false,
                'message' => 'برای این سطح اطلاعات کلی ثبت نشده است.',
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

                $result = $this->levelGeneralInfoRepository->removeFbxFileEntry($generalInfo, $fileKey);

                if (! $result['found']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'فایل مورد نظر یافت نشد.',
                    ], 404);
                }

                $deletedPath = $this->levelGeneralInfoUploadService->extractStoragePath($result['url']);
            } else {
                $result = $this->levelGeneralInfoRepository->clearFileField($generalInfo, $field);

                if (! $result['found']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'فایل مورد نظر یافت نشد.',
                    ], 404);
                }

                $deletedPath = $this->levelGeneralInfoUploadService->extractStoragePath($result['url']);
            }

            $generalInfo->refresh();

            if ($deletedPath) {
                $this->levelGeneralInfoUploadService->cleanupFiles([$deletedPath]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'general_info' => new LevelGeneralInfoResource($generalInfo),
                    'field' => $field,
                    $field => $generalInfo->{$field},
                ],
                'message' => 'فایل با موفقیت حذف شد.',
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف فایل اطلاعات کلی سطح',
            ], 500);
        }
    }
}

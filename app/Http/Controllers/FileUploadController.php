<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Symfony\Component\HttpFoundation\Response;

class FileUploadController extends Controller
{
    public const ALLOWED_EXTENSIONS = ['bin', 'glb', 'gltf', 'png', 'jpeg', 'jpg', 'fbx', 'mp4'];

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2024'],
        ]);

        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
        $fileReceived = $receiver->receive();

        if ($fileReceived->isFinished()) {
            $file = $fileReceived->getFile();
            $extension = strtolower((string) $file->getClientOriginalExtension());

            if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                @unlink($file->getPathname());

                return response()->json([
                    'success' => false,
                    'status' => false,
                    'message' => 'فرمت فایل مجاز نیست. فرمت‌های مجاز: ' . implode(', ', self::ALLOWED_EXTENSIONS),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $originalName = $file->getClientOriginalName() ?: ('file.' . $extension);
            $safeBaseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'upload';
            $fileName = $safeBaseName . '-' . md5(uniqid((string) time(), true)) . '.' . $extension;

            Storage::disk('public')->makeDirectory('levels');
            $filePath = $file->storeAs('levels', $fileName, 'public');

            @unlink($file->getPathname());

            $fileUrl = url('uploads/' . $filePath);

            return response()->json([
                'success' => true,
                'status' => true,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_url' => $fileUrl,
                'file_type' => $extension,
                'data' => [
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_url' => $fileUrl,
                    'file_type' => $extension,
                ],
                'message' => 'بارگذاری فایل با موفقیت انجام شد.',
            ], Response::HTTP_CREATED);
        }

        $handler = $fileReceived->handler();

        return response()->json([
            'success' => true,
            'status' => true,
            'done' => $handler->getPercentageDone(),
            'data' => [
                'percentage' => $handler->getPercentageDone(),
            ],
            'message' => 'بخشی از فایل بارگذاری شد.',
        ]);
    }
}

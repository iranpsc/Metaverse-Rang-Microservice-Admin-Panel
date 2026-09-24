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
    public const ALLOWED_EXTENSIONS = ['bin', 'glb', 'gltf', 'png', 'jpeg', 'gif', 'jpg', 'fbx', 'mp4'];

    public function upload(Request $request): JsonResponse
    {
        // Chunk size is 1MB; allow headroom for the last (possibly larger) chunk.
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
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
                    'message' => 'فرمت فایل مجاز نیست. فرمت‌های مجاز: '.implode(', ', self::ALLOWED_EXTENSIONS),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $originalName = $file->getClientOriginalName() ?: ('file.'.$extension);
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            // Keep the original basename; only strip path separators and reserved characters.
            $safeBaseName = preg_replace('/[\\\\\\/\\x00<>:"|?*]+/u', '-', (string) $baseName) ?? '';
            $safeBaseName = trim($safeBaseName, " .\t\n\r");
            if ($safeBaseName === '') {
                $safeBaseName = Str::slug((string) $baseName) ?: 'upload';
            }

            $disk = Storage::disk('public');
            $disk->makeDirectory('levels');

            $fileName = $safeBaseName.'.'.$extension;
            $suffix = 2;
            while ($disk->exists('levels/'.$fileName)) {
                $fileName = $safeBaseName.'-'.$suffix.'.'.$extension;
                $suffix++;
            }

            $fileSize = (string) max(0, (int) $file->getSize());
            $filePath = $file->storeAs('levels', $fileName, 'public');

            @unlink($file->getPathname());

            $fileUrl = url('uploads/'.$filePath);

            return response()->json([
                'success' => true,
                'status' => true,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_url' => $fileUrl,
                'file_type' => $extension,
                'file_size' => $fileSize,
                'data' => [
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_url' => $fileUrl,
                    'file_type' => $extension,
                    'file_size' => $fileSize,
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

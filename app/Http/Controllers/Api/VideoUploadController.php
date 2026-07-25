<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Symfony\Component\HttpFoundation\Response;

class VideoUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2024'],
        ]);

        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        $fileReceived = $receiver->receive();

        if ($fileReceived->isFinished()) {
            $file = $fileReceived->getFile();
            $extension = $file->getClientOriginalExtension();
            $fileName = md5(uniqid((string) time(), true)) . '.' . $extension;

            $path = $file->storeAs('resumable-tmp', $fileName);

            unlink($file->getPathname());

            return response()->json([
                'success' => true,
                'file_name' => $fileName,
                'file_path' => $path,
                'data' => [
                    'file_name' => $fileName,
                    'file_path' => $path,
                ],
                'message' => 'بارگذاری ویدیو با موفقیت انجام شد.',
            ], Response::HTTP_CREATED);
        }

        $handler = $fileReceived->handler();

        return response()->json([
            'success' => true,
            'data' => [
                'percentage' => $handler->getPercentageDone(),
            ],
            'message' => 'بخشی از ویدیو بارگذاری شد.',
        ]);
    }
}

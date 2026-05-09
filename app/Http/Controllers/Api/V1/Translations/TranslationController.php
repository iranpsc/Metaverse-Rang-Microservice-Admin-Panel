<?php

namespace App\Http\Controllers\Api\V1\Translations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Translations\StoreTranslationRequest;
use App\Http\Resources\Translations\TranslationResource;
use App\Models\Translations\Translation;
use App\Services\Translations\TranslationService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TranslationController extends Controller
{
    public function __construct(private readonly TranslationService $translationService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $translations = Translation::active()->get();
        return response()->json([
            'data' => $translations->map(function($translation) {
                return [
                    'id' => $translation->id,
                    'code' => $translation->code,
                    'name' => $translation->name,
                    'native_name' => $translation->native_name,
                    'direction' => $translation->direction,
                    'version' => $translation->version,
                    'icon' => asset('assets/images/flags/' . strtoupper($translation->code) . '.svg'),
                    'file_url' => $translation->file_url,
                ];
            }),
        ], 200);
    }

    /**
     * @throws FileNotFoundException
     */
    public function languages(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'languages' => $this->translationService->availableLanguages(),
            ],
            'message' => 'Languages fetched successfully.',
        ]);
    }

    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $translation = $this->translationService->createTranslationByCode($request->get('code'));
        $translation->loadCount('modals');

        return response()->json([
            'success' => true,
            'data' => [
                'translation' => new TranslationResource($translation),
            ],
            'message' => 'Translation created successfully.',
        ], 201);
    }

    public function destroy(Translation $translation): JsonResponse
    {
        $this->translationService->deleteTranslation($translation);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Translation deleted successfully.',
        ]);
    }

    public function show(Translation $translation): JsonResponse
    {
        $translation->loadCount('modals');

        return response()->json([
            'success' => true,
            'data' => [
                'translation' => new TranslationResource($translation),
            ],
            'message' => 'Translation fetched successfully.',
        ]);
    }

    public function toggleStatus(Translation $translation): JsonResponse
    {
        $updatedTranslation = $this->translationService->toggleStatus($translation)->loadCount('modals');

        return response()->json([
            'success' => true,
            'data' => [
                'translation' => new TranslationResource($updatedTranslation),
            ],
            'message' => 'Translation status updated.',
        ]);
    }

    public function export(Translation $translation): JsonResponse|BinaryFileResponse
    {
        $result = $this->translationService->exportTranslation($translation);

        if ($result instanceof BinaryFileResponse) {
            return $result;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'message' => $result,
            ],
            'message' => 'Translation exported successfully.',
        ]);
    }
}



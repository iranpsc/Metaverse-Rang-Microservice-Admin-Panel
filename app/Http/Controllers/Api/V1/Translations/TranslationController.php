<?php

namespace App\Http\Controllers\Api\V1\Translations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Translations\ImportTranslationRequest;
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
    public function __construct(private readonly TranslationService $translationService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Translation::query()->withCount('modals')->orderBy('name');
        $loadActiveTranslations = $request->query('active', false) == true;

        if ($loadActiveTranslations) {
            $query->active();
        }

        $translations = $query->get();

        return response()->json([
            'data' => $translations->map(function ($translation) {
                return [
                    'id' => $translation->id,
                    'code' => $translation->code,
                    'name' => $translation->name,
                    'native_name' => $translation->native_name,
                    'direction' => $translation->direction,
                    'version' => $translation->version,
                    'status' => (bool) $translation->status,
                    'modals_count' => $translation->modals_count,
                    'icon' => asset('assets/images/flags/'.strtoupper($translation->code).'.svg'),
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

    /**
     * Create a new translation from a language code and import flat JSON values.
     */
    public function importNew(ImportTranslationRequest $request): JsonResponse
    {
        $result = $this->translationService->createAndImportTranslation(
            (string) $request->get('code'),
            $request->file('file')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'translation' => new TranslationResource($result['translation']),
                'updated' => $result['updated'],
                'created' => $result['created'],
                'skipped' => $result['skipped'],
                'unknown_ids' => $result['unknown_ids'],
            ],
            'message' => 'Translation created and imported successfully.',
        ], 201);
    }

    /**
     * Import flat JSON values into an existing translation.
     */
    public function import(ImportTranslationRequest $request, Translation $translation): JsonResponse
    {
        $result = $this->translationService->importTranslation(
            $translation,
            $request->file('file')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'translation' => new TranslationResource($result['translation']),
                'updated' => $result['updated'],
                'created' => $result['created'],
                'skipped' => $result['skipped'],
                'unknown_ids' => $result['unknown_ids'],
            ],
            'message' => 'Translation imported successfully.',
        ]);
    }
}

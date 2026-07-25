<?php

namespace App\Http\Controllers\Api\V1\Translations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Translations\StoreModalRequest;
use App\Http\Requests\Translations\UpdateModalRequest;
use App\Http\Resources\Translations\ModalResource;
use App\Models\Translations\Modal;
use App\Models\Translations\Translation;
use App\Services\Translations\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModalController extends Controller
{
    public function __construct(private readonly TranslationService $translationService)
    {
    }

    public function index(Request $request, Translation $translation): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);

        $modals = $translation->modals()
            ->withCount('tabs')
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'modals' => ModalResource::collection($modals->items()),
                'pagination' => [
                    'current_page' => $modals->currentPage(),
                    'last_page' => $modals->lastPage(),
                    'per_page' => $modals->perPage(),
                    'total' => $modals->total(),
                    'from' => $modals->firstItem(),
                    'to' => $modals->lastItem(),
                ],
            ],
            'message' => 'Modals fetched successfully.',
        ]);
    }

    public function store(StoreModalRequest $request, Translation $translation): JsonResponse
    {
        $name = trim($request->get('name'));
        $this->translationService->createModal($name);

        $modal = $translation->modals()->where('name', $name)->withCount('tabs')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'modal' => new ModalResource($modal),
            ],
            'message' => 'Modal created successfully.',
        ], 201);
    }

    public function show(Translation $translation, Modal $modal): JsonResponse
    {
        abort_unless($modal->translation_id === $translation->id, 404);

        $modal->loadCount('tabs');

        return response()->json([
            'success' => true,
            'data' => [
                'modal' => new ModalResource($modal),
            ],
            'message' => 'Modal fetched successfully.',
        ]);
    }

    public function update(UpdateModalRequest $request, Translation $translation, Modal $modal): JsonResponse
    {
        abort_unless($modal->translation_id === $translation->id, 404);

        $name = trim($request->get('name'));
        $this->translationService->updateModal($modal, $name);

        $updatedModal = $translation->modals()->where('name', $name)->withCount('tabs')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'modal' => new ModalResource($updatedModal),
            ],
            'message' => 'Modal updated successfully.',
        ]);
    }

    public function destroy(Translation $translation, Modal $modal): JsonResponse
    {
        abort_unless($modal->translation_id === $translation->id, 404);

        $this->translationService->deleteModal($modal);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Modal deleted successfully.',
        ]);
    }
}



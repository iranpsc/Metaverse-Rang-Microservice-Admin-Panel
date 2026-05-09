<?php

namespace App\Http\Controllers\Api\V1\Translations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Translations\StoreFieldRequest;
use App\Http\Requests\Translations\UpdateFieldRequest;
use App\Http\Resources\Translations\FieldResource;
use App\Models\Translations\Field;
use App\Models\Translations\Modal;
use App\Models\Translations\Tab;
use App\Models\Translations\Translation;
use App\Services\Translations\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    public function __construct(private readonly TranslationService $translationService)
    {
    }

    public function index(Request $request, Translation $translation, Modal $modal, Tab $tab): JsonResponse
    {
        abort_unless($modal->translation_id === $translation->id && $tab->modal_id === $modal->id, 404);

        $perPage = (int) $request->get('per_page', 10);

        $fields = $tab->fields()
            ->orderBy('unique_id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'fields' => FieldResource::collection($fields->items()),
                'pagination' => [
                    'current_page' => $fields->currentPage(),
                    'last_page' => $fields->lastPage(),
                    'per_page' => $fields->perPage(),
                    'total' => $fields->total(),
                    'from' => $fields->firstItem(),
                    'to' => $fields->lastItem(),
                ],
            ],
            'message' => 'Fields fetched successfully.',
        ]);
    }

    public function store(StoreFieldRequest $request, Translation $translation, Modal $modal, Tab $tab): JsonResponse
    {
        abort_unless($modal->translation_id === $translation->id && $tab->modal_id === $modal->id, 404);

        $field = $this->translationService->createField($tab, $request->get('value'));

        return response()->json([
            'success' => true,
            'data' => [
                'field' => new FieldResource($field->refresh()),
            ],
            'message' => 'Field created successfully.',
        ], 201);
    }

    public function update(UpdateFieldRequest $request, Translation $translation, Modal $modal, Tab $tab, Field $field): JsonResponse
    {
        abort_unless(
            $modal->translation_id === $translation->id
            && $tab->modal_id === $modal->id
            && $field->tab_id === $tab->id,
            404
        );

        $this->translationService->updateField($field, $request->get('translation'));

        return response()->json([
            'success' => true,
            'data' => [
                'field' => new FieldResource($field->refresh()),
            ],
            'message' => 'Field updated successfully.',
        ]);
    }

    public function destroy(Translation $translation, Modal $modal, Tab $tab, Field $field): JsonResponse
    {
        abort_unless(
            $modal->translation_id === $translation->id
            && $tab->modal_id === $modal->id
            && $field->tab_id === $tab->id,
            404
        );

        $this->translationService->deleteField($field);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Field deleted successfully.',
        ]);
    }
}



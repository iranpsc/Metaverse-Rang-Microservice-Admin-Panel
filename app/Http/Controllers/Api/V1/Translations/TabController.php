<?php

namespace App\Http\Controllers\Api\V1\Translations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Translations\StoreTabRequest;
use App\Http\Requests\Translations\UpdateTabRequest;
use App\Http\Resources\Translations\TabResource;
use App\Models\Translations\Modal;
use App\Models\Translations\Tab;
use App\Models\Translations\Translation;
use App\Services\Translations\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TabController extends Controller
{
    public function __construct(private readonly TranslationService $translationService)
    {
    }

    public function index(Request $request, Translation $translation, Modal $modal): JsonResponse
    {
        abort_unless($modal->translation_id === $translation->id, 404);

        $perPage = (int) $request->get('per_page', 10);

        $tabs = $modal->tabs()
            ->withCount([
                'fields',
                'fields as translated_fields_count' => fn ($query) => $query->whereNotNull('translation'),
            ])
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'tabs' => TabResource::collection($tabs->items()),
                'pagination' => [
                    'current_page' => $tabs->currentPage(),
                    'last_page' => $tabs->lastPage(),
                    'per_page' => $tabs->perPage(),
                    'total' => $tabs->total(),
                    'from' => $tabs->firstItem(),
                    'to' => $tabs->lastItem(),
                ],
            ],
            'message' => 'Tabs fetched successfully.',
        ]);
    }

    public function store(StoreTabRequest $request, Translation $translation, Modal $modal): JsonResponse
    {
        abort_unless($modal->translation_id === $translation->id, 404);

        $name = trim($request->get('name'));
        $this->translationService->createTab($modal, $name);

        $tab = $modal->tabs()
            ->where('name', $name)
            ->withCount([
                'fields',
                'fields as translated_fields_count' => fn ($query) => $query->whereNotNull('translation'),
            ])
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tab' => new TabResource($tab),
            ],
            'message' => 'Tab created successfully.',
        ], 201);
    }

    public function show(Translation $translation, Modal $modal, Tab $tab): JsonResponse
    {
        abort_unless($modal->translation_id === $translation->id && $tab->modal_id === $modal->id, 404);

        $tab->loadCount([
            'fields',
            'fields as translated_fields_count' => fn ($query) => $query->whereNotNull('translation'),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'tab' => new TabResource($tab),
            ],
            'message' => 'Tab fetched successfully.',
        ]);
    }

    public function update(UpdateTabRequest $request, Translation $translation, Modal $modal, Tab $tab): JsonResponse
    {
        abort_unless($modal->translation_id === $translation->id && $tab->modal_id === $modal->id, 404);

        $name = trim($request->get('name'));
        $this->translationService->updateTab($tab, $name);

        $updatedTab = $modal->tabs()
            ->where('name', $name)
            ->withCount([
                'fields',
                'fields as translated_fields_count' => fn ($query) => $query->whereNotNull('translation'),
            ])
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tab' => new TabResource($updatedTab),
            ],
            'message' => 'Tab updated successfully.',
        ]);
    }

    public function destroy(Translation $translation, Modal $modal, Tab $tab): JsonResponse
    {
        abort_unless($modal->translation_id === $translation->id && $tab->modal_id === $modal->id, 404);

        $this->translationService->deleteTab($tab);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Tab deleted successfully.',
        ]);
    }
}



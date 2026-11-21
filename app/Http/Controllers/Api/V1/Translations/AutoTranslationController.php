<?php

namespace App\Http\Controllers\Api\V1\Translations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Translations\AutoTranslationRequest;
use App\Http\Resources\Translations\AutoTranslationRunResource;
use App\Models\Translations\AutoTranslationRun;
use App\Models\Translations\Translation;
use App\Services\ContentWebhooks\ChangeRecorder;
use App\Services\Translations\AutoTranslationService;
use Illuminate\Http\JsonResponse;

class AutoTranslationController extends Controller
{
    /**
     * Accept a translation request and return the run the admin can track.
     *
     * POST /api/translations/{translation}/auto-translate uses the locale ID from the path.
     *
     * @param  Translation  $translation  Destination bound from the integer locale ID.
     * @param  AutoTranslationService  $service  Service injected by Laravel.
     * @return JsonResponse HTTP 202 with {success: true, data: AutoTranslationRunResource payload}.
     *                      A new run returns HTTP 503 if the selected provider is unconfigured.
     */
    public function store(AutoTranslationRequest $request, Translation $translation, AutoTranslationService $service): JsonResponse
    {
        $run = $service->start($translation, ChangeRecorder::actor() ?? abort(403), $request->validated('provider'));

        return response()->json(['success' => true, 'data' => new AutoTranslationRunResource($run)], 202);
    }

    /**
     * Stop a queued, translating, or publishing run for the bound destination language.
     *
     * DELETE /api/translations/{translation}/auto-translate/{run} takes no request body.
     *
     * @param  Translation  $translation  Destination locale bound from the integer path segment.
     * @param  int  $run  Active run ID scoped to the destination locale.
     * @param  AutoTranslationService  $service  Service that atomically cancels the run.
     * @return JsonResponse HTTP 200 with {success: true, data: AutoTranslationRunResource payload}.
     *                      An inactive or unrelated run returns HTTP 404; a non-runnable status returns HTTP 409.
     */
    public function destroy(Translation $translation, int $run, AutoTranslationService $service): JsonResponse
    {
        $run = $service->cancel($translation, $run);

        return response()->json(['success' => true, 'data' => new AutoTranslationRunResource($run)]);
    }

    /**
     * List runs that are active or awaiting a publication retry.
     *
     * GET /api/translations/auto-translate/runs takes the admin's authentication as input.
     *
     * @return JsonResponse HTTP 200 with {success: true, data: list of AutoTranslationRunResource payloads}.
     */
    public function index(): JsonResponse
    {
        $runs = AutoTranslationRun::query()->where('active', true)->get();

        return response()->json(['success' => true, 'data' => AutoTranslationRunResource::collection($runs)]);
    }

    /**
     * Return the latest run so the dialog can reopen on its current or terminal status.
     *
     * @param  Translation  $translation  Destination bound from the integer locale ID.
     * @return JsonResponse HTTP 200 with {success: true, data: AutoTranslationRunResource payload or null}.
     */
    public function latest(Translation $translation): JsonResponse
    {
        $run = AutoTranslationRun::query()->where('translation_id', $translation->id)->latest('id')->first();

        return response()->json(['success' => true, 'data' => $run ? new AutoTranslationRunResource($run) : null]);
    }

    /**
     * Return progress for a run belonging to the requested language.
     *
     * GET /api/translations/{translation}/auto-translate/{run} uses both IDs from the path.
     *
     * @param  Translation  $translation  Destination bound from the integer locale ID.
     * @param  int  $run  Run ID within that destination.
     * @return JsonResponse HTTP 200 with {success: true, data: AutoTranslationRunResource payload}.
     *                      An unknown run or locale returns HTTP 404.
     */
    public function show(Translation $translation, int $run): JsonResponse
    {
        $run = AutoTranslationRun::query()->where('translation_id', $translation->id)->findOrFail($run);

        return response()->json(['success' => true, 'data' => new AutoTranslationRunResource($run)]);
    }
}

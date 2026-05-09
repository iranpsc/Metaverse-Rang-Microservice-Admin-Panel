<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Level\StoreLevelRequest;
use App\Http\Requests\Level\UpdateLevelRequest;
use App\Http\Resources\Level\LevelResource;
use App\Models\Level\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class LevelsController extends Controller
{
    /**
     * Display a listing of the levels.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = $perPage > 0 ? $perPage : 10;

        $levels = Level::query()
            ->with(['image'])
            ->latest('id')
            ->paginate($perPage);

        $levelsCollection = collect($levels->items())->map(function (Level $level) use ($request) {
            return (new LevelResource($level))->toArray($request);
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'levels' => $levelsCollection,
                'pagination' => [
                    'total' => $levels->total(),
                    'per_page' => $levels->perPage(),
                    'current_page' => $levels->currentPage(),
                    'last_page' => $levels->lastPage(),
                ],
            ],
            'message' => 'لیست سطوح با موفقیت دریافت شد.',
        ]);
    }

    /**
     * Store a newly created level in storage.
     */
    public function store(StoreLevelRequest $request): JsonResponse
    {
        $backgroundPath = null;
        $imagePath = null;

        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $backgroundPath = $request->file('background_image')->store('levels', 'public');
            $backgroundUrl = url('uploads/' . $backgroundPath);

            $level = Level::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'score' => (int) $validated['score'],
                'background_image' => $backgroundUrl,
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('levels', 'public');
                $level->image()->create([
                    'url' => $imagePath,
                ]);
            }

            DB::commit();

            $level->refresh()->load('image');

            return response()->json([
                'success' => true,
                'data' => [
                    'level' => new LevelResource($level),
                ],
                'message' => 'سطح ایجاد شد',
            ], 201);
        } catch (Throwable $throwable) {
            DB::rollBack();

            if ($backgroundPath) {
                Storage::disk('public')->delete($backgroundPath);
            }

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد سطح',
            ], 500);
        }
    }

    /**
     * Update the specified level in storage.
     */
    public function update(UpdateLevelRequest $request, Level $level): JsonResponse
    {
        $newBackgroundPath = null;
        $newImagePath = null;

        $oldBackgroundPath = $this->extractStoragePath($level->background_image);
        $oldImagePath = $level->image?->url;

        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $backgroundUrl = $level->background_image;

            if ($request->hasFile('background_image')) {
                $newBackgroundPath = $request->file('background_image')->store('levels', 'public');
                $backgroundUrl = url('uploads/' . $newBackgroundPath);
            }

            $level->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'score' => (int) $validated['score'],
                'background_image' => $backgroundUrl,
            ]);

            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')->store('levels', 'public');

                if ($level->image) {
                    $level->image->update(['url' => $newImagePath]);
                } else {
                    $level->image()->create(['url' => $newImagePath]);
                }
            }

            DB::commit();

            if ($newBackgroundPath && $oldBackgroundPath) {
                Storage::disk('public')->delete($oldBackgroundPath);
            }

            if ($newImagePath && $oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }

            $level->refresh()->load('image');

            return response()->json([
                'success' => true,
                'data' => [
                    'level' => new LevelResource($level),
                ],
                'message' => 'سطح ویرایش شد',
            ]);
        } catch (Throwable $throwable) {
            DB::rollBack();

            if ($newBackgroundPath) {
                Storage::disk('public')->delete($newBackgroundPath);
            }

            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی سطح',
            ], 500);
        }
    }

    /**
     * Remove the specified level from storage.
     */
    public function destroy(Level $level): JsonResponse
    {
        $imagePath = $level->image?->url;
        $backgroundPath = $this->extractStoragePath($level->background_image);

        try {
            if ($level->prize) {
                $level->prize->delete();
            }

            $level->image?->delete();

            $level->delete();

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            if ($backgroundPath) {
                Storage::disk('public')->delete($backgroundPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'سطح حذف شد',
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف سطح',
            ], 500);
        }
    }

    private function extractStoragePath(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $baseUrl = rtrim(url('uploads'), '/');

        if ($baseUrl && str_starts_with($url, $baseUrl)) {
            return ltrim(substr($url, strlen($baseUrl)), '/');
        }

        return ltrim($url, '/');
    }
}



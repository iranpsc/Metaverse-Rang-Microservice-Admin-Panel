<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ImportMaps;
use App\Models\Map;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MapsController extends Controller
{
    /**
     * Get paginated maps
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $maps = Map::orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'maps' => $maps->items(),
                'pagination' => [
                    'current_page' => $maps->currentPage(),
                    'last_page' => $maps->lastPage(),
                    'per_page' => $maps->perPage(),
                    'total' => $maps->total(),
                    'from' => $maps->firstItem(),
                    'to' => $maps->lastItem(),
                ],
            ],
            'message' => 'Maps retrieved successfully.',
        ]);
    }

    /**
     * Store a new map
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'map_file' => 'required|file|max:10240',
            'point_file' => 'required|file|max:10240',
            'border_file' => 'required|file|max:10240',
            'color' => 'required|string|max:255',
        ]);

        try {
            $mapFileName = $this->sanitizeUploadFileName(
                $request->file('map_file')->getClientOriginalName()
            );
            $borderFileName = $this->sanitizeUploadFileName(
                $request->file('border_file')->getClientOriginalName()
            );
            $pointFileName = $this->sanitizeUploadFileName(
                $request->file('point_file')->getClientOriginalName()
            );

            $request->file('map_file')->storePubliclyAs('maps', $mapFileName, 'public');
            $request->file('border_file')->storePubliclyAs('maps', $borderFileName, 'public');
            $request->file('point_file')->storePubliclyAs('maps', $pointFileName, 'public');

            $fileContents = file_get_contents($this->mapUploadPath($mapFileName));
            $borderFileContents = file_get_contents($this->mapUploadPath($borderFileName));
            $pointFileContents = file_get_contents($this->mapUploadPath($pointFileName));

            // Extract the relevant data from the file contents
            $fileContents = explode('=', $fileContents)[1];
            $borderFileContents = explode('=', $borderFileContents)[1];
            $pointFileContents = explode('=', $pointFileContents)[1];

            // Decode the JSON data
            $fileContents = json_decode($fileContents, true);
            $borderFileContents = json_decode($borderFileContents, true);
            $pointFileContents = json_decode($pointFileContents, true);

            // Calculate the polygon count and total area
            $polygon_count = count($fileContents['features']);
            $polygons_total_area = 0;

            foreach ($fileContents['features'] as $feature) {
                $polygons_total_area += ($feature['properties']['area'] * $feature['properties']['density']);
            }

            // Get the first and last IDs, and the karbari title
            $first_id = $fileContents['features'][0]['properties']['id'];
            $last_id = $fileContents['features'][count($fileContents['features']) - 1]['properties']['id'] ?? '';
            $karbari = $this->getFeatureTitle($fileContents['features'][0]['properties']['karbari']);

            // Create a new Map instance and save it to the database
            $map = new Map;
            $map->name = $validated['name'];
            $map->publish_date = now()->format('Y/m/d');
            $map->publisher_name = Auth::guard('admin')->user()->name;
            $map->polygon_count = $polygon_count;
            $map->total_area = $polygons_total_area;
            $map->first_id = $first_id;
            $map->last_id = $last_id;
            $map->status = 0;
            $map->karbari = $karbari;
            $map->fileName = $mapFileName;
            $map->border_coordinates = json_encode($borderFileContents['features'][0]['geometry']['coordinates'][0][0]);
            $map->central_point_coordinates = json_encode($pointFileContents['features'][0]['geometry']['coordinates']);
            $map->polygon_area = intval($borderFileContents['features'][0]['properties']['area']);
            $map->polygon_address = json_encode($borderFileContents['features'][0]['properties']['address']);
            $map->polygon_color = $validated['color'];
            $map->save();

            return response()->json([
                'success' => true,
                'message' => 'فایل با موفقیت بارگذاری شد',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'نام فایل بارگذاری‌شده نامعتبر است.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Map upload failed.', [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری فایل: خطای داخلی سرور.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update an existing map
     */
    public function update(Request $request, Map $map): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'point_file' => 'required|file|max:10240',
            'border_file' => 'required|file|max:10240',
            'color' => 'required|string|max:255',
        ]);

        try {
            $borderFileName = $this->sanitizeUploadFileName(
                $request->file('border_file')->getClientOriginalName()
            );
            $pointFileName = $this->sanitizeUploadFileName(
                $request->file('point_file')->getClientOriginalName()
            );

            $request->file('border_file')->storePubliclyAs('maps', $borderFileName, 'public');
            $request->file('point_file')->storePubliclyAs('maps', $pointFileName, 'public');

            $borderFileContents = file_get_contents($this->mapUploadPath($borderFileName));
            $pointFileContents = file_get_contents($this->mapUploadPath($pointFileName));

            $borderFileContents = explode('=', $borderFileContents)[1];
            $pointFileContents = explode('=', $pointFileContents)[1];

            $borderFileContents = json_decode($borderFileContents, true);
            $pointFileContents = json_decode($pointFileContents, true);

            $map->update([
                'name' => $validated['name'],
                'polygon_color' => $validated['color'],
                'border_coordinates' => json_encode($borderFileContents['features'][0]['geometry']['coordinates'][0][0]),
                'central_point_coordinates' => json_encode($pointFileContents['features'][0]['geometry']['coordinates']),
                'polygon_area' => intval($borderFileContents['features'][0]['properties']['area']),
                'polygon_address' => json_encode($borderFileContents['features'][0]['properties']['address']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت ویرایش شد',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'نام فایل بارگذاری‌شده نامعتبر است.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Map update failed.', [
                'exception' => $e,
                'map_id' => $map->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در ویرایش اطلاعات: خطای داخلی سرور.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a map
     */
    public function destroy(Map $map): JsonResponse
    {
        try {
            $storedFileName = $this->sanitizeStoredFileName((string) $map->fileName);

            if ($storedFileName !== null && file_exists($this->mapUploadPath($storedFileName))) {
                unlink($this->mapUploadPath($storedFileName));
            }

            $map->delete();

            return response()->json([
                'success' => true,
                'message' => 'نقشه با موفقیت حذف شد',
            ]);
        } catch (\Exception $e) {
            Log::error('Map delete failed.', [
                'exception' => $e,
                'map_id' => $map->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف نقشه: خطای داخلی سرور.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Insert map into database (dispatch ImportMaps job)
     */
    public function insertIntoDatabase(Map $map): JsonResponse
    {
        try {
            ImportMaps::dispatch($map);

            $map->update(['status' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت وارد دیتابیس شد',
            ]);
        } catch (\Exception $e) {
            Log::error('Map import dispatch failed.', [
                'exception' => $e,
                'map_id' => $map->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در وارد کردن اطلاعات: خطای داخلی سرور.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function sanitizeUploadFileName(string $fileName): string
    {
        $fileName = basename(str_replace('\\', '/', $fileName));

        if ($fileName === '' || $fileName === '.' || $fileName === '..') {
            throw new \InvalidArgumentException('Invalid upload file name.');
        }

        if (preg_match('/[\x00-\x1f\x7f]/', $fileName)) {
            throw new \InvalidArgumentException('Invalid upload file name.');
        }

        return $fileName;
    }

    protected function sanitizeStoredFileName(string $fileName): ?string
    {
        try {
            return $this->sanitizeUploadFileName($fileName);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    protected function mapUploadPath(string $fileName): string
    {
        return Storage::disk('public')->path('maps/'.$this->sanitizeUploadFileName($fileName));
    }

    /**
     * Get the title for a feature type.
     *
     * @param  string  $type  The feature type.
     * @return string The title for the feature type.
     */
    protected function getFeatureTitle(string $type): string
    {
        return match ($type) {
            'm' => 'مسکونی',
            't' => 'تجاری',
            'e' => 'اداری',
            'a' => 'آموزشی',
            'b' => 'بهداشتی',
            's' => 'فضای سبز',
            'f' => 'فرهنگی',
            'g' => 'گردشگری',
            'z' => 'مذهبی',
            'n' => 'نمایشگاه',
            default => $type,
        };
    }
}

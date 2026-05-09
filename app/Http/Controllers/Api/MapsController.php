<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ImportMaps;
use App\Models\Map;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MapsController extends Controller
{
    /**
     * Get paginated maps
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'map_file' => 'required|file|max:10240',
            'point_file' => 'required|file|max:10240',
            'border_file' => 'required|file|max:10240',
            'color' => 'required|string|max:255',
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(fn () => app()->environment() === 'production'),
                'integer',
                'digits:6',
                'is_valid_verify_code',
            ],
        ]);

        try {
            // Get the original file names
            $mapFileName = $request->file('map_file')->getClientOriginalName();
            $borderFileName = $request->file('border_file')->getClientOriginalName();
            $pointFileName = $request->file('point_file')->getClientOriginalName();

            // Store the files in the public storage
            $request->file('map_file')->storePubliclyAs('maps', $mapFileName, 'public');
            $request->file('border_file')->storePubliclyAs('maps', $borderFileName, 'public');
            $request->file('point_file')->storePubliclyAs('maps', $pointFileName, 'public');

            // Read the file contents
            $fileContents = file_get_contents(public_path('uploads/maps/' . $mapFileName));
            $borderFileContents = file_get_contents(public_path('uploads/maps/' . $borderFileName));
            $pointFileContents = file_get_contents(public_path('uploads/maps/' . $pointFileName));

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
            $last_id = $fileContents['features'][count($fileContents['features']) - 1]['properties']['id'] ?? "";
            $karbari = $this->getFeatureTitle($fileContents['features'][0]['properties']['karbari']);

            // Create a new Map instance and save it to the database
            $map = new Map();
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری فایل: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing map
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $map = Map::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'point_file' => 'required|file|max:10240',
            'border_file' => 'required|file|max:10240',
            'color' => 'required|string|max:255',
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(fn () => app()->environment() === 'production'),
                'integer',
                'digits:6',
                'is_valid_verify_code',
            ],
        ]);

        try {
            $borderFileName = $request->file('border_file')->getClientOriginalName();
            $pointFileName = $request->file('point_file')->getClientOriginalName();

            $request->file('border_file')->storePubliclyAs('maps', $borderFileName, 'public');
            $request->file('point_file')->storePubliclyAs('maps', $pointFileName, 'public');

            $borderFileContents = file_get_contents(public_path('uploads/maps/' . $borderFileName));
            $pointFileContents = file_get_contents(public_path('uploads/maps/' . $pointFileName));

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
                'polygon_address' => json_encode($borderFileContents['features'][0]['properties']['address'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت ویرایش شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ویرایش اطلاعات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a map
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $map = Map::findOrFail($id);

        try {
            // Delete the map file from storage
            if (file_exists(public_path('uploads/maps/' . $map->fileName))) {
                unlink(public_path('uploads/maps/' . $map->fileName));
            }

            $map->delete();

            return response()->json([
                'success' => true,
                'message' => 'نقشه با موفقیت حذف شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف نقشه: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Insert map into database (dispatch ImportMaps job)
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function insertIntoDatabase(Request $request, int $id): JsonResponse
    {
        $map = Map::findOrFail($id);

        $validated = $request->validate([
            'phone_verification' => [
                'nullable',
                Rule::requiredIf(fn () => app()->environment() === 'production'),
                'integer',
                'digits:6',
                'is_valid_verify_code',
            ],
        ]);

        if ($map->isPublished()) {
            return response()->json([
                'success' => false,
                'message' => 'این نقشه قبلاً منتشر شده است',
            ], 422);
        }

        try {
            ImportMaps::dispatch($map);

            $map->update(['status' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت وارد دیتابیس شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در وارد کردن اطلاعات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the title for a feature type.
     *
     * @param string $type The feature type.
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


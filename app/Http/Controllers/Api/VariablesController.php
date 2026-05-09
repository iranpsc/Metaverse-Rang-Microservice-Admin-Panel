<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Variable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VariablesController extends Controller
{
    /**
     * Get all variables with their relationships
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $variables = Variable::with(['priceChangeLogs', 'image'])->get();

            // Transform data for frontend
            $transformedVariables = $variables->map(function ($variable) {
                return [
                    'id' => $variable->id,
                    'asset' => $variable->asset,
                    'asset_title' => $variable->getAssetTitle(),
                    'price' => $variable->price,
                    'note' => $variable->note,
                    'updated_at' => $variable->updated_at,
                    'image_url' => $variable->image ? $variable->image->url : null,
                    'price_change_logs' => $variable->priceChangeLogs->map(function ($log) {
                        return [
                            'id' => $log->id,
                            'changer_name' => $log->changer_name,
                            'previous_value' => $log->previous_value,
                            'current_value' => $log->current_value,
                            'note' => $log->note,
                            'created_at' => $log->created_at,
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedVariables,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری اطلاعات',
            ], 500);
        }
    }

    /**
     * Store a new variable
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'price' => 'required|numeric|min:1',
            'asset' => 'required|in:red,blue,yellow,irr,psc,satisfaction,effect|unique:variables',
            'image' => 'required|image|max:1024',
        ];

        // Add verification rules if in production
        if (app()->environment('production')) {
            $rules['phone_verification'] = [
                'required',
                'is_valid_verify_code'
            ];
            $rules['access_password'] = [
                'required',
                'is_valid_access_password'
            ];
        }

        $validated = $request->validate($rules);

        try {
            // Create variable
            $variable = Variable::create([
                'asset' => $validated['asset'],
                'price' => $validated['price'],
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('variables', 'public');
                $variable->image()->create([
                    'url' => url('uploads/' . $imagePath)
                ]);
            }

            // Load relationships
            $variable->load(['priceChangeLogs', 'image']);

            return response()->json([
                'success' => true,
                'message' => 'قیمت رنگ با موفقیت وارد شد',
                'data' => [
                    'id' => $variable->id,
                    'asset' => $variable->asset,
                    'asset_title' => $variable->getAssetTitle(),
                    'price' => $variable->price,
                    'note' => $variable->note,
                    'updated_at' => $variable->updated_at,
                    'image_url' => $variable->image ? $variable->image->url : null,
                    'price_change_logs' => [],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت اطلاعات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing variable
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $variable = Variable::find($id);

        if (!$variable) {
            return response()->json([
                'success' => false,
                'message' => 'ارز یافت نشد',
            ], 404);
        }

        $rules = [
            'price' => 'required|numeric|min:1',
            'image' => 'nullable|image|max:1024',
            'note' => 'nullable|string',
        ];

        // Add verification rules if in production
        if (app()->environment('production')) {
            $rules['phone_verification'] = [
                'required',
                'is_valid_verify_code'
            ];
            $rules['access_password'] = [
                'required',
                'is_valid_access_password'
            ];
        }

        $validated = $request->validate($rules);

        try {
            $admin = Auth::guard('admin')->user();

            // Create price change log
            $variable->priceChangeLogs()->create([
                'changer_name' => $admin->name,
                'previous_value' => $variable->price,
                'current_value' => $validated['price'],
                'note' => $validated['note'] ?? null,
            ]);

            // Update variable
            $variable->update([
                'price' => $validated['price'],
                'note' => $validated['note'] ?? null,
            ]);

            // Handle image update
            if ($request->hasFile('image')) {
                // Delete old image
                if ($variable->image) {
                    $variable->image->delete();
                }

                // Upload new image
                $imagePath = $request->file('image')->store('variables', 'public');
                $variable->image()->create([
                    'url' => url('uploads/' . $imagePath)
                ]);
            }

            // Reload relationships
            $variable->load(['priceChangeLogs', 'image']);

            return response()->json([
                'success' => true,
                'message' => 'ارز با موفقیت بروزرسانی شد',
                'data' => [
                    'id' => $variable->id,
                    'asset' => $variable->asset,
                    'asset_title' => $variable->getAssetTitle(),
                    'price' => $variable->price,
                    'note' => $variable->note,
                    'updated_at' => $variable->updated_at,
                    'image_url' => $variable->image ? $variable->image->url : null,
                    'price_change_logs' => $variable->priceChangeLogs->map(function ($log) {
                        return [
                            'id' => $log->id,
                            'changer_name' => $log->changer_name,
                            'previous_value' => $log->previous_value,
                            'current_value' => $log->current_value,
                            'note' => $log->note,
                            'created_at' => $log->created_at,
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی اطلاعات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a variable
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $variable = Variable::find($id);

        if (!$variable) {
            return response()->json([
                'success' => false,
                'message' => 'ارز یافت نشد',
            ], 404);
        }

        try {
            // Delete relationships
            $variable->priceChangeLogs()->delete();
            $variable->image()->delete();
            $variable->delete();

            return response()->json([
                'success' => true,
                'message' => 'ارز با موفقیت حذف شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف ارز',
            ], 500);
        }
    }
}


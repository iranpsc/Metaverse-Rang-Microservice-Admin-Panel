<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Variable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OptionsController extends Controller
{
    /**
     * Get all options with pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $options = Option::with(['priceChangeLogs', 'image'])->paginate($perPage);

            // Transform data for frontend
            $transformedOptions = $options->map(function ($option) {
                $assetPrice = Variable::getRate($option->asset);
                
                return [
                    'id' => $option->id,
                    'code' => $option->code,
                    'asset' => $option->asset,
                    'asset_title' => $option->getAssetTitle(),
                    'amount' => $option->amount,
                    'package_price' => $assetPrice * $option->amount,
                    'note' => $option->note,
                    'updated_at' => $option->updated_at,
                    'image_url' => $option->image ? $option->image->url : null,
                    'price_change_logs' => $option->priceChangeLogs->map(function ($log) {
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
                'data' => [
                    'options' => $transformedOptions,
                    'pagination' => [
                        'total' => $options->total(),
                        'per_page' => $options->perPage(),
                        'current_page' => $options->currentPage(),
                        'last_page' => $options->lastPage(),
                        'from' => $options->firstItem(),
                        'to' => $options->lastItem(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری اطلاعات',
            ], 500);
        }
    }

    /**
     * Get all available variables for dropdown
     *
     * @return JsonResponse
     */
    public function getVariables(): JsonResponse
    {
        try {
            $variables = Variable::all(['id', 'asset'])->map(function ($variable) {
                return [
                    'asset' => $variable->asset,
                    'asset_title' => $variable->getAssetTitle(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $variables,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری اطلاعات',
            ], 500);
        }
    }

    /**
     * Store a new option
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp,gif|max:2048',
            'amount' => 'required|integer|min:1',
            'asset' => 'required|in:red,blue,yellow,psc,irr',
            'code' => 'required|string|unique:options,code',
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
            // Create option
            $option = Option::create([
                'asset' => $validated['asset'],
                'amount' => $validated['amount'],
                'code' => $validated['code'],
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('packages', 'public');
                $option->image()->create([
                    'url' => url('uploads/' . $imagePath)
                ]);
            }

            // Load relationships
            $option->load(['priceChangeLogs', 'image']);

            $assetPrice = Variable::getRate($option->asset);

            return response()->json([
                'success' => true,
                'message' => 'پکیج رنگ وارد شد',
                'data' => [
                    'id' => $option->id,
                    'code' => $option->code,
                    'asset' => $option->asset,
                    'asset_title' => $option->getAssetTitle(),
                    'amount' => $option->amount,
                    'package_price' => $assetPrice * $option->amount,
                    'note' => $option->note,
                    'updated_at' => $option->updated_at,
                    'image_url' => $option->image ? $option->image->url : null,
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
     * Update an existing option
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $option = Option::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'پکیج یافت نشد',
            ], 404);
        }

        $rules = [
            'amount' => 'required|numeric|min:1',
            'code' => 'required|string',
            'asset' => 'required|in:red,blue,yellow,psc,irr',
            'note' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp,gif|max:2048',
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
            $option->priceChangeLogs()->create([
                'changer_name' => $admin->name,
                'previous_value' => $option->amount,
                'current_value' => $validated['amount'],
                'note' => $validated['note'],
            ]);

            // Update option
            $option->update([
                'asset' => $validated['asset'],
                'amount' => $validated['amount'],
                'note' => $validated['note'],
                'code' => $validated['code'],
            ]);

            // Handle image update
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('packages', 'public');

                if ($option->image) {
                    $option->image->update([
                        'url' => url('uploads/' . $imagePath)
                    ]);
                } else {
                    $option->image()->create([
                        'url' => url('uploads/' . $imagePath)
                    ]);
                }
            }

            // Reload relationships
            $option->load(['priceChangeLogs', 'image']);

            $assetPrice = Variable::getRate($option->asset);

            return response()->json([
                'success' => true,
                'message' => 'بسته بروزرسانی شد',
                'data' => [
                    'id' => $option->id,
                    'code' => $option->code,
                    'asset' => $option->asset,
                    'asset_title' => $option->getAssetTitle(),
                    'amount' => $option->amount,
                    'package_price' => $assetPrice * $option->amount,
                    'note' => $option->note,
                    'updated_at' => $option->updated_at,
                    'image_url' => $option->image ? $option->image->url : null,
                    'price_change_logs' => $option->priceChangeLogs->map(function ($log) {
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
     * Delete an option
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $option = Option::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'پکیج یافت نشد',
            ], 404);
        }

        try {
            // Delete relationships
            $option->image()->delete();
            $option->priceChangeLogs()->delete();
            $option->delete();

            return response()->json([
                'success' => true,
                'message' => 'پکیج با موفقیت حذف شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف پکیج',
            ], 500);
        }
    }
}


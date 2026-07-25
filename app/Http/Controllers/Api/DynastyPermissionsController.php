<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dynasty\DynastyPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DynastyPermissionsController extends Controller
{
    /**
     * Get dynasty permissions (single record)
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        try {
            $permissions = DynastyPermission::first();

            // If no permissions exist, return defaults
            if (!$permissions) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => null,
                        'BFR' => 0,
                        'SF' => 0,
                        'W' => 0,
                        'JU' => 0,
                        'DM' => 0,
                        'PIUP' => 0,
                        'PITC' => 0,
                        'PIC' => 0,
                        'ESOO' => 0,
                        'COTB' => 0,
                        'created_at' => null,
                        'updated_at' => null,
                    ],
                    'message' => 'دسترسی‌های سلسله با موفقیت بارگذاری شدند.',
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $permissions->id,
                    'BFR' => $permissions->BFR,
                    'SF' => $permissions->SF,
                    'W' => $permissions->W,
                    'JU' => $permissions->JU,
                    'DM' => $permissions->DM,
                    'PIUP' => $permissions->PIUP,
                    'PITC' => $permissions->PITC,
                    'PIC' => $permissions->PIC,
                    'ESOO' => $permissions->ESOO,
                    'COTB' => $permissions->COTB,
                    'created_at' => $permissions->created_at,
                    'updated_at' => $permissions->updated_at,
                ],
                'message' => 'دسترسی‌های سلسله با موفقیت بارگذاری شدند.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری دسترسی‌های سلسله',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update or create dynasty permissions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'BFR' => 'nullable|boolean',
            'SF' => 'nullable|boolean',
            'W' => 'nullable|boolean',
            'JU' => 'nullable|boolean',
            'DM' => 'nullable|boolean',
            'PIUP' => 'nullable|boolean',
            'PITC' => 'nullable|boolean',
            'PIC' => 'nullable|boolean',
            'ESOO' => 'nullable|boolean',
            'COTB' => 'nullable|boolean',
        ]);

        try {
            // Convert all boolean values to integers (0 or 1)
            $permissionData = [
                'BFR' => $request->input('BFR', 0) ? 1 : 0,
                'SF' => $request->input('SF', 0) ? 1 : 0,
                'W' => $request->input('W', 0) ? 1 : 0,
                'JU' => $request->input('JU', 0) ? 1 : 0,
                'DM' => $request->input('DM', 0) ? 1 : 0,
                'PIUP' => $request->input('PIUP', 0) ? 1 : 0,
                'PITC' => $request->input('PITC', 0) ? 1 : 0,
                'PIC' => $request->input('PIC', 0) ? 1 : 0,
                'ESOO' => $request->input('ESOO', 0) ? 1 : 0,
                'COTB' => $request->input('COTB', 0) ? 1 : 0,
            ];

            $permissions = DynastyPermission::updateOrCreate(
                ['id' => 1],
                $permissionData
            );

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت ثبت شد',
                'data' => [
                    'id' => $permissions->id,
                    'BFR' => $permissions->BFR,
                    'SF' => $permissions->SF,
                    'W' => $permissions->W,
                    'JU' => $permissions->JU,
                    'DM' => $permissions->DM,
                    'PIUP' => $permissions->PIUP,
                    'PITC' => $permissions->PITC,
                    'PIC' => $permissions->PIC,
                    'ESOO' => $permissions->ESOO,
                    'COTB' => $permissions->COTB,
                    'created_at' => $permissions->created_at,
                    'updated_at' => $permissions->updated_at,
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
}


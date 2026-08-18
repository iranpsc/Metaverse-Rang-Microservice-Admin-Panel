<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dynasty\DynastyPrize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DynastyPrizesController extends Controller
{
    /**
     * Get paginated dynasty prizes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 10);

            $prizes = DynastyPrize::paginate($perPage);

            $transformedPrizes = $prizes->map(function (DynastyPrize $prize) {
                return [
                    'id' => $prize->id,
                    'member' => $prize->member,
                    'member_title' => $prize->getRelationTitle(),
                    'satisfaction' => $prize->satisfaction,
                    'introduction_profit_increase' => $prize->introduction_profit_increase,
                    'introduction_profit_increase_percent' => $prize->introduction_profit_increase * 100,
                    'accumulated_capital_reserve' => $prize->accumulated_capital_reserve,
                    'accumulated_capital_reserve_percent' => $prize->accumulated_capital_reserve * 100,
                    'data_storage' => $prize->data_storage,
                    'data_storage_percent' => $prize->data_storage * 100,
                    'psc' => $prize->psc,
                    'created_at' => $prize->created_at,
                    'updated_at' => $prize->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'prizes' => $transformedPrizes,
                    'pagination' => [
                        'current_page' => $prizes->currentPage(),
                        'last_page' => $prizes->lastPage(),
                        'per_page' => $prizes->perPage(),
                        'total' => $prizes->total(),
                        'from' => $prizes->firstItem(),
                        'to' => $prizes->lastItem(),
                    ],
                ],
                'message' => 'جوایز سلسله با موفقیت بارگذاری شدند.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری جوایز سلسله',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new dynasty prize
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'member' => 'required|in:father,mother,brother,offspring,sister,husband,wife|unique:dynasty_prizes,member',
            'satisfaction' => 'required|numeric|min:0',
            'introduction_profit_increase' => 'required|numeric|min:0',
            'accumulated_capital_reserve' => 'required|numeric|min:0',
            'data_storage' => 'required|numeric|min:0',
            'psc' => 'required|numeric|min:0',
        ]);

        try {
            $prize = DynastyPrize::create([
                'member' => $request->input('member'),
                'satisfaction' => $request->input('satisfaction'),
                'introduction_profit_increase' => $request->input('introduction_profit_increase') / 100,
                'accumulated_capital_reserve' => $request->input('accumulated_capital_reserve') / 100,
                'data_storage' => $request->input('data_storage') / 100,
                'psc' => $request->input('psc'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت ثبت شد',
                'data' => [
                    'id' => $prize->id,
                    'member' => $prize->member,
                    'member_title' => $prize->getRelationTitle(),
                    'satisfaction' => $prize->satisfaction,
                    'introduction_profit_increase' => $prize->introduction_profit_increase,
                    'introduction_profit_increase_percent' => $prize->introduction_profit_increase * 100,
                    'accumulated_capital_reserve' => $prize->accumulated_capital_reserve,
                    'accumulated_capital_reserve_percent' => $prize->accumulated_capital_reserve * 100,
                    'data_storage' => $prize->data_storage,
                    'data_storage_percent' => $prize->data_storage * 100,
                    'psc' => $prize->psc,
                    'created_at' => $prize->created_at,
                    'updated_at' => $prize->updated_at,
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
     * Update an existing dynasty prize
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $prize = DynastyPrize::find($id);

        if (! $prize) {
            return response()->json([
                'success' => false,
                'message' => 'پاداش یافت نشد',
            ], 404);
        }

        $request->validate([
            'satisfaction' => 'required|numeric|min:0',
            'introduction_profit_increase' => 'required|numeric|min:0',
            'accumulated_capital_reserve' => 'required|numeric|min:0',
            'data_storage' => 'required|numeric|min:0',
            'psc' => 'required|numeric|min:0',
        ]);

        try {
            $prize->update([
                'satisfaction' => $request->input('satisfaction'),
                'introduction_profit_increase' => $request->input('introduction_profit_increase') / 100,
                'accumulated_capital_reserve' => $request->input('accumulated_capital_reserve') / 100,
                'data_storage' => $request->input('data_storage') / 100,
                'psc' => $request->input('psc'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت ثبت شد',
                'data' => [
                    'id' => $prize->id,
                    'member' => $prize->member,
                    'member_title' => $prize->getRelationTitle(),
                    'satisfaction' => $prize->satisfaction,
                    'introduction_profit_increase' => $prize->introduction_profit_increase,
                    'introduction_profit_increase_percent' => $prize->introduction_profit_increase * 100,
                    'accumulated_capital_reserve' => $prize->accumulated_capital_reserve,
                    'accumulated_capital_reserve_percent' => $prize->accumulated_capital_reserve * 100,
                    'data_storage' => $prize->data_storage,
                    'data_storage_percent' => $prize->data_storage * 100,
                    'psc' => $prize->psc,
                    'created_at' => $prize->created_at,
                    'updated_at' => $prize->updated_at,
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
     * Delete a dynasty prize
     */
    public function destroy(int $id): JsonResponse
    {
        $prize = DynastyPrize::find($id);

        if (! $prize) {
            return response()->json([
                'success' => false,
                'message' => 'پاداش یافت نشد',
            ], 404);
        }

        try {
            $prize->delete();

            return response()->json([
                'success' => true,
                'message' => 'پاداش با موفقیت حذف شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف پاداش',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

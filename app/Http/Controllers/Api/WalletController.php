<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get paginated Wallet records
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);

        // Use the same algorithm as Livewire Assets component
        $wallets = Wallet::with('user:id,name', 'user.features:id,owner_id')
            ->paginate($perPage);

        $formattedWallets = $wallets->map(function ($wallet) {
            return [
                'id' => $wallet->id,
                'user_name' => $wallet->user->name ?? '-',
                'psc' => number_format($wallet->psc ?? 0),
                'blue' => number_format($wallet->blue ?? 0),
                'red' => number_format($wallet->red ?? 0),
                'yellow' => number_format($wallet->yellow ?? 0),
                'irr' => number_format($wallet->irr ?? 0),
                'features_count' => count($wallet->user->features ?? []),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'assets' => $formattedWallets,
                'pagination' => [
                    'current_page' => $wallets->currentPage(),
                    'last_page' => $wallets->lastPage(),
                    'per_page' => $wallets->perPage(),
                    'total' => $wallets->total(),
                    'from' => $wallets->firstItem(),
                    'to' => $wallets->lastItem(),
                ],
            ],
            'message' => 'Assets retrieved successfully.',
        ]);
    }
}


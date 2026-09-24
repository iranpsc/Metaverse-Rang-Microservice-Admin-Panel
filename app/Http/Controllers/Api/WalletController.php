<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    private const SORTABLE_ASSETS = ['psc', 'blue', 'red', 'yellow', 'irr', 'features_count'];

    /**
     * Get paginated Wallet records
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search', '');
        $perPage = $request->input('per_page', 10);
        $asset = $request->input('asset', '');
        $sort = strtolower((string) $request->input('sort', 'desc'));
        $direction = in_array($sort, ['asc', 'desc'], true) ? $sort : 'desc';

        $query = Wallet::with('user:id,name,code', 'user.features:id,owner_id');

        if ($searchTerm) {
            $query->whereHas('user', function (Builder $builder) use ($searchTerm) {
                $builder->where('code', 'like', '%'.$searchTerm.'%');
            });
        }

        $this->applyAssetSort($query, $asset, $direction);

        $wallets = $query->paginate($perPage);

        $formattedWallets = $wallets->map(function ($wallet) {
            return [
                'id' => $wallet->id,
                'user_name' => $wallet->user?->name ?? '-',
                'citizen_code' => $wallet->user?->code ?? '-',
                'psc' => number_format($wallet->psc ?? 0),
                'blue' => number_format($wallet->blue ?? 0),
                'red' => number_format($wallet->red ?? 0),
                'yellow' => number_format($wallet->yellow ?? 0),
                'irr' => number_format($wallet->irr ?? 0),
                'features_count' => count($wallet->user?->features ?? []),
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

    private function applyAssetSort(Builder $query, mixed $asset, string $direction): void
    {
        if (! in_array($asset, self::SORTABLE_ASSETS, true)) {
            return;
        }

        if ($asset === 'features_count') {
            $query->orderBy(
                Feature::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('features.owner_id', 'wallets.user_id'),
                $direction
            );
        } else {
            $query->orderBy($asset, $direction);
        }

        $query->orderBy('wallets.id');
    }
}

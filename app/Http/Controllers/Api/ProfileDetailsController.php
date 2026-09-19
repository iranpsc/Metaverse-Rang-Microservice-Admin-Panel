<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileDetailsController extends Controller
{
    /**
     * Get paginated Profile Details with statistics
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search', '');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $query = User::withSum('activities', 'total')
            ->withSum('payments', 'amount')
            ->withCount([
                'followers',
                'payments',
                'payments as more_than_a_million_payment' => function ($query) {
                    $query->where('amount', '>', 10000000);
                },
            ]);

        if ($searchTerm) {
            $query->where('code', 'like', '%'.$searchTerm.'%');
        }

        $users = $query
            ->orderBy('score', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $formattedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'code' => $user->code ?? '-',
                'created_at' => $user->created_at ? jdate($user->created_at)->format('Y/m/d H:i:s') : '-',
                'activities_sum_total' => number_format($user->activities_sum_total ?? 0),
                'followers_count' => $user->followers_count ?? 0,
                'payments_count' => $user->payments_count ?? 0,
                'more_than_a_million_payment' => $user->more_than_a_million_payment ?? 0,
                'total_deposit_amount' => $this->formatCompactTomans($user->payments_sum_amount ?? 0),
                'score' => number_format($user->score ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $formattedUsers,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],
            ],
            'message' => 'Profile details retrieved successfully.',
        ]);
    }

    /**
     * Convert a Rial amount to Tomans and compact it with K/M suffixes.
     */
    private function formatCompactTomans(int|float $amountInRials): string
    {
        $tomans = (int) round($amountInRials / 10);

        if ($tomans >= 1_000_000) {
            return $this->compactWithSuffix($tomans / 1_000_000, 'M');
        }

        if ($tomans >= 1_000) {
            return $this->compactWithSuffix($tomans / 1_000, 'K');
        }

        return (string) $tomans;
    }

    private function compactWithSuffix(float $value, string $suffix): string
    {
        $formatted = rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');

        return $formatted.$suffix;
    }
}

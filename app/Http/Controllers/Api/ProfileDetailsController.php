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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $searchTerm = $request->get('search', '');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $query = User::withSum('activities', 'total')
            ->withCount([
                'followers',
                'payments',
                'payments as more_than_a_million_payment' => function ($query) {
                    $query->where('amount', '>', 10000000);
                }
            ]);

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('code', 'like', '%' . $searchTerm . '%');
            });
        }

        $users = $query->paginate($perPage, ['*'], 'page', $page);

        $formattedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'code' => $user->code ?? '-',
                'created_at' => $user->created_at ? jdate($user->created_at)->format('Y/m/d H:i:s') : '-',
                'activities_sum_total' => number_format($user->activities_sum_total ?? 0),
                'followers_count' => $user->followers_count ?? 0,
                'payments_count' => $user->payments_count ?? 0,
                'more_than_a_million_payment' => $user->more_than_a_million_payment ?? 0,
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
}


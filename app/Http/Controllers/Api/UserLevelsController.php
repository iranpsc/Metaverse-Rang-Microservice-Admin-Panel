<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserLevelResource;
use App\Models\Level\Level;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserLevelsController extends Controller
{
    use AuthorizesAdminAccess;

    public function __construct()
    {
        $this->authorizeAdminAccess(['level-management', 'manage-level']);
    }

    /**
     * Display a paginated listing of users with level information.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = $perPage > 0 ? $perPage : 10;
        $search = trim((string) $request->input('search', ''));

        $query = User::query()
            ->select(['id', 'name', 'code', 'score'])
            ->with(['levels'])
            ->orderBy('score', 'desc');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $users = $query->paginate($perPage);

        $usersCollection = collect($users->items())->map(function (User $user) use ($request) {
            return (new UserLevelResource($user))->toArray($request);
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $usersCollection,
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                ],
            ],
            'message' => 'لیست سطوح کاربران با موفقیت دریافت شد.',
        ]);
    }

    /**
     * Search users for Select2 dropdown with infinite scroll.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $search = trim($validated['search'] ?? '');
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = User::query()
            ->select(['id', 'name', 'code'])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $users = $query->paginate($perPage, ['*'], 'page', $page);

        $options = collect($users->items())->map(function (User $user) {
            $code = $user->code ? " ({$user->code})" : '';

            return [
                'value' => $user->id,
                'label' => $user->name . $code,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'options' => $options,
                'pagination' => [
                    'more' => $users->hasMorePages(),
                ],
            ],
            'message' => 'لیست کاربران با موفقیت دریافت شد.',
        ]);
    }

    /**
     * Promote a user by incrementing score and optionally attaching a new level.
     */
    public function promote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'score' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $user = User::query()
                ->with(['levels'])
                ->lockForUpdate()
                ->findOrFail($validated['user_id']);

            $user->score = (int) ($user->score ?? 0) + (int) $validated['score'];
            $user->save();

            $this->applyLevelPromotion($user);

            DB::commit();

            $user->refresh()->load('levels');

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => new UserLevelResource($user),
                ],
                'message' => 'سطح کاربر با موفقیت ارتقاء یافت.',
            ]);
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'خطا در ارتقاء سطح کاربر',
            ], 500);
        }
    }

    /**
     * Attach all qualifying levels based on the user's current score.
     */
    private function applyLevelPromotion(User $user): void
    {
        while (true) {
            $user->load('levels');

            $achievedLevelIds = $user->levels->pluck('id')->all();
            $currentLevel = $user->levels
                ->sortByDesc(fn (Level $level) => $level->numeric_score)
                ->first();

            if (!$currentLevel) {
                $qualifyingLevel = Level::query()
                    ->whereNumericScore('<=', (int) $user->score)
                    ->orderByNumericScore()
                    ->first();

                if ($qualifyingLevel && !in_array($qualifyingLevel->id, $achievedLevelIds, true)) {
                    $user->levels()->attach($qualifyingLevel->id);
                    continue;
                }

                break;
            }

            $nextLevel = Level::query()
                ->whereNumericScore('>', $currentLevel->numeric_score)
                ->orderByNumericScore()
                ->first();

            if (
                $nextLevel
                && (int) $user->score >= $nextLevel->numeric_score
                && !in_array($nextLevel->id, $achievedLevelIds, true)
            ) {
                $user->levels()->attach($nextLevel->id);
                continue;
            }

            break;
        }
    }
}

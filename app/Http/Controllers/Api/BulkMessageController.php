<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkMessage\SendBulkMessageRequest;
use App\Jobs\SendBulkEmailJob;
use App\Jobs\SendBulkSmsJob;
use App\Models\User;
use App\Services\BulkMessage\BulkMessageUserQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BulkMessageController extends Controller
{
    public function __construct(
        private readonly BulkMessageUserQueryService $userQueryService
    ) {
    }

    public function send(SendBulkMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $bulkSendId = (string) Str::uuid();
        $channel = $validated['channel'];

        $userQuery = $this->userQueryService->buildQuery(
            $validated['target_type'],
            [
                'level_ids' => $validated['level_ids'] ?? [],
                'code_from' => $validated['code_from'] ?? null,
                'code_to' => $validated['code_to'] ?? null,
                'user_ids' => $validated['user_ids'] ?? [],
            ]
        );

        if ($channel === 'sms') {
            (clone $userQuery)->select('id')->chunkById(200, function ($users) use ($validated, $bulkSendId) {
                SendBulkSmsJob::dispatch(
                    $users->pluck('id')->all(),
                    $validated['sms_content'],
                    $bulkSendId
                );
            });
        }

        if ($channel === 'email') {
            (clone $userQuery)->select('id')->chunkById(100, function ($users) use ($validated, $bulkSendId) {
                SendBulkEmailJob::dispatch(
                    $users->pluck('id')->all(),
                    $validated['email_content'],
                    $bulkSendId
                );
            });
        }

        return response()->json([
            'success' => true,
            'data' => [
                'bulk_send_id' => $bulkSendId,
            ],
            'message' => 'ارسال پیام در صف قرار گرفت.',
        ], 202);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $this->authorizeSuperAdmin();

        $search = $request->get('search', '');
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(50, max(1, (int) $request->get('per_page', 20)));

        $query = User::query()->orderBy('name');

        if ($search !== '') {
            $normalizedCode = preg_replace('/^hm-?/i', '', $search);

            $query->where(function ($subQuery) use ($search, $normalizedCode) {
                $subQuery->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%');

                if ($normalizedCode !== $search) {
                    $subQuery->orWhere('code', 'like', '%hm-'.$normalizedCode.'%');
                }
            });
        }

        $users = $query->paginate($perPage, ['*'], 'page', $page);

        $options = $users->getCollection()->map(function (User $user) {
            $code = $user->code ?? '-';

            return [
                'value' => $user->id,
                'label' => "{$user->name} ({$code})",
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'options' => $options,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'more' => $users->hasMorePages(),
                ],
            ],
            'message' => 'کاربران با موفقیت بارگذاری شدند.',
        ]);
    }

    private function authorizeSuperAdmin(): void
    {
        $admin = auth('admin')->user();

        if (! $admin || ! $admin->hasRole('super-admin')) {
            abort(403, 'دسترسی غیرمجاز');
        }
    }
}

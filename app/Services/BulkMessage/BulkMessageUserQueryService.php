<?php

namespace App\Services\BulkMessage;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class BulkMessageUserQueryService
{
    public function buildQuery(string $targetType, array $params = []): Builder
    {
        $query = User::query();

        return match ($targetType) {
            'all' => $query,
            'levels' => $this->applyLevelsFilter($query, $params['level_ids'] ?? []),
            'code_range' => $this->applyCodeRangeFilter($query, $params['code_from'] ?? '', $params['code_to'] ?? ''),
            'no_wallet' => $query->whereNull('wallet_address'),
            'selected_users' => $this->applySelectedUsersFilter($query, $params['user_ids'] ?? []),
            default => $query->whereRaw('0 = 1'),
        };
    }

    private function applyLevelsFilter(Builder $query, array $levelIds): Builder
    {
        if (empty($levelIds)) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereHas('levels', function ($q) use ($levelIds) {
            $q->whereIn('levels.id', $levelIds)
                ->whereRaw('levels.slug = (
                    SELECT MAX(l2.slug) FROM level_user lu2
                    JOIN levels l2 ON lu2.level_id = l2.id
                    WHERE lu2.user_id = level_user.user_id
                )');
        });
    }

    private function applyCodeRangeFilter(Builder $query, string $codeFrom, string $codeTo): Builder
    {
        $from = 'hm-' . $codeFrom;
        $to = 'hm-' . $codeTo;

        return $query->where('code', '>=', $from)
            ->where('code', '<=', $to);
    }

    private function applySelectedUsersFilter(Builder $query, array $userIds): Builder
    {
        if (empty($userIds)) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('id', $userIds);
    }
}

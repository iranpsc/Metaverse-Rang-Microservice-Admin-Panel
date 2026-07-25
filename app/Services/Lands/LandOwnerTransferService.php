<?php

namespace App\Services\Lands;

use App\Models\Feature;
use App\Models\FeatureProperties;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class LandOwnerTransferService
{
    private const SYSTEM_OWNER_ID = 1;

    /**
     * @return array{options: Collection, pagination: array<string, mixed>}
     */
    public function getOptions(string $type, string $search, int $page, int $perPage): array
    {
        return match ($type) {
            'lands' => $this->getLandOptions($search, $page, $perPage),
            'users' => $this->getUserOptions($search, $page, $perPage),
            default => throw new \InvalidArgumentException("Unsupported owner transfer option type [{$type}]."),
        };
    }

    public function transferOwner(int $featureId, int $newOwnerId): void
    {
        $feature = Feature::findOrFail($featureId);

        if ($feature->owner_id !== self::SYSTEM_OWNER_ID) {
            throw new UnprocessableEntityHttpException('فقط زمین‌های بدون مالک کاربر قابل انتقال هستند');
        }

        DB::transaction(function () use ($feature, $newOwnerId) {
            $feature->update(['owner_id' => $newOwnerId]);
        });
    }

    /**
     * @return array{options: Collection, pagination: array<string, mixed>}
     */
    private function getLandOptions(string $search, int $page, int $perPage): array
    {
        $query = FeatureProperties::with(['feature.owner:id,name'])
            ->orderBy('id', 'asc');

        if ($search !== '') {
            $query->where('id', 'like', '%' . $search . '%');
        }

        $properties = $query->paginate($perPage, ['*'], 'page', $page);

        $options = $properties->getCollection()->map(function (FeatureProperties $property) {
            $ownerId = $property->feature?->owner_id;
            $transferable = $ownerId === self::SYSTEM_OWNER_ID;
            $ownerName = $property->feature?->owner?->name ?? '-';

            return [
                'value' => $property->feature_id,
                'label' => $transferable
                    ? $property->id
                    : "{$property->id} (مالک: {$ownerName})",
                'disabled' => !$transferable,
            ];
        })->values();

        return [
            'options' => $options,
            'pagination' => $this->formatPagination($properties),
        ];
    }

    /**
     * @return array{options: Collection, pagination: array<string, mixed>}
     */
    private function getUserOptions(string $search, int $page, int $perPage): array
    {
        $query = User::query()
            ->where('id', '!=', self::SYSTEM_OWNER_ID)
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->paginate($perPage, ['*'], 'page', $page);

        $options = $users->getCollection()->map(function (User $user) {
            $code = $user->code ?? '-';

            return [
                'value' => $user->id,
                'label' => "{$user->name} ({$code})",
                'disabled' => false,
            ];
        })->values();

        return [
            'options' => $options,
            'pagination' => $this->formatPagination($users),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPagination(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'more' => $paginator->hasMorePages(),
        ];
    }
}

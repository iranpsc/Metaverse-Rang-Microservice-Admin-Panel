<?php

namespace App\Services\Dynasty;

use App\Models\Dynasty\DynastyPrize;
use App\Models\Dynasty\ReceivedPrize;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DynastyPrizeService
{
    /**
     * @return array{prizes: LengthAwarePaginator, total_paid_amount: int}
     */
    public function list(int $perPage = 10): array
    {
        $perPage = $perPage > 0 ? $perPage : 10;

        $prizes = DynastyPrize::query()
            ->withCount(['receivedPrizes as recipients_count'])
            ->orderBy('id')
            ->paginate($perPage);

        return [
            'prizes' => $prizes,
            'total_paid_amount' => $this->calculateTotalPaidAmount(),
        ];
    }

    public function create(array $data): DynastyPrize
    {
        $prize = DynastyPrize::create($this->normalizePercentFields($data, includeMember: true));

        return $prize->loadCount(['receivedPrizes as recipients_count']);
    }

    public function update(DynastyPrize $prize, array $data): DynastyPrize
    {
        $prize->update($this->normalizePercentFields($data, includeMember: false));

        return $prize->fresh()->loadCount(['receivedPrizes as recipients_count']);
    }

    public function delete(DynastyPrize $prize): void
    {
        $prize->delete();
    }

    public function findOrFail(int $id): DynastyPrize
    {
        return DynastyPrize::query()->findOrFail($id);
    }

    public function calculateTotalPaidAmount(): int
    {
        return (int) ReceivedPrize::query()
            ->join('dynasty_prizes', 'dynasty_prizes.id', '=', 'received_prizes.prize_id')
            ->sum('dynasty_prizes.psc');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizePercentFields(array $data, bool $includeMember): array
    {
        $payload = [
            'satisfaction' => $data['satisfaction'],
            'introduction_profit_increase' => $data['introduction_profit_increase'] / 100,
            'accumulated_capital_reserve' => $data['accumulated_capital_reserve'] / 100,
            'data_storage' => $data['data_storage'] / 100,
            'psc' => $data['psc'],
        ];

        if ($includeMember) {
            $payload['member'] = $data['member'];
        }

        return $payload;
    }
}

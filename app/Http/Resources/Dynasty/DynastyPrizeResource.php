<?php

namespace App\Http\Resources\Dynasty;

use App\Models\Dynasty\DynastyPrize;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DynastyPrize
 */
class DynastyPrizeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $recipientsCount = (int) ($this->recipients_count ?? $this->receivedPrizes()->count());
        $psc = (int) $this->psc;

        return [
            'id' => $this->id,
            'member' => $this->member,
            'member_title' => $this->getRelationTitle(),
            'satisfaction' => $this->satisfaction,
            'introduction_profit_increase' => $this->introduction_profit_increase,
            'introduction_profit_increase_percent' => $this->introduction_profit_increase * 100,
            'accumulated_capital_reserve' => $this->accumulated_capital_reserve,
            'accumulated_capital_reserve_percent' => $this->accumulated_capital_reserve * 100,
            'data_storage' => $this->data_storage,
            'data_storage_percent' => $this->data_storage * 100,
            'psc' => $psc,
            'recipients_count' => $recipientsCount,
            'total_paid_amount' => $recipientsCount * $psc,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

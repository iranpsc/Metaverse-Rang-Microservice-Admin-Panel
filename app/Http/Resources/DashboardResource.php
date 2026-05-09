<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'users' => [
                'all' => $this->resource['users']['all'],
                'verified' => $this->resource['users']['verified'],
                'verified_phone' => $this->resource['users']['verified_phone'],
                'kyc_verified' => $this->resource['users']['kyc_verified'],
            ],
            'dynasties' => $this->resource['dynasties'],
            'features' => [
                'all' => $this->resource['features']['all'],
                'sold' => $this->resource['features']['sold'],
            ],
            'referrals' => $this->resource['referrals'],
            'referral_amount' => $this->resource['referral_amount'],
            'sold_assets' => [
                'psc' => $this->resource['sold_assets']['psc'],
                'red' => $this->resource['sold_assets']['red'],
                'blue' => $this->resource['sold_assets']['blue'],
                'yellow' => $this->resource['sold_assets']['yellow'],
            ],
            'deposited_rial_amount' => $this->resource['deposited_rial_amount'],
        ];
    }
}


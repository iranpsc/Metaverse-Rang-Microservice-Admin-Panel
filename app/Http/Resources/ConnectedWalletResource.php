<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConnectedWalletResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code ?? '-',
            'wallet_address' => $this->wallet_address,
            'registered_at' => $this->created_at
                ? jdate($this->created_at)->format('Y/m/d H:i:s')
                : '-',
        ];
    }
}

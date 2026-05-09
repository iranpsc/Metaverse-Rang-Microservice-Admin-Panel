<?php

namespace App\Http\Resources\SystemVariable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\VariableChangeLog
 */
class SystemVariableChangeLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'changer_name' => $this->changer_name,
            'previous_value' => $this->previous_value,
            'current_value' => $this->current_value,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}



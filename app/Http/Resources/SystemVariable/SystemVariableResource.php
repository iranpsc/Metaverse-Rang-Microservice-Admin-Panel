<?php

namespace App\Http\Resources\SystemVariable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\SystemVariable
 */
class SystemVariableResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'value' => $this->value,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'change_logs' => $this->whenLoaded('changeLogs', function () use ($request) {
                return SystemVariableChangeLogResource::collection($this->changeLogs)->resolve($request);
            }, []),
        ];
    }
}



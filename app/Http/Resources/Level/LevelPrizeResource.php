<?php

namespace App\Http\Resources\Level;

use Illuminate\Http\Resources\Json\JsonResource;

class LevelPrizeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'psc' => (int) $this->psc,
            'yellow' => (int) $this->yellow,
            'blue' => (int) $this->blue,
            'red' => (int) $this->red,
            'effect' => (int) $this->effect,
            'satisfaction' => (float) $this->satisfaction,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}



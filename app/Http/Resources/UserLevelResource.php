<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLevelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $achievedLevels = $this->levels ?? collect();
        $currentLevel = $achievedLevels->sortByDesc(fn ($level) => $level->pivot->created_at)->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'score' => (int) ($this->score ?? 0),
            'current_level' => $currentLevel ? [
                'id' => $currentLevel->id,
                'name' => $currentLevel->name,
                'slug' => $currentLevel->slug,
                'score' => (int) $currentLevel->score,
            ] : null,
            'achieved_levels' => $achievedLevels
                ->sortBy(fn ($level) => $level->numeric_score)
                ->values()
                ->map(fn ($level) => [
                'id' => $level->id,
                'name' => $level->name,
                'slug' => $level->slug,
                'score' => (int) $level->score,
                'achieved_at' => optional($level->pivot->created_at)->toISOString(),
            ])->values(),
        ];
    }
}

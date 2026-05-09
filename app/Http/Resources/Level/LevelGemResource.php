<?php

namespace App\Http\Resources\Level;

use Illuminate\Http\Resources\Json\JsonResource;

class LevelGemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'thread' => $this->thread,
            'points' => (int) $this->points,
            'volume' => $this->volume !== null ? (float) $this->volume : null,
            'color' => $this->color,
            'png_file' => $this->png_file,
            'fbx_file' => $this->fbx_file,
            'encryption' => (bool) $this->encryption,
            'designer' => $this->designer,
            'has_animation' => (bool) $this->has_animation,
            'lines' => (int) $this->lines,
        ];
    }
}



<?php

namespace App\Http\Resources\Level;

use Illuminate\Http\Resources\Json\JsonResource;

class LevelGeneralInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'score' => (int) $this->score,
            'description' => $this->description,
            'rank' => (int) $this->rank,
            'subcategories' => (int) $this->subcategories,
            'persian_font' => $this->persian_font,
            'english_font' => $this->english_font,
            'file_volume' => $this->file_volume !== null ? (float) $this->file_volume : null,
            'used_colors' => $this->used_colors,
            'points' => (int) $this->points,
            'designer' => $this->designer,
            'model_designer' => $this->model_designer,
            'creation_date' => $this->creation_date,
            'has_animation' => (bool) $this->has_animation,
            'lines' => (int) $this->lines,
            'png_file' => $this->png_file,
            'fbx_file' => $this->fbx_file,
            'gif_file' => $this->gif_file,
        ];
    }
}



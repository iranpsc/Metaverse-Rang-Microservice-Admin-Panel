<?php

namespace App\Http\Resources\Level;

use Illuminate\Http\Resources\Json\JsonResource;

class LevelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $imagePath = $this->image?->url;
        $imageFullUrl = $imagePath ? url('uploads/' . ltrim($imagePath, '/')) : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'score' => (int) $this->score,
            'background_image' => $this->background_image,
            'background_image_url' => $this->background_image,
            'image' => $this->when($this->image, function () use ($imagePath, $imageFullUrl) {
                return [
                    'id' => $this->image->id,
                    'url' => $imagePath,
                    'full_url' => $imageFullUrl,
                ];
            }),
            'image_url' => $imageFullUrl,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}



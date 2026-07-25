<?php

namespace App\Http\Resources\Videos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\VideoSubCategory
 */
class VideoSubCategoryResource extends JsonResource
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
            'video_category_id' => $this->video_category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image,
            'image_url' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'icon' => $this->icon,
            'icon_url' => $this->icon ? Storage::disk('public')->url($this->icon) : null,
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at ? [
                'date' => jdate($this->created_at)->format('Y/m/d'),
                'time' => jdate($this->created_at)->format('H:i:s'),
            ] : null,
            'category' => $this->whenLoaded('category', function () use ($request) {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),
        ];
    }
}



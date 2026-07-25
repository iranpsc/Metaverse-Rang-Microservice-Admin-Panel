<?php

namespace App\Http\Resources\Videos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\VideoCategory
 */
class VideoCategoryResource extends JsonResource
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
            'sub_categories_count' => $this->when(isset($this->sub_categories_count), $this->sub_categories_count),
            'sub_categories' => $this->whenLoaded('subCategories', function () use ($request) {
                return VideoSubCategoryResource::collection($this->subCategories)->resolve($request);
            }, []),
        ];
    }
}



<?php

namespace App\Http\Resources\Videos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\Video
 */
class VideoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $category = $this->whenLoaded('category');
        $parentCategory = $category?->category;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'creator_code' => $this->creator_code,
            'file_name' => $this->fileName,
            'file_url' => $this->fileName ? Storage::disk('public')->url($this->fileName) : null,
            'image' => $this->image,
            'image_url' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'parent' => $parentCategory ? [
                    'id' => $parentCategory->id,
                    'name' => $parentCategory->name,
                    'slug' => $parentCategory->slug,
                ] : null,
            ] : null,
            'views_count' => $this->when(isset($this->views_count), $this->views_count, fn () => $this->views()->count()),
            'likes_count' => $this->when(isset($this->likes_count), $this->likes_count, fn () => $this->interactions()->where('liked', 1)->count()),
            'dislikes_count' => $this->when(isset($this->dislikes_count), $this->dislikes_count, fn () => $this->interactions()->where('liked', 0)->count()),
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at ? [
                'date' => jdate($this->created_at)->format('Y/m/d'),
                'time' => jdate($this->created_at)->format('H:i:s'),
            ] : null,
        ];
    }
}

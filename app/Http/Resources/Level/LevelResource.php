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
        $imageFullUrl = $this->uploadUrl($imagePath);
        $backgroundImageUrl = $this->uploadUrl($this->background_image);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'score' => (int) $this->score,
            'background_image' => $backgroundImageUrl,
            'background_image_url' => $backgroundImageUrl,
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

    private function uploadUrl(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $path = parse_url($value, PHP_URL_PATH);

        if (is_string($path) && str_starts_with($path, '/uploads/')) {
            $query = parse_url($value, PHP_URL_QUERY);
            $fragment = parse_url($value, PHP_URL_FRAGMENT);
            $suffix = ($query !== null ? '?'.$query : '').($fragment !== null ? '#'.$fragment : '');

            return url(ltrim($path, '/')).$suffix;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        return url('uploads/'.ltrim($value, '/'));
    }
}

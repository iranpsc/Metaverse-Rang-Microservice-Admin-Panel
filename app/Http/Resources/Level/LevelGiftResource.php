<?php

namespace App\Http\Resources\Level;

use Illuminate\Http\Resources\Json\JsonResource;

class LevelGiftResource extends JsonResource
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
            'monthly_capacity_count' => (int) $this->monthly_capacity_count,
            'store_capacity' => (bool) $this->store_capacity,
            'sell_capacity' => (bool) $this->sell_capacity,
            'features' => $this->features,
            'sell' => (bool) $this->sell,
            'vod_document_registration' => (bool) $this->vod_document_registration,
            'seller_link' => $this->seller_link,
            'designer' => $this->designer,
            'three_d_model_volume' => (float) $this->three_d_model_volume,
            'three_d_model_points' => (int) $this->three_d_model_points,
            'three_d_model_lines' => (int) $this->three_d_model_lines,
            'has_animation' => (bool) $this->has_animation,
            'png_file' => $this->png_file,
            'fbx_file' => $this->fbx_file,
            'gif_file' => $this->gif_file,
            'rent' => (bool) $this->rent,
            'vod_count' => (int) $this->vod_count,
            'start_vod_id' => $this->start_vod_id,
            'end_vod_id' => $this->end_vod_id,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}



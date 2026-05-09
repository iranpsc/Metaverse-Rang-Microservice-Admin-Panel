<?php

namespace App\Http\Resources\Translations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Translations\Modal */
class ModalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'translation_id' => $this->translation_id,
            'name' => $this->name,
            'tabs_count' => $this->whenCounted('tabs'),
        ];
    }
}



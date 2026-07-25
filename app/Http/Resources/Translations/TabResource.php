<?php

namespace App\Http\Resources\Translations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Translations\Tab */
class TabResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fieldsCount = (int) ($this->fields_count ?? 0);
        $translatedFields = (int) ($this->translated_fields_count ?? 0);

        $progress = $fieldsCount > 0 ? round(($translatedFields / $fieldsCount) * 100) : 0;

        return [
            'id' => $this->id,
            'modal_id' => $this->modal_id,
            'name' => $this->name,
            'fields_count' => $fieldsCount,
            'translated_fields_count' => $translatedFields,
            'progress' => $progress,
        ];
    }
}



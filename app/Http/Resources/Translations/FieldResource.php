<?php

namespace App\Http\Resources\Translations;

use App\Models\Translations\Field;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Field */
class FieldResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tab_id' => $this->tab_id,
            'unique_id' => $this->unique_id,
            'name' => $this->name,
            'translation' => $this->translation,
        ];
    }
}

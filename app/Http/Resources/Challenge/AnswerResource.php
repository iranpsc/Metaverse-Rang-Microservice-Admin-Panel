<?php

namespace App\Http\Resources\Challenge;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Challenge\Answer
 */
class AnswerResource extends JsonResource
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
            'question_id' => $this->question_id,
            'title' => $this->title,
            'image' => $this->image,
            'is_correct' => (bool) $this->is_correct,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}



<?php

namespace App\Http\Resources\Challenge;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Challenge\Question
 */
class QuestionResource extends JsonResource
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
            'code' => $this->code,
            'title' => $this->title,
            'creator_code' => $this->creator_code,
            'prize' => $this->prize,
            'image' => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'answers' => $this->whenLoaded('answers', function () use ($request) {
                return AnswerResource::collection($this->answers)->resolve($request);
            }, []),
        ];
    }
}



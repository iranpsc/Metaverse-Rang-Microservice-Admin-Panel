<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KycResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fname' => $this->fname,
            'lname' => $this->lname,
            'melli_code' => $this->melli_code,
            'birthdate' => $this->birthdate ? jdate($this->birthdate)->format('Y/m/d') : null,
            'created_at' => $this->created_at ? jdate($this->created_at)->format('Y/m/d') : null,
            'province' => $this->province,
            'gender' => $this->gender,
            'melli_card' => $this->melli_card,
            'video' => $this->video,
            'status' => $this->status,
            'status_badge' => $this->status_badge,
            'errors' => $this->errors,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'code' => $this->user->code,
                ];
            }),
            'verify_text' => $this->whenLoaded('verifyText', function () {
                return [
                    'id' => $this->verifyText->id,
                    'text' => $this->verifyText->text,
                ];
            }),
        ];
    }
}


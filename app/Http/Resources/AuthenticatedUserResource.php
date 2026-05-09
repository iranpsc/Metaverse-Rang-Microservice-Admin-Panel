<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthenticatedUserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'image' => $this->image,
            'active' => $this->active,
            'token' => $this->whenHas('token', $this->token),
            'token_expires_at' => $this->whenHas('token_expires_at', $this->token_expires_at),
            'roles' => $this->roles->pluck('name'),
            'permissions' => $this->permissions->pluck('name'),
        ];
    }
}


<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $jalali = $this->created_at ? Jalalian::fromDateTime($this->created_at) : null;

        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'title' => $this->title,
            'content' => $this->content,
            'url' => $this->url,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'code' => $this->user?->code,
            ],
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    $url = $image->url;

                    if ($url && !Str::startsWith($url, ['http://', 'https://'])) {
                        $url = url('storage/' . ltrim($url, '/'));
                    }

                    return [
                        'id' => $image->id,
                        'url' => $url,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'created_at_jalali' => $jalali?->format('Y/m/d'),
            'created_at_time' => $jalali?->format('H:i:s'),
        ];
    }
}



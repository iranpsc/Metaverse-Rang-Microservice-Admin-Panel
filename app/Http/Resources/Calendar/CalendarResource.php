<?php

namespace App\Http\Resources\Calendar;

use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;

class CalendarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $startsAt = $this->starts_at;
        $endsAt = $this->ends_at;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'version_title' => $this->version_title,
            'content' => $this->content,
            'color' => $this->color,
            'btn_name' => $this->btn_name,
            'btn_link' => $this->btn_link,
            'writer' => $this->writer,
            'image_url' => $this->image,
            'status' => $this->getStatus(),
            'is_version' => (bool) $this->is_version,
            'starts_at' => $startsAt?->toIso8601String(),
            'ends_at' => $endsAt?->toIso8601String(),
            'start_date' => $startsAt ? Jalalian::fromCarbon($startsAt)->format('Y/m/d') : null,
            'start_time' => $startsAt?->format('H:i'),
            'end_date' => $endsAt ? Jalalian::fromCarbon($endsAt)->format('Y/m/d') : null,
            'end_time' => $endsAt?->format('H:i'),
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_jalali' => $this->created_at ? Jalalian::fromCarbon($this->created_at)->format('Y/m/d') : null,
            'views_count' => $this->when(isset($this->views_count), $this->views_count, fn () => $this->views()->count()),
            'likes_count' => $this->when(isset($this->likes_count), $this->likes_count, fn () => $this->interactions()->where('liked', 1)->count()),
            'dislikes_count' => $this->when(isset($this->dislikes_count), $this->dislikes_count, fn () => $this->interactions()->where('liked', 0)->count()),
        ];
    }
}



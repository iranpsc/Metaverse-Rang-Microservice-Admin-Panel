<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'department' => $this->department,
            'importance' => $this->importance,
            'attachment' => $this->attachment,
            'responser_name' => $this->responser_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'priority_title' => $this->getPriorityTitle(),
            'status_label' => $this->getStatusLabel(),
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'email' => $this->sender->email,
                'phone' => $this->sender->phone,
            ],
            'responses' => TicketResponseResource::collection($this->whenLoaded('responses')),
        ];
    }

    /**
     * Get status label
     */
    private function getStatusLabel()
    {
        return match($this->status) {
            0 => 'جدید',
            1 => 'پاسخ داده شده',
            3 => 'درحال بررسی',
            4 => 'بسته شده',
            default => 'نامشخص'
        };
    }
}


<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResponseResource extends JsonResource
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
            'response' => $this->response,
            'attachment' => $this->attachment,
            'responser_name' => $this->responser_name,
            'responser_id' => $this->responser_id,
            'created_at' => jdate($this->created_at)->format('Y/m/d H:i:s'),
        ];
    }
}


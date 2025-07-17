<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'message' => $this->message,
            'read' => $this->read,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'from_user' => [
                'id' => $this->fromUser->id,
                'name' => $this->fromUser->name,
                'email' => $this->fromUser->email,
            ],
            'to_user' => [
                'id' => $this->toUser->id,
                'name' => $this->toUser->name,
                'email' => $this->toUser->email,
            ],
            'lost_item' => [
                'id' => $this->lostItem->id,
                'title' => $this->lostItem->title,
                'location' => $this->lostItem->location,
            ],
        ];
    }
}

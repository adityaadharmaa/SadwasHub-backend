<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'photo_url' => $this->photo_path ? asset('storage/' . $this->photo_path) : null,
            'admin_note' => $this->admin_note,
            'room' => new RoomResource($this->whenLoaded('room')),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at_humans' => $this->created_at->diffForHumans(),
            'created_at' => $this->created_at->toIso8601String()
        ];
    }
}

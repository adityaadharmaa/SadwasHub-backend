<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'room_number' => $this->room_number,
            'status'      => $this->status, // available, occupied, maintenance

            // Menampilkan label status yang lebih user-friendly (opsional)
            'status_label' => ucfirst($this->status),

            // Load data tipe kamar jika dipanggil via eager loading
            'type'        => new RoomTypeResource($this->whenLoaded('roomType')),

            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at_human' => $this->created_at->diffFormHumans()
        ];
    }
}

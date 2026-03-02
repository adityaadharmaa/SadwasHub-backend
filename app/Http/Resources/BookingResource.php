<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'user' => new UserResource($this->whenLoaded('user')),
            'room' => new RoomResource($this->whenLoaded('room')),
            'rent_type' => $this->rent_type, // <--- TAMBAHKAN INI
            'check_in_date' => $this->check_in_date,
            'check_out_date' => $this->check_out_date,
            'total_amount' => (float) $this->total_amount,
            'discount_amount' => (float) $this->discount_amount,
            'status' => $this->status, 
            'notes' => $this->notes, // <--- TAMBAHKAN INI
            'payments' => new PaymentResource($this->whenLoaded('payments')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}

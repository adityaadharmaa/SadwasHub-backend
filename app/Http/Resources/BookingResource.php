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
            'room' => new RoomResource($this->whenLoaded('room')),
            'check_in' => $this->check_in_date,
            'check_out' => $this->check_out_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'reason' => $this->when($this->status === 'cancelled', $this->reason),
            'total_amount' => (float)$this->total_amount,
            'payment' => new PaymentResource($this->whenLoaded('payment'))
        ];
    }
}

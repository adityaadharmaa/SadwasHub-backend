<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'external_id' => $this->external_id,
            'method' => $this->payment_method,
            'amount' => (float) $this->amount,
            'status' =>  $this->status,
            'checkout_url' =>  $this->checkout_url,
            'paid_at' =>  $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}

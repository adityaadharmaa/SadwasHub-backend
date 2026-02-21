<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $today = now()->toDateString();

        return [
            'id' => $this->id,
            'code' => $this->code,
            'reward_amount' => (float) $this->reward_amount,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'limit' => $this->limit,
            'is_active' => $today >= $startDate->toDateString() && $today <= $endDate->toDateString(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class RoomTypeResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug ?? Str::slug($this->name),
            
            // Tambahkan ketiga tipe harga
            'price_per_day' => (float) $this->price_per_day,
            'price_per_week' => (float) $this->price_per_week,
            'price_per_month' => (float) $this->price_per_month,
            
            'facilities' => FacilityResource::collection($this->whenLoaded('facilities')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}

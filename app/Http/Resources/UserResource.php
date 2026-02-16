<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'roles' => $this->getRoleNames()->first(),
            'profile' => new UserProfileResource($this->whenLoaded('profile')),
            'creted_at_human' => $this->created_at->diffForHumans(),
            'creted_at' => $this->created_at->toDateTimeString(),
        ];
    }
}

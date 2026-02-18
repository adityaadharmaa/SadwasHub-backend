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
        $profile = $this->relationLoaded('profile') ? $this->profile : null;
        return [
            'id' => $this->id,
            'email' => $this->email,
            'roles' => $this->getRoleNames()->first(),

            'verification' => [
                'is_verified' => $profile ? (bool) $profile->is_verified : false,
                'status' => $this->getVerificationStatus($profile),
                'admin_note' => $profile ? $profile->admin_note : null
            ],
            'profile' => new UserProfileResource($this->whenLoaded('profile')),
            'created_at_human' => $this->created_at->diffForHumans(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }

    private function getVerificationStatus($profile): string
    {
        if (!$profile || (!$profile->nik && !$profile->ktp_path)) {
            return 'uncompleted';
        }

        if ($profile->is_verified) {
            return 'verified';
        }

        if ($profile->admin_note) {
            return 'rejected';
        }

        return 'pending';
    }
}

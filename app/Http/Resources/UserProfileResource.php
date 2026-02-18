<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserProfileResource extends JsonResource
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
            'full_name' => $this->full_name,
            'nik' => $this->nik,
            'phone_number' => $this->phone_number,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'address' => $this->address,
            'occupation' => $this->occupation,
            'emergency_contact' => [
                'name' => $this->emergency_contact_name,
                'phone' => $this->emergency_contact_phone,
            ],
            'ktp_url' => $this->ktp_path ? url("/api/v1/profile/ktp/" . basename($this->ktp_path)) : null,

            'is_verified' => (bool) $this->is_verified,
        ];
    }

    public function getKtpUrl($path)
    {
        if (!$path || $path === '-') return null;
        if (str_starts_with($path, 'http')) return $path;
        return Storage::url($path);
    }
}

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
        $ktpAttachment = $this->whenLoaded('attachments')
        ? $this->attachments->where('file_type', 'ktp')->first()
        : null;
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'nickname' => $this->nickname,
            'phone_number' => $this->phone_number,
            'nik' => $this->nik,
            'address' => $this->address,
            'ktp_url' => $ktpAttachment ? $ktpAttachment->file_path : null,
            'is_verified' => $this->is_verified,
            'admin_note' => $this->admin_note,
            
            // --- TAMBAHKAN 5 BARIS INI ---
            'gender' => $this->gender,
            'birth_date' => $this->birth_date ? $this->birth_date->format('Y-m-d') : null,
            'occupation' => $this->occupation,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,

            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    public function getKtpUrl($path)
    {
        if (!$path || $path === '-') return null;
        if (str_starts_with($path, 'http')) return $path;
        return Storage::url($path);
    }
}

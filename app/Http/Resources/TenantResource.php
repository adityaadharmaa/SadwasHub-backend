<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Logika Status KTP Murni dari Profile
        $status = 'unverified';
        if ($this->profile) {
            if ($this->profile->is_verified) {
                $status = 'verified';
            } elseif (!empty($this->profile->ktp_path)) {
                $status = 'pending';
            }
        }

        return [
            'id'    => $this->id,
            'name'  => $this->profile?->full_name ?? 'Belum Mengisi Profil',
            'email' => $this->email,
            'phone' => $this->profile?->phone_number ?? null,
            'verification_status' => $status,

            'profile' => [
                'id'          => $this->profile?->id,
                'nik'         => $this->profile?->nik,
                'address'     => $this->profile?->address,
                // Gunakan Helper Function untuk URL KTP (Sama seperti UserProfileResource Anda)
                'ktp_url'     => $this->getKtpUrl($this->profile?->ktp_path),
                'admin_note'  => $this->profile?->admin_note,
            ],

            'joined_human' => $this->created_at?->diffForHumans() ?? 'Baru saja'
        ];
    }

    // Copy Paste Fungsi getKtpUrl milik Anda agar path-nya sesuai
    private function getKtpUrl($path)
    {
        if (!$path || $path === '-') return null;
        if (str_starts_with($path, 'http')) return $path;

        // Ekstrak nama filenya saja (contoh: ktp-123.jpg)
        $filename = basename($path);

        // PERBAIKAN: Kembalikan path relatifnya saja, TANPA fungsi url()
        return "/profile/ktp/" . $filename;
    }
}

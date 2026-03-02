<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $isAdmin = $user->hasRole('admin'); 
        $hasKtp = $user->profile && $user->profile->ktp_path;

        // 1. ATURAN DASAR (Wajib untuk Admin & Tenant)
        $rules = [
            'full_name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:15',
            'nickname' => 'nullable|string|max:50',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date|before:today',
        ];

        // 2. ATURAN KHUSUS (Berbeda antara Admin dan Tenant)
        if ($isAdmin) {
            // Jika ADMIN: Semua dokumen dan kontak darurat TIDAK WAJIB (nullable)
            $rules['nik'] = ['nullable', 'digits:16', Rule::unique('user_profiles', 'nik')->ignore($user->id, 'user_id')];
            $rules['address'] = 'nullable|string|max:500';
            $rules['occupation'] = 'nullable|string|max:100';
            $rules['emergency_contact_name'] = 'nullable|string|max:100';
            $rules['emergency_contact_phone'] = 'nullable|string|max:15';
            $rules['ktp_image'] = 'nullable|image|mimes:jpeg,png,jpg|max:2048';
        } else {
            // Jika TENANT: Dokumen dan kontak darurat WAJIB (required)
            $rules['nik'] = ['required', 'digits:16', Rule::unique('user_profiles', 'nik')->ignore($user->id, 'user_id')];
            $rules['address'] = 'required|string|max:500';
            $rules['occupation'] = 'required|string|max:100';
            $rules['emergency_contact_name'] = 'required|string|max:100';
            $rules['emergency_contact_phone'] = 'required|string|max:15';

            // KTP Wajib jika belum pernah upload
            $rules['ktp_image'] = $hasKtp 
                ? 'nullable|image|mimes:jpeg,png,jpg|max:2048' 
                : 'required|image|mimes:jpeg,png,jpg|max:2048';
        }

        return $rules;
    }

    public function messages(): array {
        return [
            'ktp_image.required' => 'Foto KTP wajib diunggah untuk keperluan verifikasi.',
            'nik.unique' => 'NIK ini sudah terdaftar pada akun lain.'
        ];
    }
}

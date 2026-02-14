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
        return [
            'full_name' => 'required|string|max:100',
            // NIK harus 16 digit dan unik (kecuali milik user sendiri)
            'nik' => [
                'required',
                'digits:16',
                Rule::unique('user_profiles', 'nik')->ignore($this->user()->id, 'user_id')
            ],
            'phone_number' => 'required|string|max:15',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date|before:today',
            'address' => 'required|string|max:500',
            'occupation' => 'required|string|max:100',

            // Kontak Darurat (Wajib)
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_phone' => 'required|string|max:15',

            // Upload KTP (Hanya gambar, max 2MB)
            'ktp_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}

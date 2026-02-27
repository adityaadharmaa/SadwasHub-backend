<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
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
        $roomId = $this->route('room');

        return [
            'room_type_id' => 'sometimes|uuid|exists:room_types,id',
            // Pengecualian unique ID agar tidak error saat update tanpa ganti nomor kamar
            'room_number' => 'sometimes|string|max:20|unique:rooms,room_number,' . $roomId,
            'status' => 'sometimes|in:available,occupied,maintenance',

            'images'   => 'nullable|array',
            // Validasi file: harus berupa gambar, maksimal 5MB (karena nanti kita kompres)
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}

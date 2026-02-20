<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
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
            'room_type_id' => 'required|uuid|exists:room_types,id',
            // Nomor kamar harus unik agar tidak ada duplikasi
            'room_number' => 'required|string|max:20|unique:rooms,room_number',
            'status' => 'nullable|in:available,occupied,maintenance',
        ];
    }
}

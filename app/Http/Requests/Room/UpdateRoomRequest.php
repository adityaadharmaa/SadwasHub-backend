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
        return [
            'room_type_id' => 'exists:room_types,id',

            // Validasi unik, tapi abaikan ID kamar yang sedang diedit ini
            'room_number' => [
                'string',
                Rule::unique('rooms', 'room_number')->ignore($this->route('room')),
            ],

            'status' => 'in:available,occupied,maintenance',
        ];
    }
}

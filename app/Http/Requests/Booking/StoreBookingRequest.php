<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'room_id' => 'required|uuid|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'duration_months' => 'required|integer|min:1',
            'promo_code' => 'nullable|string|exists:promos,code',
        ];
    }

    /**
     * Custom message agar error lebih mudah dibaca user
     */
    public function messages(): array
    {
        return [
            'check_out_date.after' => 'Tanggal keluar harus setelah tanggal masuk.',
            'room_id.exists' => 'Kamar yang dipilih tidak valid.',
        ];
    }
}

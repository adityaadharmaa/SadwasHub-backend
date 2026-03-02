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
            'rent_type' => 'required|string|in:daily,weekly,monthly', // <--- BARU
            'duration' => 'required|integer|min:1', // <--- UBAH DARI duration_months
            'promo_code' => 'nullable|string|exists:promos,code',
            'notes' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Custom message agar error lebih mudah dibaca user
     */
    public function messages(): array
    {
        return [
            'check_in_date.after_or_equal' => 'Tanggal check-in minimal adalah hari ini.',
            'room_id.exists' => 'Kamar yang dipilih tidak valid.',
            'rent_type.in' => 'Tipe sewa tidak valid.',
        ];
    }
}

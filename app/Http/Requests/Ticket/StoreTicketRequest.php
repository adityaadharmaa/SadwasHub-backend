<?php

namespace App\Http\Requests\Ticket;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends BaseRequest
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
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'photo'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }

     public function messages(): array
    {
        return [
            'title.required'       => 'Judul masalah wajib diisi.',
            'description.required' => 'Deskripsi masalah wajib diisi.',
            'priority.in'          => 'Prioritas tidak valid.',
            'photo.mimes'          => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photo.max'            => 'Ukuran foto maksimal 5MB.',
            'photo.file'           => 'Upload yang dikirim harus berupa file.',
        ];
    }
}

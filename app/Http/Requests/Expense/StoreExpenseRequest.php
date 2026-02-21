<?php

namespace App\Http\Requests\Expense;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'category' => 'required|in:operational,maintenance,salary,tax,other',
            'room_id' => 'nullable|uuid|exists:rooms,id',

            // Validasi File Nota/Struk
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ];
    }
}

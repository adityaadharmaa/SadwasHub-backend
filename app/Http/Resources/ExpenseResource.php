<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,

            // Konversi ke float agar di frontend terbaca sebagai angka, bukan teks
            'amount' => (float) $this->amount,

            'expense_date' => $this->expense_date,
            'category' => $this->category,
            'room_id' => $this->room_id,

            // Tampilkan data kamar jika relasinya di-load (Opsional tapi sangat berguna)
            'room' => $this->whenLoaded('room', function () {
                return [
                    'id' => $this->room->id,
                    'room_number' => $this->room->room_number,
                ];
            }),

            // Format data struk/nota menjadi URL yang siap pakai untuk frontend
            'attachments' => $this->whenLoaded('attachments', function () {
                return $this->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        // Gunakan fungsi asset() untuk menghasilkan full URL (http://localhost:8000/storage/...)
                        'file_url' => asset('storage/' . $attachment->file_path),
                        'file_type' => $attachment->file_type,
                    ];
                });
            }),

            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'type', // 'percentage' atau 'fixed'
        'reward_amount',
        'start_date',
        'end_date',
        'limit', // Kuota promo
        'used_count' // Counter berapa kali dipakai
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'reward_amount' => 'decimal:2',
        'limit' => 'integer',
        'used_count' => 'integer'
    ];


    /**
     * Cek apakah promo masih valid
     */
    public function isValid(): bool
    {
        $now = now();

        // Cek tanggal
        if ($now->lt($this->start_date) || $now->gt($this->end_date)) {
            return false;
        }

        // Cek kuota (jika ada limit)
        if (!is_null($this->limit) && $this->used_count >= $this->limit) {
            return false;
        }

        return true;
    }
}

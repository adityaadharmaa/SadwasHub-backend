<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'full_name',
        'nik',
        'phone_number',
        'gender',
        'birth_date',
        'address',
        'occupation',
        'emergency_contact_name',
        'emergency_contact_phone',
        'ktp_path',
        'is_verified',
        'admin_note',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'birth_date' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

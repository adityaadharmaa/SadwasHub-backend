<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class UserProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'full_name',
        'nickname',
        'phone_number',
        'nik',
        'address',
        'ktp_path',
        'is_verified',
        'admin_note',
        // Tambahkan 5 baris ini agar bisa disimpan ke database:
        'gender',
        'birth_date',
        'occupation',
        'emergency_contact_name',
        'emergency_contact_phone'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'birth_date' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}

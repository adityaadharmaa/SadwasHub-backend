<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'description', 'price_per_month'];

    protected $casts = [
        'price_per_month' => 'decimal:2'
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(
            Facilitie::class,
            'room_type_facilities',
            'room_type_id',
            'facility_id'
        )
            ->using(RoomTypeFacility::class)
            ->withPivot('id')
            ->withTimestamps();
    }
}

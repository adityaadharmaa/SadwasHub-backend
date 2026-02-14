<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facilitie extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['name', 'icon'];

    public function RoomTypes(): BelongsToMany
    {
        return $this->belongsToMany(RoomType::class)->using(RoomTypeFacility::class);
    }
}

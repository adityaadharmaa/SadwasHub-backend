<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RoomTypeFacility extends Pivot
{
    use HasUuids;

    protected $table = 'room_type_facilities';
}

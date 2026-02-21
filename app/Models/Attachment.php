<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'file_path',
        'file_type',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}

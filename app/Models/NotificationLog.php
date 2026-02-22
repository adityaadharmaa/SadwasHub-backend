<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasUuids;

    protected $table = 'notification_logs';

    protected $fillable = ['user_id', 'category', 'channel', 'title', 'message', 'payload', 'delivery_status', 'error_log', 'read_at'];

    protected $casts = ['patload' => 'array', 'read_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

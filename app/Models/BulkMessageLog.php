<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkMessageLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'bulk_send_id',
        'channel',
        'user_id',
        'message_id',
        'status',
        'error',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

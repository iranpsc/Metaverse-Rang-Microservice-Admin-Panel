<?php

namespace App\Models\Dynasty;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivedPrize extends Model
{
    use HasFactory;

    protected $table = 'received_prizes';

    protected $fillable = [
        'user_id',
        'prize_id',
        'message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(DynastyPrize::class, 'prize_id');
    }
}

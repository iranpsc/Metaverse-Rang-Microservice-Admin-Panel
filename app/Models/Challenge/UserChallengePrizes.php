<?php

namespace App\Models\Challenge;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserChallengePrizes extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_prize_id'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function questionPrize(): BelongsTo
    {
        return $this->belongsTo(QuestionPrize::class);
    }
}

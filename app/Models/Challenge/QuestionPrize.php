<?php

namespace App\Models\Challenge;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionPrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'prize_id',
        'amount',
    ];

    /**
     * @return HasMany
     */
    public function userChallengePrizes(): HasMany
    {
        return $this->hasMany(UserChallengePrizes::class);
    }

    /**
     * @return BelongsTo
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }


    /**
     * @return BelongsTo
     */
    public function challengePrizeList(): BelongsTo
    {
        return $this->belongsTo(challengePrizesList::class);
    }
}

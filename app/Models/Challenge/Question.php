<?php

namespace App\Models\Challenge;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
    ];

    /**
     * @return HasMany
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class);
    }

    /**
     * @return HasOne
     */
    public function correctAnswer(): HasOne
    {
        return $this->hasOne(CorrectAnswer::class);
    }

    /**
     * @return HasMany
     */
    public function questionPrizes(): HasMany
    {
        return $this->hasMany(QuestionPrize::class);
    }

    /**
     * @return BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * @return HasMany
     */
    public function userQuestionAnswer(): HasMany
    {
        return $this->hasMany(UserQuestionAnswer::class);
    }
}

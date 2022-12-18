<?php

namespace App\Models\Challenge;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class QuestionAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'answer'
    ];

    /**
     * @return BelongsTo
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return HasOne
     */
    public function correctAnswer(): HasOne
    {
        return $this->hasOne(CorrectAnswer::class);
    }

    public function userQuestionAnswer(): HasMany
    {
        return $this->hasMany(UserQuestionAnswer::class);
    }

    /**
     * @return MorphOne
     */
    public function image()
    {
        return $this->morphOne(Image::class , 'imageable');
    }
}

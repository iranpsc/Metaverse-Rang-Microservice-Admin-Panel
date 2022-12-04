<?php

namespace App\Models\Challenge;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'question_answer_id'
    ];

    /**
     * @return BelongsTo
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(QuestionAnswer::class);
    }
}

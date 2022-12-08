<?php

namespace App\Models\Challenge;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionFile extends Model
{
    protected $fillable = [
        'question_code',
        'question_image',
        'question',
        'answer_one_image',
        'answer_one',
        'answer_two_image',
        'answer_two',
        'answer_three_image',
        'answer_three',
        'answer_four_image',
        'correct_answer',
        'answer_four',
    ];

    use HasFactory;

}

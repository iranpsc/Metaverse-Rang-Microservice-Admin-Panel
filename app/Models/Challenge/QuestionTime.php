<?php

namespace App\Models\Challenge;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value'
    ];
}

<?php

namespace App\Models\Challenge;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengePrizesList extends Model
{
    use HasFactory;

    protected $table = 'challenge_prizes_lists';

    protected $guarded = [];
}

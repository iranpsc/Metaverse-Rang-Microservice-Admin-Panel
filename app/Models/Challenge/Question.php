<?php

namespace App\Models\Challenge;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'creator_code',
        'prize',
        'image'
    ];

    /**
     * @return HasMany
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}

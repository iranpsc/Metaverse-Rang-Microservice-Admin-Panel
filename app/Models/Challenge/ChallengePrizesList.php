<?php

namespace App\Models\Challenge;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class challengePrizesList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    /**
     * @return HasMany
     */
    public function questionPrize(): HasMany
    {
        return $this->hasMany(QuestionPrize::class);
    }
}

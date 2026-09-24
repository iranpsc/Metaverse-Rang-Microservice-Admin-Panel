<?php

namespace App\Models\Dynasty;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DynastyPrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'member',
        'satisfaction',
        'introduction_profit_increase',
        'accumulated_capital_reserve',
        'data_storage',
        'psc',
    ];

    public function receivedPrizes(): HasMany
    {
        return $this->hasMany(ReceivedPrize::class, 'prize_id');
    }

    public function getRelationTitle()
    {
        return match ($this->member) {
            'father' => 'پدر',
            'mother' => 'مادر',
            'life_partner' => 'همسر',
            'brother' => 'برادر',
            'sister' => 'خواهر',
            'offspring' => 'فرزند',
            'wife' => 'زن',
            'husband' => 'شوهر',
        };
    }
}

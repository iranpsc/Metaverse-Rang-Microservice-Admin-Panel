<?php

namespace App\Models\Dynasty;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}

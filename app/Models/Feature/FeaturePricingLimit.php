<?php

namespace App\Models\Feature;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturePricingLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_price_limit',
        'under_eighteen_price_limit'
    ];
}

<?php

namespace App\Models\Level;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelGift extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'monthly_capacity_count' => 'integer',
        'store_capacity' => 'boolean',
        'sell_capacity' => 'boolean',
        'sell' => 'boolean',
        'vod_document_registration' => 'boolean',
        'three_d_model_volume' => 'decimal:4',
        'three_d_model_points' => 'integer',
        'three_d_model_lines' => 'integer',
        'has_animation' => 'boolean',
        'rent' => 'boolean',
        'vod_count' => 'integer',
    ];
}

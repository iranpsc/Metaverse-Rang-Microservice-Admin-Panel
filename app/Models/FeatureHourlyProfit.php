<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureHourlyProfit extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'feature_id',
        'asset',
        'amount',
        'dead_line',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'int',
            'feature_id' => 'int',
            'asset' => 'string',
            'amount' => 'float',
            'dead_line' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The attributes that should be set to default.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
        'amount' => 0,
    ];

    /**
     * Get the user that owns the feature hourly profit.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the feature that owns the feature hourly profit.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}

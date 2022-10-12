<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LockedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_id',
        'buy_feature_request_id',
        'user_id',
        'asset',
        'amount',
        'status',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function assetable() {
        return $this->morphTo();
    }
}

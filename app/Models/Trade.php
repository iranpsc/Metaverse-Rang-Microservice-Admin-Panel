<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comission;

class Trade extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_id',
        'buyer_id',
        'seller_id',
        'date',
    ];

    public function feature() {
        return $this->belongsTo(Feature::class);
    }

    public function buyer() {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    public function seller() {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    public function commision() {
        return $this->hasOne(comission::class);
    }
}

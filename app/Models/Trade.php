<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    use HasFactory;

    protected $table = 'trades';

    protected $fillable = [
        'feature_id',
        'buyer_id',
        'seller_id',
        'psc_amount',
        'irr_amount',
        'date',
        'type',
    ];

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    public function commision()
    {
        return $this->hasOne(Comission::class);
    }
}

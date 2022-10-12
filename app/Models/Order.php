<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset', 'amount', 'user_id', 'status',
    ];

    public function transactions() {
	    return $this->morphOne(Transaction::class , 'payable');
    }
}

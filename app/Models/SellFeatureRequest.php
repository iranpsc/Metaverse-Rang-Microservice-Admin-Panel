<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellFeatureRequest extends Model
{
    use HasFactory;

    protected $casts = [
		'seller_id' => 'int',
		'feature_id' => 'int',
		'buyer_id' => 'int'
	];

	protected $fillable = [
		'seller_id',
		'buyer_id',
		'feature_id',
		'status',
		'note',
		'price_psc',
        'price_irr',
	];

	public  function seller() {
	    return $this->belongsTo(User::class , 'seller_id');
    }

    public function buyer() {
	    return $this->belongsTo(User::class);
    }

    public function feature() {
	    return $this->belongsTo(Feature::class);
    }
}

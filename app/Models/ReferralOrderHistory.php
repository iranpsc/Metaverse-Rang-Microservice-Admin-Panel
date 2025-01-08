<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralOrderHistory extends Model
{
	protected $casts = [
		'user_id' => 'int',
		'referral_id' => 'int',
		'amount' => 'float'
	];

	protected $fillable = [
		'user_id',
		'referral_id',
		'amount'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function referral(){
        return $this->belongsTo(User::class);

    }
}

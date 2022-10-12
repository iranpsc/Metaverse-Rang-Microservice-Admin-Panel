<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ReferralOrderHistory
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $referral_id
 * @property float|null $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 * @package App\Models
 * @property-read \App\Models\User|null $referral
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralOrderHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralOrderHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralOrderHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralOrderHistory whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralOrderHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralOrderHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralOrderHistory whereReferralId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralOrderHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralOrderHistory whereUserId($value)
 * @mixin \Eloquent
 */
class ReferralOrderHistory extends Model
{
	protected $table = 'referal_order_histories';

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

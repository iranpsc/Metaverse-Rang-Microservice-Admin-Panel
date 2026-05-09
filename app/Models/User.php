<?php

namespace App\Models;

use App\Models\Challenge\UserChallengePrizes;
use App\Models\Challenge\UserQuestionAnswer;
use App\Models\Level\UserLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\SellFeatureRequests;
use App\Models\Note;
use App\Models\User\UserActivity;
use App\Models\Wallet;

class User extends Authenticatable
{
    use Notifiable, HasFactory, HasApiTokens;

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'ip',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function getHasReferenceAttribute()
    {
        return !empty($this->referrer()->first());
    }

    public function ownField(Feature $feature)
    {
        return ($feature->owner_id == $this->id);
    }

    public function features()
    {
        return $this->hasMany(Feature::class, 'owner_id');
    }

    public function sellRequests()
    {
        return $this->hasMany(SellFeatureRequests::class, 'seller_id');
    }

    public function buyRequests()
    {
        return $this->hasMany(BuyFeatureRequest::class, 'buyer_id');
    }

    public function recievedBuyRequests()
    {
        return $this->hasMany(BuyFeatureRequest::class, 'seller_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function referralOrders()
    {
        return $this->hasMany(ReferralOrderHistory::class);
    }

    public function firstOrder()
    {
        return $this->hasOne(FirstOrder::class);
    }

    public function lockedAssets()
    {
        return $this->morphMany(LockedAsset::class, 'assetable');
    }

    /**
     * @return BelongsToMany
     */
    public function followers()
    {
        return $this->belongsToMany(__CLASS__, 'follows', 'following_id', 'follower_id');
    }

    /**
     * @return BelongsToMany
     */
    public function following()
    {
        return $this->belongsToMany(__CLASS__, 'follows', 'follower_id', 'following_id');
    }

    public function tickets() {
        return $this->hasMany(Ticket::class);
    }

    public function recievedTickets() {
        return $this->hasMany(Ticket::class, 'reciever_id');
    }

    public function notes() {
        return $this->hasMany(Note::class);
    }

    public function kyc() {
        return $this->hasOne(Kyc::class);
    }

    public function settings() {
        return $this->hasOne(Setting::class);
    }

    public function generalSettings() {
        return $this->hasOne(GeneralSetting::class);
    }

    public function log() {
        return $this->hasOne(UserLog::class);
    }

    public function activities() {
        return $this->hasMany(UserActivity::class);
    }


    /**
     * @return HasMany
     */
    public function userChallengePrizes(): HasMany
    {
        return $this->hasMany(UserChallengePrizes::class);
    }

    /**
     * @return HasMany
     */
    public function userQuestionAnswer(): HasMany
    {
        return $this->hasMany(related: UserQuestionAnswer::class);
    }

    public function bankAccounts()
    {
        return $this->morphMany(Bankaccount::class, 'bankable');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

}

<?php

namespace Tests\Unit\Models;

use App\Models\BuyFeatureRequest;
use App\Models\Feature;
use App\Models\FirstOrder;
use App\Models\GeneralSetting;
use App\Models\Level\Level;
use App\Models\Level\UserLog;
use App\Models\LockedAsset;
use App\Models\Note;
use App\Models\Payment;
use App\Models\ReferralOrderHistory;
use App\Models\SellFeatureRequest;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\User\UserActivity;
use App\Models\Wallet;
use App\Models\Challenge\UserChallengePrizes;
use App\Models\Challenge\UserQuestionAnswer;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_casts_email_verified_at_to_datetime(): void
    {
        $casts = (new User)->getCasts();

        $this->assertSame('datetime', $casts['email_verified_at']);
    }

    public function test_relation_methods_return_expected_relation_types(): void
    {
        $user = new User;

        $this->assertInstanceOf(HasOne::class, $user->wallet());
        $this->assertSame(Wallet::class, $user->wallet()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->features());
        $this->assertSame(Feature::class, $user->features()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->sellRequests());
        $this->assertSame(SellFeatureRequest::class, $user->sellRequests()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->buyRequests());
        $this->assertSame(BuyFeatureRequest::class, $user->buyRequests()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->recievedBuyRequests());
        $this->assertSame(BuyFeatureRequest::class, $user->recievedBuyRequests()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->transactions());
        $this->assertSame(Transaction::class, $user->transactions()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->referralOrders());
        $this->assertSame(ReferralOrderHistory::class, $user->referralOrders()->getRelated()::class);

        $this->assertInstanceOf(HasOne::class, $user->firstOrder());
        $this->assertSame(FirstOrder::class, $user->firstOrder()->getRelated()::class);

        $this->assertInstanceOf(MorphMany::class, $user->lockedAssets());
        $this->assertSame(LockedAsset::class, $user->lockedAssets()->getRelated()::class);

        $this->assertInstanceOf(BelongsToMany::class, $user->following());
        $this->assertSame(User::class, $user->following()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->tickets());
        $this->assertSame(Ticket::class, $user->tickets()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->recievedTickets());
        $this->assertSame(Ticket::class, $user->recievedTickets()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->notes());
        $this->assertSame(Note::class, $user->notes()->getRelated()::class);

        $this->assertInstanceOf(HasOne::class, $user->settings());
        $this->assertSame(Setting::class, $user->settings()->getRelated()::class);

        $this->assertInstanceOf(HasOne::class, $user->generalSettings());
        $this->assertSame(GeneralSetting::class, $user->generalSettings()->getRelated()::class);

        $this->assertInstanceOf(HasOne::class, $user->log());
        $this->assertSame(UserLog::class, $user->log()->getRelated()::class);

        $this->assertInstanceOf(BelongsToMany::class, $user->levels());
        $this->assertSame(Level::class, $user->levels()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->activities());
        $this->assertSame(UserActivity::class, $user->activities()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->userChallengePrizes());
        $this->assertSame(UserChallengePrizes::class, $user->userChallengePrizes()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $user->userQuestionAnswer());
        $this->assertSame(UserQuestionAnswer::class, $user->userQuestionAnswer()->getRelated()::class);

        $this->assertInstanceOf(MorphMany::class, $user->bankAccounts());

        $this->assertInstanceOf(HasMany::class, $user->payments());
        $this->assertSame(Payment::class, $user->payments()->getRelated()::class);
    }

    public function test_own_field_compares_feature_owner_id(): void
    {
        $user = new User;
        $user->id = 42;

        $owned = new Feature(['owner_id' => 42]);
        $foreign = new Feature(['owner_id' => 7]);

        $this->assertTrue($user->ownField($owned));
        $this->assertFalse($user->ownField($foreign));
    }
}

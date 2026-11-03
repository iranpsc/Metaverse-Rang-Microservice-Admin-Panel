<?php

namespace Tests\Unit\Coverage;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Resources\DashboardResource;
use App\Models\Admin;
use App\Models\BuyFeatureRequest;
use App\Models\Feature;
use App\Models\Map;
use App\Notifications\SendVerificationCode;
use App\Traits\SendsVerificationSms;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeadCodeCoverageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('activitylog.enabled', false);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createMinimalTables();
    }

    public function test_dashboard_resource_to_array(): void
    {
        $payload = [
            'users' => [
                'all' => 10,
                'verified' => 5,
                'verified_phone' => 4,
                'kyc_verified' => 3,
            ],
            'dynasties' => 2,
            'features' => [
                'all' => 100,
                'sold' => 20,
            ],
            'referrals' => 7,
            'referral_amount' => 1500,
            'sold_assets' => [
                'psc' => 1,
                'red' => 2,
                'blue' => 3,
                'yellow' => 4,
            ],
            'deposited_rial_amount' => 999,
        ];

        $array = (new DashboardResource($payload))->toArray(Request::create('/'));

        $this->assertSame(10, $array['users']['all']);
        $this->assertSame(5, $array['users']['verified']);
        $this->assertSame(2, $array['dynasties']);
        $this->assertSame(100, $array['features']['all']);
        $this->assertSame(20, $array['features']['sold']);
        $this->assertSame(7, $array['referrals']);
        $this->assertSame(1500, $array['referral_amount']);
        $this->assertSame(1, $array['sold_assets']['psc']);
        $this->assertSame(999, $array['deposited_rial_amount']);
    }

    public function test_change_password_request_rules_via_validator(): void
    {
        $request = new ChangePasswordRequest;
        $this->assertTrue($request->authorize());

        $rules = $request->rules();

        $fails = Validator::make([
            'password' => 'short',
            'password_confirmation' => 'short',
        ], $rules);
        $this->assertTrue($fails->fails());

        $passes = Validator::make([
            'password' => 'ValidPass1!',
            'password_confirmation' => 'ValidPass1!',
        ], $rules);
        $this->assertFalse($passes->fails());
    }

    public function test_sends_verification_sms_trait_dispatches_and_notifies(): void
    {
        Notification::fake();

        $admin = Admin::withoutEvents(function () {
            return Admin::create([
                'name' => 'SMS Admin',
                'email' => Str::uuid().'@example.com',
                'password' => Hash::make('password'),
                'phone' => '09123334444',
                'active' => 1,
            ]);
        });

        Auth::guard('admin')->setUser($admin);
        Auth::guard('web')->setUser($admin);
        Auth::shouldUse('web');

        $host = new class
        {
            use SendsVerificationSms;

            /** @var list<array<int, mixed>> */
            public array $dispatched = [];

            public function dispatch(...$args): static
            {
                $this->dispatched[] = $args;

                return $this;
            }
        };

        $host->sendSMS('verify-form');

        $this->assertSame($admin->id, $host->admin->id);
        $this->assertNotEmpty($host->dispatched);
        $this->assertSame('start-countdown', $host->dispatched[0][0]);
        $this->assertSame('notify', $host->dispatched[1][0]);

        Notification::assertSentTo($admin, SendVerificationCode::class);
    }

    public function test_thin_models_instantiate_and_expose_relations(): void
    {
        $admin = new Admin([
            'name' => 'Thin',
            'email' => 'thin@example.com',
            'phone' => '09120000001',
        ]);
        $this->assertSame('admin', $admin->getGuardName());
        $this->assertSame('09120000001', $admin->routeNotificationForKavenegar('kavenegar'));

        $map = new Map(['status' => 1, 'name' => 'Coverage Map']);
        $this->assertTrue($map->isPublished());
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $map->features()
        );

        $buy = new BuyFeatureRequest([
            'seller_id' => 1,
            'buyer_id' => 2,
            'feature_id' => 3,
            'status' => 'pending',
            'price_psc' => 10,
            'price_irr' => 1000,
        ]);
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $buy->seller()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $buy->buyer()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $buy->feature()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\MorphOne::class,
            $buy->transactions()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\MorphOne::class,
            $buy->lockedAssets()
        );

        $feature = new Feature(['map_id' => 1, 'type' => 'Feature', 'owner_id' => 1]);
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $feature->map()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasOne::class,
            $feature->properties()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasOne::class,
            $feature->geometry()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $feature->images()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $feature->owner()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $feature->buyRequests()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\MorphOne::class,
            $feature->lockedAssets()
        );

        $this->assertSame('admin', $admin->getGuardName());
        $admin->setRelation('roles', collect([
            (object) ['title' => 'Super Admin'],
        ]));
        $this->assertSame(['Super Admin'], $admin->getRoleTitles()->all());

        $level = new \App\Models\Level\Level(['score' => '12']);
        $this->assertSame(12, $level->numeric_score);
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $level->users()
        );

        try {
            \App\Models\Level\Level::query()->whereNumericScore('!!', 1);
            $this->fail('Expected invalid operator exception');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('Unsupported operator', $exception->getMessage());
        }

        $crs = new \App\Models\Crs;
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $crs->map()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $crs->crs_properties()
        );

        $locked = new \App\Models\LockedAsset;
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $locked->user()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\MorphTo::class,
            $locked->assetable()
        );

        $prize = new \App\Models\Challenge\QuestionPrize;
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $prize->userChallengePrizes()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $prize->question()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $prize->challengePrizeList()
        );

        $answer = new \App\Models\Challenge\UserQuestionAnswer;
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $answer->user()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $answer->question()
        );
        if (method_exists($answer, 'questionAnswer')) {
            $answer->questionAnswer();
        }

        if (! Schema::hasTable('join_requests')) {
            Schema::create('join_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('status')->default(5);
                $table->timestamps();
            });
        }

        \App\Models\Dynasty\JoinRequest::query()->create(['status' => 5]);
        $this->assertGreaterThanOrEqual(1, \App\Models\Dynasty\JoinRequest::query()->count());
    }

    private function createMinimalTables(): void
    {
        if (! Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('phone')->nullable();
                $table->boolean('active')->default(1);
                $table->string('image')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('maps')) {
            Schema::create('maps', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->tinyInteger('status')->default(0);
                $table->string('fileName')->nullable();
            });
        }

        if (! Schema::hasTable('features')) {
            Schema::create('features', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('map_id')->nullable();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('owner_id')->nullable();
                $table->timestamps();
            });
        }
    }
}

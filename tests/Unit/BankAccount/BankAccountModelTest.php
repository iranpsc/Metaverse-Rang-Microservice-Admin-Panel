<?php

namespace Tests\Unit\BankAccount;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesBankAccountApiSchema;
use Tests\TestCase;

class BankAccountModelTest extends TestCase
{
    use CreatesBankAccountApiSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBankAccountApiSchema();
    }

    // -------------------------------------------------------------------------
    // status_badge accessor
    // -------------------------------------------------------------------------

    public function test_status_badge_returns_pending_for_status_zero(): void
    {
        $bankAccount = BankAccount::make(['status' => 0]);

        $this->assertSame(
            '<span class="badge badge-info">در انتظار بررسی</span>',
            $bankAccount->status_badge
        );
    }

    public function test_status_badge_returns_verified_for_status_one(): void
    {
        $bankAccount = BankAccount::make(['status' => 1]);

        $this->assertSame(
            '<span class="badge badge-success">تایید شده</span>',
            $bankAccount->status_badge
        );
    }

    public function test_status_badge_returns_rejected_for_status_minus_one(): void
    {
        $bankAccount = BankAccount::make(['status' => -1]);

        $this->assertSame(
            '<span class="badge badge-danger">رد شده</span>',
            $bankAccount->status_badge
        );
    }

    public function test_status_badge_returns_unknown_for_unrecognized_status(): void
    {
        $bankAccount = BankAccount::make(['status' => 99]);

        $this->assertSame(
            '<span class="badge badge-warning">نامشخص</span>',
            $bankAccount->status_badge
        );
    }

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    public function test_errors_is_cast_to_array(): void
    {
        $user = $this->createUser();
        $bankAccount = BankAccount::create([
            'bank_name' => 'Test Bank',
            'shaba_num' => 'IR123456789012345678901234',
            'card_num' => '6037991234567890',
            'status' => -1,
            'errors' => ['card_num_err' => 'invalid'],
            'bankable_type' => User::class,
            'bankable_id' => $user->id,
        ]);

        $fresh = $bankAccount->fresh();

        $this->assertIsArray($fresh->errors);
        $this->assertSame(['card_num_err' => 'invalid'], $fresh->errors);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_bankable_relation_is_morph_to(): void
    {
        $bankAccount = new BankAccount;

        $this->assertInstanceOf(MorphTo::class, $bankAccount->bankable());
    }

    public function test_bankable_morph_to_returns_user(): void
    {
        $user = $this->createUser(['name' => 'Bank User']);
        $bankAccount = BankAccount::create([
            'bank_name' => 'Melli',
            'shaba_num' => 'IR111111111111111111111111',
            'card_num' => '6037991111111111',
            'status' => 0,
            'errors' => null,
            'bankable_type' => User::class,
            'bankable_id' => $user->id,
        ]);

        $loaded = BankAccount::with('bankable')->findOrFail($bankAccount->id);

        $this->assertTrue($loaded->bankable->is($user));
        $this->assertSame('Bank User', $loaded->bankable->name);
        $this->assertInstanceOf(User::class, $loaded->bankable);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Citizen '.Str::random(5),
            'email' => Str::uuid().'@example.com',
            'code' => (string) random_int(1000, 9999),
            'password' => 'secret',
            'ip' => '127.0.0.1',
        ], $overrides));
    }
}

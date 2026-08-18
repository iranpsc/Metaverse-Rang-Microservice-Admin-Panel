<?php

namespace Tests\Unit\Kyc;

use App\Models\Kyc;
use App\Models\KycVerifyText;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesKycApiSchema;
use Tests\TestCase;

class KycModelTest extends TestCase
{
    use CreatesKycApiSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpKycApiSchema();
    }

    // -------------------------------------------------------------------------
    // status_badge accessor
    // -------------------------------------------------------------------------

    public function test_status_badge_returns_pending_for_status_zero(): void
    {
        $kyc = Kyc::make(['status' => 0]);

        $this->assertSame(
            '<span class="badge badge-info">در انتظار بررسی</span>',
            $kyc->status_badge
        );
    }

    public function test_status_badge_returns_verified_for_status_one(): void
    {
        $kyc = Kyc::make(['status' => 1]);

        $this->assertSame(
            '<span class="badge badge-success">تایید شده</span>',
            $kyc->status_badge
        );
    }

    public function test_status_badge_returns_rejected_for_status_minus_one(): void
    {
        $kyc = Kyc::make(['status' => -1]);

        $this->assertSame(
            '<span class="badge badge-danger">رد شده</span>',
            $kyc->status_badge
        );
    }

    public function test_status_badge_returns_unknown_for_unrecognized_status(): void
    {
        $kyc = Kyc::make(['status' => 99]);

        $this->assertSame(
            '<span class="badge badge-warning">نامشخص</span>',
            $kyc->status_badge
        );
    }

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    public function test_errors_is_cast_to_array(): void
    {
        $user = $this->createUser();
        $kyc = Kyc::create([
            'user_id' => $user->id,
            'fname' => 'Test',
            'lname' => 'User',
            'melli_code' => '1234567890',
            'status' => -1,
            'errors' => ['video' => 'invalid'],
        ]);

        $fresh = $kyc->fresh();

        $this->assertIsArray($fresh->errors);
        $this->assertSame(['video' => 'invalid'], $fresh->errors);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function test_user_relation_is_belongs_to(): void
    {
        $kyc = new Kyc;

        $this->assertInstanceOf(BelongsTo::class, $kyc->user());
        $this->assertSame(User::class, $kyc->user()->getRelated()::class);
    }

    public function test_verify_text_relation_is_has_one(): void
    {
        $kyc = new Kyc;

        $this->assertInstanceOf(HasOne::class, $kyc->verifyText());
        $this->assertSame(KycVerifyText::class, $kyc->verifyText()->getRelated()::class);
    }

    public function test_user_and_verify_text_relations_can_be_loaded(): void
    {
        $user = $this->createUser(['name' => 'Linked User', 'code' => '8888']);
        $verifyText = KycVerifyText::create(['text' => 'Say this line']);

        $kyc = Kyc::create([
            'user_id' => $user->id,
            'fname' => 'Linked',
            'lname' => 'Kyc',
            'melli_code' => '9876543210',
            'status' => 0,
            'verify_text_id' => $verifyText->id,
        ]);

        $loaded = Kyc::with(['user', 'verifyText'])->findOrFail($kyc->id);

        $this->assertTrue($loaded->user->is($user));
        $this->assertSame('Linked User', $loaded->user->name);
        $this->assertTrue($loaded->verifyText->is($verifyText));
        $this->assertSame('Say this line', $loaded->verifyText->text);
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

<?php

namespace Tests\Unit\Verification;

use App\Rules\IsValidVerifyCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesVerificationApiSchema;
use Tests\TestCase;

class IsValidVerifyCodeRuleTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesVerificationApiSchema;

    private IsValidVerifyCode $rule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpVerificationApiSchema();
        $this->rule = new IsValidVerifyCode;
    }

    public function test_passes_with_correct_code_in_cache(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $this->seedVerificationCode($admin, '123456');

        $this->assertTrue($this->rule->passes('phone_verification', '123456'));
        $this->assertTrue($this->rule->passes('phone_verification', 123456));
    }

    public function test_fails_with_wrong_code(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $this->seedVerificationCode($admin, '123456');

        $this->assertFalse($this->rule->passes('phone_verification', '654321'));
    }

    public function test_fails_when_no_cache_entry_exists(): void
    {
        $this->actingAsSuperAdmin();

        $this->assertFalse($this->rule->passes('phone_verification', '123456'));
    }

    public function test_fails_when_admin_is_not_authenticated(): void
    {
        Cache::put(
            'verify.code.1',
            Hash::make('123456'),
            now()->addMinutes(1)
        );

        Auth::guard('admin')->logout();

        $this->assertFalse($this->rule->passes('phone_verification', '123456'));
    }

    public function test_message_returns_persian_validation_error(): void
    {
        $this->assertSame('کد تایید صحیح نیست!', $this->rule->message());
    }
}

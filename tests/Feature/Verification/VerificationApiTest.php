<?php

namespace Tests\Feature\Verification;

use App\Notifications\SendVerificationCode;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesVerificationApiSchema;
use Tests\TestCase;

class VerificationApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesVerificationApiSchema;

    private const SEND_SMS_PATH = '/api/send-verification-sms';

    private const VERIFY_PATH = '/api/verify-verification-sms';

    private const STATUS_PATH = '/api/phone-verification/status';

    private const CONFIRM_PATH = '/api/phone-verification/confirm';

    private const SEND_SUCCESS_MESSAGE = 'کد تایید با موفقیت ارسال گردید';

    private const VERIFY_SUCCESS_MESSAGE = 'کد تایید با موفقیت تایید شد';

    private const CONFIRM_SUCCESS_MESSAGE = 'جلسه تایید شماره موبایل با موفقیت تمدید شد.';

    private const COOLDOWN_MESSAGE = 'لطفاً قبل از درخواست مجدد کمی صبر کنید.';

    private const SEND_ERROR_MESSAGE = 'خطا در ارسال کد تایید';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpVerificationApiSchema();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_send_sms_returns_unauthorized(): void
    {
        $this->postJson(self::SEND_SMS_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_verify_returns_unauthorized(): void
    {
        $this->postJson(self::VERIFY_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_status_returns_unauthorized(): void
    {
        $this->getJson(self::STATUS_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_confirm_returns_unauthorized(): void
    {
        $this->postJson(self::CONFIRM_PATH)->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // sendSMS
    // -------------------------------------------------------------------------

    public function test_authenticated_admin_can_send_sms_successfully(): void
    {
        Notification::fake();

        $admin = $this->actingAsSuperAdmin();
        $cooldownSeconds = (int) config('phone_verification.sms_resend_cooldown_seconds');

        $this->postJson(self::SEND_SMS_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SEND_SUCCESS_MESSAGE)
            ->assertJsonPath('data.resend_available_in', $cooldownSeconds);

        Notification::assertSentTo($admin, SendVerificationCode::class);

        $cooldownKey = 'verify.sms.cooldown.'.$admin->id;
        $cooldownUntil = Cache::get($cooldownKey);

        $this->assertIsInt($cooldownUntil);
        $this->assertGreaterThan(now()->timestamp, $cooldownUntil);
    }

    public function test_send_sms_cooldown_prevents_resend_with_remaining_seconds(): void
    {
        Notification::fake();

        $admin = $this->actingAsSuperAdmin();
        $remainingSeconds = 45;

        $this->putSmsCooldown($admin, $remainingSeconds);

        $this->postJson(self::SEND_SMS_PATH)
            ->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::COOLDOWN_MESSAGE)
            ->assertJsonPath('data.resend_available_in', $remainingSeconds);

        Notification::assertNothingSent();
    }

    public function test_send_sms_allows_resend_after_cooldown_expires(): void
    {
        Notification::fake();

        $admin = $this->actingAsSuperAdmin();
        $cooldownSeconds = (int) config('phone_verification.sms_resend_cooldown_seconds');

        $this->postJson(self::SEND_SMS_PATH)->assertOk();

        Notification::assertSentTo($admin, SendVerificationCode::class);
        Notification::fake();

        Carbon::setTestNow(now()->addSeconds($cooldownSeconds + 1));

        $this->postJson(self::SEND_SMS_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SEND_SUCCESS_MESSAGE)
            ->assertJsonPath('data.resend_available_in', $cooldownSeconds);

        Notification::assertSentTo($admin, SendVerificationCode::class);
    }

    public function test_send_sms_uses_config_cooldown_value_in_response(): void
    {
        Notification::fake();

        config()->set('phone_verification.sms_resend_cooldown_seconds', 90);

        $this->actingAsSuperAdmin();

        $this->postJson(self::SEND_SMS_PATH)
            ->assertOk()
            ->assertJsonPath('data.resend_available_in', 90);
    }

    public function test_send_sms_returns_500_on_notification_failure(): void
    {
        $this->actingAsSuperAdmin();

        $this->mock(Dispatcher::class, function ($mock) {
            $mock->shouldReceive('send')
                ->once()
                ->andThrow(new \Exception('SMS gateway error'));
        });

        $this->postJson(self::SEND_SMS_PATH)
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::SEND_ERROR_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // verify (non-production / default testing env)
    // -------------------------------------------------------------------------

    public function test_verify_returns_success_without_code_when_verification_disabled(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::VERIFY_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::VERIFY_SUCCESS_MESSAGE);
    }

    public function test_verify_does_not_require_phone_verification_field_when_disabled(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::VERIFY_PATH, [])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('data.phone_verification');
    }

    // -------------------------------------------------------------------------
    // verify (production env)
    // -------------------------------------------------------------------------

    public function test_verify_with_valid_code_returns_success_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedVerificationCode($admin, '654321');

            $this->postJson(self::VERIFY_PATH, [
                'phone_verification' => 654321,
            ])
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', self::VERIFY_SUCCESS_MESSAGE)
                ->assertJsonPath('data.phone_verification', 654321);
        });
    }

    public function test_verify_with_invalid_code_returns_validation_error_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedVerificationCode($admin, '654321');

            $this->postJson(self::VERIFY_PATH, [
                'phone_verification' => 111111,
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['phone_verification']);
        });
    }

    public function test_verify_with_missing_code_returns_validation_error_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $this->actingAsSuperAdmin();

            $this->postJson(self::VERIFY_PATH, [])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['phone_verification']);
        });
    }

    public function test_verify_with_wrong_digit_count_fails_validation_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedVerificationCode($admin, '654321');

            $this->postJson(self::VERIFY_PATH, [
                'phone_verification' => 12345,
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['phone_verification']);
        });
    }

    // -------------------------------------------------------------------------
    // status
    // -------------------------------------------------------------------------

    public function test_status_returns_verified_true_with_null_expiry_in_non_production(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::STATUS_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.verified', true)
            ->assertJsonPath('data.expires_at', null)
            ->assertJsonPath('data.remaining_seconds', null)
            ->assertJsonPath('data.duration_minutes', null);
    }

    public function test_status_returns_unverified_when_no_session_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $this->actingAsSuperAdmin();

            $this->getJson(self::STATUS_PATH)
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.verified', false)
                ->assertJsonPath('data.expires_at', null)
                ->assertJsonPath('data.remaining_seconds', 0)
                ->assertJsonPath('data.duration_minutes', null);
        });
    }

    public function test_status_returns_active_session_details_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $durationMinutes = 15;

            Cache::put('phone.verification.'.$admin->id, [
                'verified_at' => now()->timestamp,
                'duration_minutes' => $durationMinutes,
            ], now()->addMinutes($durationMinutes));

            $response = $this->getJson(self::STATUS_PATH)
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.verified', true)
                ->assertJsonPath('data.duration_minutes', $durationMinutes);

            $remainingSeconds = $response->json('data.remaining_seconds');

            $this->assertIsInt($remainingSeconds);
            $this->assertGreaterThan(0, $remainingSeconds);
            $this->assertLessThanOrEqual($durationMinutes * 60, $remainingSeconds);
            $this->assertNotNull($response->json('data.expires_at'));
        });
    }

    // -------------------------------------------------------------------------
    // confirm (non-production)
    // -------------------------------------------------------------------------

    public function test_confirm_with_default_duration_in_non_production(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $defaultDuration = (int) config('phone_verification.default_duration_minutes');

        $this->postJson(self::CONFIRM_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::CONFIRM_SUCCESS_MESSAGE)
            ->assertJsonPath('data.verified', true)
            ->assertJsonPath('data.expires_at', null)
            ->assertJsonPath('data.remaining_seconds', null)
            ->assertJsonPath('data.duration_minutes', null);

        $payload = Cache::get('phone.verification.'.$admin->id);

        $this->assertIsArray($payload);
        $this->assertSame($defaultDuration, (int) $payload['duration_minutes']);
    }

    public function test_confirm_clamps_custom_duration_to_min_and_max_in_non_production(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $minDuration = (int) config('phone_verification.min_duration_minutes');
        $maxDuration = (int) config('phone_verification.max_duration_minutes');

        $this->postJson(self::CONFIRM_PATH, ['duration_minutes' => 1])
            ->assertOk()
            ->assertJsonPath('data.verified', true);

        $payload = Cache::get('phone.verification.'.$admin->id);
        $this->assertSame($minDuration, (int) $payload['duration_minutes']);

        Cache::forget('phone.verification.'.$admin->id);

        $this->postJson(self::CONFIRM_PATH, ['duration_minutes' => 999])
            ->assertOk()
            ->assertJsonPath('data.verified', true);

        $payload = Cache::get('phone.verification.'.$admin->id);
        $this->assertSame($maxDuration, (int) $payload['duration_minutes']);
    }

    public function test_confirm_does_not_require_phone_verification_code_in_non_production(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::CONFIRM_PATH, ['duration_minutes' => 20])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::CONFIRM_SUCCESS_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // confirm (production)
    // -------------------------------------------------------------------------

    public function test_confirm_with_valid_code_and_duration_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedVerificationCode($admin, '123456');

            $this->postJson(self::CONFIRM_PATH, [
                'phone_verification' => 123456,
                'duration_minutes' => 20,
            ])
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', self::CONFIRM_SUCCESS_MESSAGE)
                ->assertJsonPath('data.verified', true)
                ->assertJsonPath('data.duration_minutes', 20)
                ->assertJsonPath('data.remaining_seconds', 20 * 60);
        });
    }

    public function test_confirm_clears_verify_code_cache_after_success_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedVerificationCode($admin, '123456');

            $this->assertNotNull(Cache::get('verify.code.'.$admin->id));

            $this->postJson(self::CONFIRM_PATH, [
                'phone_verification' => 123456,
                'duration_minutes' => 15,
            ])->assertOk();

            $this->assertNull(Cache::get('verify.code.'.$admin->id));
        });
    }

    public function test_confirm_with_invalid_code_fails_validation_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedVerificationCode($admin, '123456');

            $this->postJson(self::CONFIRM_PATH, [
                'phone_verification' => 999999,
                'duration_minutes' => 15,
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['phone_verification']);
        });
    }

    public function test_confirm_with_missing_duration_minutes_fails_validation_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedVerificationCode($admin, '123456');

            $this->postJson(self::CONFIRM_PATH, [
                'phone_verification' => 123456,
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['duration_minutes']);
        });
    }

    public function test_confirm_with_duration_below_min_fails_validation_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedVerificationCode($admin, '123456');
            $minDuration = (int) config('phone_verification.min_duration_minutes');

            $this->postJson(self::CONFIRM_PATH, [
                'phone_verification' => 123456,
                'duration_minutes' => $minDuration - 1,
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['duration_minutes']);
        });
    }

    public function test_confirm_with_duration_above_max_fails_validation_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedVerificationCode($admin, '123456');
            $maxDuration = (int) config('phone_verification.max_duration_minutes');

            $this->postJson(self::CONFIRM_PATH, [
                'phone_verification' => 123456,
                'duration_minutes' => $maxDuration + 1,
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['duration_minutes']);
        });
    }
}

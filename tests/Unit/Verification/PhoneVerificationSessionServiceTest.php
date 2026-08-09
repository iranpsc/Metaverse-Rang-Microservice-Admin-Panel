<?php

namespace Tests\Unit\Verification;

use App\Services\PhoneVerificationSessionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesVerificationApiSchema;
use Tests\TestCase;

class PhoneVerificationSessionServiceTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesVerificationApiSchema;

    private PhoneVerificationSessionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpVerificationApiSchema();
        $this->service = app(PhoneVerificationSessionService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // isEnabled
    // -------------------------------------------------------------------------

    public function test_is_enabled_returns_false_in_testing_environment(): void
    {
        $this->assertFalse($this->service->isEnabled());
    }

    public function test_is_enabled_returns_true_in_production_environment(): void
    {
        $this->withProductionEnvironment(function () {
            $this->assertTrue($this->service->isEnabled());
        });
    }

    // -------------------------------------------------------------------------
    // isVerified
    // -------------------------------------------------------------------------

    public function test_is_verified_returns_true_when_verification_disabled(): void
    {
        $this->actingAsSuperAdmin();

        $this->assertTrue($this->service->isVerified());
    }

    public function test_is_verified_returns_false_without_session_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $this->actingAsSuperAdmin();

            $this->assertFalse($this->service->isVerified());
        });
    }

    public function test_is_verified_returns_true_with_active_session_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();

            $this->service->confirm(10);

            $this->assertTrue($this->service->isVerified());
        });
    }

    public function test_is_verified_returns_false_after_session_expires_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();

            $this->service->confirm(5);

            Carbon::setTestNow(now()->addMinutes(6));

            $this->assertFalse($this->service->isVerified());
        });
    }

    // -------------------------------------------------------------------------
    // confirm
    // -------------------------------------------------------------------------

    public function test_confirm_stores_verified_at_and_duration_in_cache(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $durationMinutes = 12;

        Carbon::setTestNow('2026-08-07 10:00:00');

        $this->service->confirm($durationMinutes);

        $payload = Cache::get('phone.verification.'.$admin->id);

        $this->assertIsArray($payload);
        $this->assertSame(Carbon::parse('2026-08-07 10:00:00')->timestamp, (int) $payload['verified_at']);
        $this->assertSame($durationMinutes, (int) $payload['duration_minutes']);
    }

    public function test_confirm_clamps_duration_before_storing(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $maxDuration = (int) config('phone_verification.max_duration_minutes');

        $this->service->confirm(999);

        $payload = Cache::get('phone.verification.'.$admin->id);

        $this->assertSame($maxDuration, (int) $payload['duration_minutes']);
    }

    public function test_confirm_does_nothing_when_admin_is_not_authenticated(): void
    {
        $this->service->confirm(15);

        $this->assertFalse(Cache::has('phone.verification.1'));
    }

    // -------------------------------------------------------------------------
    // getStatus
    // -------------------------------------------------------------------------

    public function test_get_status_returns_verified_true_with_null_fields_when_disabled(): void
    {
        $this->actingAsSuperAdmin();

        $status = $this->service->getStatus();

        $this->assertSame([
            'verified' => true,
            'expires_at' => null,
            'remaining_seconds' => null,
            'duration_minutes' => null,
        ], $status);
    }

    public function test_get_status_returns_unverified_without_session_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $this->actingAsSuperAdmin();

            $status = $this->service->getStatus();

            $this->assertSame([
                'verified' => false,
                'expires_at' => null,
                'remaining_seconds' => 0,
                'duration_minutes' => null,
            ], $status);
        });
    }

    public function test_get_status_returns_active_session_details_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $this->actingAsSuperAdmin();

            Carbon::setTestNow('2026-08-07 10:00:00');

            $this->service->confirm(15);

            $status = $this->service->getStatus();

            $this->assertTrue($status['verified']);
            $this->assertSame(15, $status['duration_minutes']);
            $this->assertSame(15 * 60, $status['remaining_seconds']);
            $this->assertSame(
                Carbon::parse('2026-08-07 10:15:00')->toIso8601String(),
                $status['expires_at']
            );
        });
    }

    public function test_get_status_returns_zero_remaining_seconds_for_expired_session_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();

            Carbon::setTestNow('2026-08-07 10:00:00');

            Cache::put('phone.verification.'.$admin->id, [
                'verified_at' => Carbon::parse('2026-08-07 09:54:00')->timestamp,
                'duration_minutes' => 5,
            ], now()->addHour());

            Carbon::setTestNow('2026-08-07 10:00:00');

            $status = $this->service->getStatus();

            $this->assertFalse($status['verified']);
            $this->assertSame(0, $status['remaining_seconds']);
            $this->assertNull($status['expires_at']);
            $this->assertSame(5, $status['duration_minutes']);
        });
    }

    // -------------------------------------------------------------------------
    // clampDuration
    // -------------------------------------------------------------------------

    public function test_clamp_duration_returns_value_within_min_and_max(): void
    {
        config()->set('phone_verification.min_duration_minutes', 5);
        config()->set('phone_verification.max_duration_minutes', 50);

        $this->assertSame(25, $this->service->clampDuration(25));
    }

    public function test_clamp_duration_clamps_values_below_minimum(): void
    {
        config()->set('phone_verification.min_duration_minutes', 5);
        config()->set('phone_verification.max_duration_minutes', 50);

        $this->assertSame(5, $this->service->clampDuration(1));
        $this->assertSame(5, $this->service->clampDuration(0));
        $this->assertSame(5, $this->service->clampDuration(-10));
    }

    public function test_clamp_duration_clamps_values_above_maximum(): void
    {
        config()->set('phone_verification.min_duration_minutes', 5);
        config()->set('phone_verification.max_duration_minutes', 50);

        $this->assertSame(50, $this->service->clampDuration(51));
        $this->assertSame(50, $this->service->clampDuration(1000));
    }

    public function test_clamp_duration_uses_minimum_when_max_is_less_than_min(): void
    {
        config()->set('phone_verification.min_duration_minutes', 10);
        config()->set('phone_verification.max_duration_minutes', 3);

        $this->assertSame(10, $this->service->clampDuration(7));
        $this->assertSame(10, $this->service->clampDuration(20));
    }

    // -------------------------------------------------------------------------
    // clear
    // -------------------------------------------------------------------------

    public function test_clear_removes_session_cache_for_authenticated_admin(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $this->service->confirm(15);

        $this->assertTrue(Cache::has('phone.verification.'.$admin->id));

        $this->service->clear();

        $this->assertFalse(Cache::has('phone.verification.'.$admin->id));
    }

    public function test_clear_removes_session_cache_for_explicit_admin_id(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $this->service->confirm(15);

        $this->service->clear($admin->id);

        $this->assertFalse(Cache::has('phone.verification.'.$admin->id));
    }

    // -------------------------------------------------------------------------
    // remainingSeconds (via getStatus)
    // -------------------------------------------------------------------------

    public function test_remaining_seconds_decreases_as_time_passes_in_production(): void
    {
        $this->withProductionEnvironment(function () {
            $this->actingAsSuperAdmin();

            Carbon::setTestNow('2026-08-07 10:00:00');

            $this->service->confirm(10);

            $initialRemaining = $this->service->getStatus()['remaining_seconds'];
            $this->assertSame(600, $initialRemaining);

            Carbon::setTestNow('2026-08-07 10:02:30');

            $laterRemaining = $this->service->getStatus()['remaining_seconds'];
            $this->assertSame(450, $laterRemaining);
        });
    }
}

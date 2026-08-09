<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class ForgotPasswordApiTest extends TestCase
{
    use CreatesAuthApiSchema;

    private const PASSWORD_EMAIL_PATH = '/api/password/email';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthApiSchema();
        Notification::fake();
    }

    public function test_valid_email_sends_password_reset_notification(): void
    {
        $admin = $this->createAdmin([
            'email' => 'reset@example.com',
        ]);

        $response = $this->postJson(self::PASSWORD_EMAIL_PATH, [
            'email' => 'reset@example.com',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message']);

        Notification::assertSentTo($admin, ResetPassword::class);

        $this->assertDatabaseHas('password_resets', [
            'email' => 'reset@example.com',
        ], 'sqlite');
    }

    public function test_unknown_email_returns_validation_error_and_does_not_send_notification(): void
    {
        $response = $this->postJson(self::PASSWORD_EMAIL_PATH, [
            'email' => 'unknown@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        Notification::assertNothingSent();

        $this->assertDatabaseMissing('password_resets', [
            'email' => 'unknown@example.com',
        ], 'sqlite');
    }

    public function test_missing_email_returns_validation_error(): void
    {
        $this->postJson(self::PASSWORD_EMAIL_PATH, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        Notification::assertNothingSent();
    }

    public function test_invalid_email_format_returns_validation_error(): void
    {
        $this->postJson(self::PASSWORD_EMAIL_PATH, [
            'email' => 'not-an-email',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        Notification::assertNothingSent();
    }

    public function test_successful_reset_request_writes_activity_log(): void
    {
        $this->createAdmin([
            'email' => 'activity-reset@example.com',
        ]);

        $this->postJson(self::PASSWORD_EMAIL_PATH, [
            'email' => 'activity-reset@example.com',
        ])->assertOk();

        $this->assertTrue(
            Activity::query()
                ->where('log_name', 'auth')
                ->where('event', 'password_reset_requested')
                ->exists()
        );
    }

    public function test_unknown_email_does_not_write_password_reset_activity_log(): void
    {
        $this->postJson(self::PASSWORD_EMAIL_PATH, [
            'email' => 'no-log@example.com',
        ])->assertUnprocessable();

        $this->assertFalse(
            Activity::query()
                ->where('log_name', 'auth')
                ->where('event', 'password_reset_requested')
                ->exists()
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createAdmin(array $overrides = []): Admin
    {
        $password = $overrides['password'] ?? 'password';

        return Admin::withoutEvents(function () use ($overrides, $password) {
            return Admin::create([
                'name' => $overrides['name'] ?? 'Test Admin',
                'email' => $overrides['email'] ?? Str::uuid().'@example.com',
                'password' => Hash::make($password),
                'phone' => $overrides['phone'] ?? '09120000000',
                'active' => $overrides['active'] ?? 1,
            ]);
        });
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class ResetPasswordApiTest extends TestCase
{
    use CreatesAuthApiSchema;

    private const PASSWORD_RESET_PATH = '/api/password/reset';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthApiSchema();
    }

    public function test_valid_token_resets_password_and_writes_activity_log(): void
    {
        $admin = $this->createAdmin([
            'email' => 'reset-ok@example.com',
            'password' => 'OldPass1!',
        ]);

        $token = Password::broker('admins')->createToken($admin);

        $response = $this->postJson(self::PASSWORD_RESET_PATH, [
            'token' => $token,
            'email' => 'reset-ok@example.com',
            'password' => 'NewPass1!',
            'password_confirmation' => 'NewPass1!',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message']);

        $admin->refresh();
        $this->assertTrue(Hash::check('NewPass1!', $admin->password));
        $this->assertFalse(Hash::check('OldPass1!', $admin->password));

        $this->assertTrue(
            Activity::query()
                ->where('log_name', 'auth')
                ->where('event', 'password_reset')
                ->exists()
        );
    }

    public function test_invalid_token_returns_validation_error(): void
    {
        $this->createAdmin([
            'email' => 'reset-bad@example.com',
        ]);

        $this->postJson(self::PASSWORD_RESET_PATH, [
            'token' => 'invalid-token',
            'email' => 'reset-bad@example.com',
            'password' => 'NewPass1!',
            'password_confirmation' => 'NewPass1!',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertFalse(
            Activity::query()
                ->where('log_name', 'auth')
                ->where('event', 'password_reset')
                ->exists()
        );
    }

    public function test_missing_fields_return_validation_errors(): void
    {
        $this->postJson(self::PASSWORD_RESET_PATH, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    public function test_password_confirmation_mismatch_returns_validation_error(): void
    {
        $admin = $this->createAdmin([
            'email' => 'reset-mismatch@example.com',
        ]);

        $token = Password::broker('admins')->createToken($admin);

        $this->postJson(self::PASSWORD_RESET_PATH, [
            'token' => $token,
            'email' => 'reset-mismatch@example.com',
            'password' => 'NewPass1!',
            'password_confirmation' => 'Different1!',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
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

<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class LoginApiTest extends TestCase
{
    use CreatesAuthApiSchema;

    private const LOGIN_PATH = '/api/login';

    private const ME_PATH = '/api/me';

    private const LOGOUT_PATH = '/api/logout';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthApiSchema();
        RateLimiter::clear($this->throttleKey('admin@example.com'));
    }

    public function test_successful_login_returns_token_and_creates_sanctum_token(): void
    {
        $admin = $this->createAdmin([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $response = $this->postJson(self::LOGIN_PATH, [
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'ورود با موفقیت انجام شد',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_expires_at',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertNotEmpty($response->json('data.token_expires_at'));

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => Admin::class,
            'tokenable_id' => $admin->id,
            'name' => 'auth-token',
        ], 'sqlite');
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $this->createAdmin([
            'email' => 'admin@example.com',
            'password' => 'correct-password',
        ]);

        $response = $this->postJson(self::LOGIN_PATH, [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('personal_access_tokens', 0, 'sqlite');
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson(self::LOGIN_PATH, [
            'email' => 'missing@example.com',
            'password' => 'any-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson(self::LOGIN_PATH, []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_rejects_invalid_email_format(): void
    {
        $response = $this->postJson(self::LOGIN_PATH, [
            'email' => 'not-an-email',
            'password' => 'secret-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_is_throttled_after_max_failed_attempts(): void
    {
        $email = 'throttled@example.com';

        $this->createAdmin([
            'email' => $email,
            'password' => 'correct-password',
        ]);

        RateLimiter::clear($this->throttleKey($email));

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->postJson(self::LOGIN_PATH, [
                'email' => $email,
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson(self::LOGIN_PATH, [
            'email' => $email,
            'password' => 'wrong-password',
        ])->assertStatus(429)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_me_returns_authenticated_user_resource(): void
    {
        $admin = $this->createAdmin([
            'email' => 'me@example.com',
            'name' => 'Me Admin',
        ]);

        Sanctum::actingAs($admin, abilities: ['*'], guard: 'admin');

        $response = $this->getJson(self::ME_PATH);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $admin->id,
                    'name' => 'Me Admin',
                    'email' => 'me@example.com',
                    'active' => 1,
                ],
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'image',
                    'active',
                    'roles',
                    'permissions',
                ],
            ]);
    }

    public function test_me_returns_unauthorized_when_unauthenticated(): void
    {
        $this->getJson(self::ME_PATH)
            ->assertUnauthorized();
    }

    public function test_logout_deletes_tokens_and_returns_success(): void
    {
        $admin = $this->createAdmin([
            'email' => 'logout@example.com',
        ]);

        $token = $admin->createToken('auth-token', ['*'], now()->addHours(3))->plainTextToken;

        $this->assertDatabaseCount('personal_access_tokens', 1, 'sqlite');

        $response = $this->withToken($token)->postJson(self::LOGOUT_PATH);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'خروج با موفقیت انجام شد',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0, 'sqlite');
    }

    public function test_logout_returns_unauthorized_when_unauthenticated(): void
    {
        $this->postJson(self::LOGOUT_PATH)
            ->assertUnauthorized();
    }

    public function test_login_token_can_be_used_for_authenticated_requests(): void
    {
        $this->createAdmin([
            'email' => 'token-user@example.com',
            'password' => 'secret-password',
            'name' => 'Token User',
        ]);

        $loginResponse = $this->postJson(self::LOGIN_PATH, [
            'email' => 'token-user@example.com',
            'password' => 'secret-password',
        ]);

        $loginResponse->assertOk();
        $token = $loginResponse->json('data.token');

        $this->withToken($token)
            ->getJson(self::ME_PATH)
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => 'token-user@example.com',
                    'name' => 'Token User',
                ],
            ]);
    }

    public function test_successful_login_writes_activity_log(): void
    {
        $this->createAdmin([
            'email' => 'activity-login@example.com',
            'password' => 'secret-password',
        ]);

        $this->postJson(self::LOGIN_PATH, [
            'email' => 'activity-login@example.com',
            'password' => 'secret-password',
        ])->assertOk();

        $this->assertTrue(
            Activity::query()
                ->where('log_name', 'auth')
                ->where('event', 'login')
                ->exists()
        );
    }

    public function test_failed_login_writes_activity_log(): void
    {
        $this->createAdmin([
            'email' => 'activity-fail@example.com',
            'password' => 'secret-password',
        ]);

        $this->postJson(self::LOGIN_PATH, [
            'email' => 'activity-fail@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable();

        $this->assertTrue(
            Activity::query()
                ->where('log_name', 'auth')
                ->where('event', 'login_failed')
                ->exists()
        );
    }

    public function test_logout_writes_activity_log(): void
    {
        $admin = $this->createAdmin([
            'email' => 'activity-logout@example.com',
        ]);

        $token = $admin->createToken('auth-token')->plainTextToken;

        $this->withToken($token)
            ->postJson(self::LOGOUT_PATH)
            ->assertOk();

        $this->assertTrue(
            Activity::query()
                ->where('log_name', 'auth')
                ->where('event', 'logout')
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

    private function throttleKey(string $email): string
    {
        return Str::transliterate(Str::lower($email).'|127.0.0.1');
    }
}

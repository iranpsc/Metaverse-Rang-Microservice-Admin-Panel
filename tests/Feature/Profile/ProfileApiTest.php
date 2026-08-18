<?php

namespace Tests\Feature\Profile;

use App\Models\Admin;
use App\Notifications\SendVerificationCode;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesProfileApiSchema;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesProfileApiSchema;

    private const SHOW_PATH = '/api/profile';

    private const UPDATE_INFO_PATH = '/api/profile/info';

    private const REQUEST_PASSWORD_PATH = '/api/profile/password/request';

    private const VERIFY_PASSWORD_PATH = '/api/profile/password/verify';

    private const SHOW_SUCCESS_MESSAGE = 'اطلاعات پروفایل با موفقیت دریافت شد';

    private const UPDATE_SUCCESS_MESSAGE = 'اطلاعات پروفایل با موفقیت بروزرسانی شد';

    private const REQUEST_PASSWORD_NON_PROD_MESSAGE = 'رمز عبور بدون نیاز به تایید بروزرسانی شد.';

    private const REQUEST_PASSWORD_PROD_MESSAGE = 'کد تایید برای شما ارسال شد.';

    private const REQUEST_PASSWORD_ERROR_MESSAGE = 'ارسال کد تایید با مشکل مواجه شد. لطفا مجدداً تلاش کنید.';

    private const VERIFY_PASSWORD_NON_PROD_MESSAGE = 'تغییر رمز عبور در محیط غیر پروداکشن نیاز به تایید ندارد.';

    private const VERIFY_PASSWORD_SUCCESS_MESSAGE = 'رمز عبور با موفقیت بروزرسانی شد.';

    private const VERIFY_PASSWORD_MISSING_PENDING_MESSAGE = 'درخواست تغییر رمز عبور یافت نشد یا منقضی شده است.';

    private const VERIFY_PASSWORD_INVALID_CODE_MESSAGE = 'کد تایید نامعتبر است یا منقضی شده است.';

    private const NEW_PASSWORD = 'NewSecurePass1!';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpProfileApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth — show
    // -------------------------------------------------------------------------

    public function test_unauthenticated_show_returns_unauthorized(): void
    {
        $this->getJson(self::SHOW_PATH)->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_show_profile(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $this->getJson(self::SHOW_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonPath('data.id', $admin->id);
    }

    public function test_authenticated_regular_admin_can_show_profile(): void
    {
        $admin = $this->actingAsRegularAdmin();

        $this->getJson(self::SHOW_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonPath('data.id', $admin->id);
    }

    // -------------------------------------------------------------------------
    // Auth — updateInfo
    // -------------------------------------------------------------------------

    public function test_unauthenticated_update_info_returns_unauthorized(): void
    {
        $this->putJson(self::UPDATE_INFO_PATH, ['name' => 'Updated'])->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_update_info(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson(self::UPDATE_INFO_PATH, ['name' => 'Updated Admin'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_update_info(): void
    {
        $this->actingAsRegularAdmin();

        $this->putJson(self::UPDATE_INFO_PATH, ['name' => 'Updated Admin'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Auth — requestPasswordChange
    // -------------------------------------------------------------------------

    public function test_unauthenticated_request_password_change_returns_unauthorized(): void
    {
        $this->postJson(self::REQUEST_PASSWORD_PATH)->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_request_password_change(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::REQUEST_PASSWORD_PATH, $this->validPasswordPayload())
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_authenticated_regular_admin_can_request_password_change(): void
    {
        $this->actingAsRegularAdmin();

        $this->postJson(self::REQUEST_PASSWORD_PATH, $this->validPasswordPayload())
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // -------------------------------------------------------------------------
    // Auth — verifyPasswordChange
    // -------------------------------------------------------------------------

    public function test_unauthenticated_verify_password_change_returns_unauthorized(): void
    {
        $this->postJson(self::VERIFY_PASSWORD_PATH)->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_verify_password_change(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::VERIFY_PASSWORD_PATH, ['code' => '123456'])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_authenticated_regular_admin_can_verify_password_change(): void
    {
        $this->actingAsRegularAdmin();

        $this->postJson(self::VERIFY_PASSWORD_PATH, ['code' => '123456'])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // -------------------------------------------------------------------------
    // show
    // -------------------------------------------------------------------------

    public function test_show_returns_authenticated_user_resource_structure(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $this->getJson(self::SHOW_PATH)
            ->assertOk()
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'image',
                    'active',
                    'roles',
                    'permissions',
                ],
            ])
            ->assertJsonPath('data.id', $admin->id)
            ->assertJsonPath('data.name', $admin->name)
            ->assertJsonPath('data.email', $admin->email)
            ->assertJsonPath('data.image', 'noimage.png')
            ->assertJsonPath('data.active', 1);
    }

    public function test_show_includes_roles_and_permissions_for_super_admin(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $permission = Permission::firstOrCreate(
            ['name' => 'manage-profile', 'guard_name' => 'admin'],
            ['title' => 'Manage Profile']
        );

        $admin->givePermissionTo($permission);

        $response = $this->getJson(self::SHOW_PATH)->assertOk();

        $this->assertContains('super-admin', $response->json('data.roles'));
        $this->assertContains('manage-profile', $response->json('data.permissions'));
    }

    // -------------------------------------------------------------------------
    // updateInfo
    // -------------------------------------------------------------------------

    public function test_update_info_updates_name(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $this->putJson(self::UPDATE_INFO_PATH, ['name' => 'Renamed Admin'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.name', 'Renamed Admin');

        $this->assertSame('Renamed Admin', $admin->fresh()->name);
    }

    public function test_update_info_requires_name(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson(self::UPDATE_INFO_PATH, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_info_rejects_invalid_image(): void
    {
        $this->actingAsSuperAdmin();
        $this->setUpProfileStorage();

        $this->put(self::UPDATE_INFO_PATH, [
            'name' => 'Valid Name',
            'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    public function test_update_info_stores_uploaded_image_and_updates_path(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $this->setUpProfileStorage();

        $response = $this->put(self::UPDATE_INFO_PATH, [
            'name' => 'Admin With Avatar',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();

        $imagePath = $response->json('data.image');

        $this->assertNotNull($imagePath);
        $this->assertStringStartsWith('profile/', $imagePath);
        Storage::disk('public')->assertExists($imagePath);
        $this->assertSame($imagePath, $admin->fresh()->image);
    }

    public function test_update_info_deletes_old_local_image_when_replaced(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $this->setUpProfileStorage();

        $oldPath = 'profile/old-avatar.jpg';
        Storage::disk('public')->put($oldPath, 'old-image-content');
        $admin->forceFill(['image' => $oldPath])->save();

        $this->put(self::UPDATE_INFO_PATH, [
            'name' => 'Admin With New Avatar',
            'image' => UploadedFile::fake()->image('new-avatar.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();

        Storage::disk('public')->assertMissing($oldPath);
        $this->assertNotSame($oldPath, $admin->fresh()->image);
    }

    public function test_update_info_does_not_delete_noimage_png_when_replaced(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $this->setUpProfileStorage();

        Storage::disk('public')->put('noimage.png', 'placeholder');
        $admin->forceFill(['image' => 'noimage.png'])->save();

        $this->put(self::UPDATE_INFO_PATH, [
            'name' => 'Admin With New Avatar',
            'image' => UploadedFile::fake()->image('new-avatar.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();

        Storage::disk('public')->assertExists('noimage.png');
    }

    public function test_update_info_does_not_delete_http_url_image_when_replaced(): void
    {
        $admin = $this->actingAsSuperAdmin();
        $this->setUpProfileStorage();

        $remoteImage = 'https://cdn.example.com/avatars/admin.jpg';
        $admin->forceFill(['image' => $remoteImage])->save();

        $this->put(self::UPDATE_INFO_PATH, [
            'name' => 'Admin With New Avatar',
            'image' => UploadedFile::fake()->image('new-avatar.jpg'),
        ], ['Accept' => 'application/json'])->assertOk();

        $this->assertStringStartsWith('profile/', $admin->fresh()->image);
    }

    // -------------------------------------------------------------------------
    // requestPasswordChange (non-production)
    // -------------------------------------------------------------------------

    public function test_request_password_change_fails_with_wrong_current_password(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::REQUEST_PASSWORD_PATH, [
            'current_password' => 'wrong-password',
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_request_password_change_fails_when_password_confirmation_mismatches(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::REQUEST_PASSWORD_PATH, [
            'current_password' => 'password',
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => 'DifferentPass1!',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_request_password_change_updates_password_immediately_in_non_production(): void
    {
        $admin = $this->actingAsSuperAdmin();

        $this->postJson(self::REQUEST_PASSWORD_PATH, $this->validPasswordPayload())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::REQUEST_PASSWORD_NON_PROD_MESSAGE)
            ->assertJsonPath('requires_verification', false);

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $admin->fresh()->password));
    }

    public function test_request_password_change_allows_login_with_new_password_in_non_production(): void
    {
        $admin = Admin::withoutEvents(function () {
            return Admin::create([
                'name' => 'Login Test Admin',
                'email' => Str::uuid().'@example.com',
                'password' => bcrypt('password'),
                'phone' => '09120000001',
                'active' => 1,
            ]);
        });

        $token = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertOk()->json('data.token');

        $this->withToken($token)
            ->postJson(self::REQUEST_PASSWORD_PATH, $this->validPasswordPayload())
            ->assertOk();

        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->withoutToken();
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => self::NEW_PASSWORD,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // -------------------------------------------------------------------------
    // requestPasswordChange (production)
    // -------------------------------------------------------------------------

    public function test_request_password_change_in_production_sends_notification_and_caches_pending_password(): void
    {
        $this->withProductionEnvironment(function () {
            Notification::fake();

            $admin = $this->actingAsSuperAdmin();
            $this->seedPhoneVerificationSession($admin);
            $pendingKey = $this->pendingPasswordCacheKey($admin->id);

            $this->postJson(self::REQUEST_PASSWORD_PATH, $this->validPasswordPayload())
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', self::REQUEST_PASSWORD_PROD_MESSAGE)
                ->assertJsonPath('requires_verification', true);

            Notification::assertSentTo($admin, SendVerificationCode::class);

            $pendingPassword = Cache::get($pendingKey);
            $this->assertNotNull($pendingPassword);
            $this->assertTrue(Hash::check(self::NEW_PASSWORD, $pendingPassword));
        });
    }

    public function test_request_password_change_in_production_returns_500_on_notification_failure_and_clears_cache(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedPhoneVerificationSession($admin);
            $pendingKey = $this->pendingPasswordCacheKey($admin->id);

            $this->mock(Dispatcher::class, function ($mock) {
                $mock->shouldReceive('send')
                    ->once()
                    ->andThrow(new \Exception('SMS gateway error'));
            });

            $this->postJson(self::REQUEST_PASSWORD_PATH, $this->validPasswordPayload())
                ->assertStatus(500)
                ->assertJsonPath('success', false)
                ->assertJsonPath('message', self::REQUEST_PASSWORD_ERROR_MESSAGE);

            $this->assertNull(Cache::get($pendingKey));
            $this->assertTrue(Hash::check('password', $admin->fresh()->password));
        });
    }

    // -------------------------------------------------------------------------
    // verifyPasswordChange (non-production)
    // -------------------------------------------------------------------------

    public function test_verify_password_change_returns_success_without_verification_in_non_production(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::VERIFY_PASSWORD_PATH, ['code' => '123456'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::VERIFY_PASSWORD_NON_PROD_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // verifyPasswordChange (production)
    // -------------------------------------------------------------------------

    public function test_verify_password_change_in_production_fails_when_pending_password_missing(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedPhoneVerificationSession($admin);

            $this->postJson(self::VERIFY_PASSWORD_PATH, ['code' => '123456'])
                ->assertStatus(422)
                ->assertJsonPath('success', false)
                ->assertJsonPath('message', self::VERIFY_PASSWORD_MISSING_PENDING_MESSAGE);
        });
    }

    public function test_verify_password_change_in_production_fails_with_invalid_code(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedPhoneVerificationSession($admin);
            $this->seedVerificationCode($admin, '123456');

            Cache::put(
                $this->pendingPasswordCacheKey($admin->id),
                Hash::make(self::NEW_PASSWORD),
                now()->addMinutes(5)
            );

            $this->postJson(self::VERIFY_PASSWORD_PATH, ['code' => '999999'])
                ->assertStatus(422)
                ->assertJsonPath('success', false)
                ->assertJsonPath('message', self::VERIFY_PASSWORD_INVALID_CODE_MESSAGE);
        });
    }

    public function test_verify_password_change_in_production_updates_password_and_clears_caches(): void
    {
        $this->withProductionEnvironment(function () {
            $admin = $this->actingAsSuperAdmin();
            $this->seedPhoneVerificationSession($admin);
            $pendingKey = $this->pendingPasswordCacheKey($admin->id);
            $verifyCodeKey = 'verify.code.'.$admin->id;

            Cache::put(
                $pendingKey,
                Hash::make(self::NEW_PASSWORD),
                now()->addMinutes(5)
            );
            $this->seedVerificationCode($admin, '123456');

            $this->postJson(self::VERIFY_PASSWORD_PATH, ['code' => '123456'])
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', self::VERIFY_PASSWORD_SUCCESS_MESSAGE);

            $this->assertTrue(Hash::check(self::NEW_PASSWORD, $admin->fresh()->password));
            $this->assertNull(Cache::get($pendingKey));
            $this->assertNull(Cache::get($verifyCodeKey));
        });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @return array<string, string>
     */
    private function validPasswordPayload(): array
    {
        return [
            'current_password' => 'password',
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ];
    }

    private function pendingPasswordCacheKey(int $adminId): string
    {
        return 'admin.password.pending.'.$adminId;
    }
}

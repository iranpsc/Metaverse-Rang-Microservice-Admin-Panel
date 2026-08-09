<?php

namespace Tests\Feature\Kyc;

use App\Models\Kyc;
use App\Models\KycVerifyText;
use App\Models\User;
use App\Notifications\KycDeniedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesKycApiSchema;
use Tests\TestCase;

class KycApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesKycApiSchema;

    private const INDEX_PATH = '/api/kycs';

    private const INDEX_SUCCESS_MESSAGE = 'KYC records retrieved successfully.';

    private const SHOW_SUCCESS_MESSAGE = 'KYC record retrieved successfully.';

    private const UPDATE_SUCCESS_MESSAGE = 'اطلاعات با موفقیت ثبت شد';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpKycApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_show_returns_unauthorized(): void
    {
        $this->getJson($this->kycPath(1))->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $this->putJson($this->kycPath(1), ['kyc_errors' => []])->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $user = $this->createUser();
        $kyc = $this->createKyc($user);

        $this->getJson($this->kycPath($kyc))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE);

        $this->putJson($this->kycPath($kyc), ['kyc_errors' => []])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $user = $this->createUser();
        $kyc = $this->createKyc($user);

        $this->getJson($this->kycPath($kyc))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE);

        $this->putJson($this->kycPath($kyc), ['kyc_errors' => []])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Happy path / structure (index)
    // -------------------------------------------------------------------------

    public function test_empty_dataset_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.kycs', [])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_returns_full_json_structure(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createKyc($user, [
            'fname' => 'Ali',
            'lname' => 'Rezaei',
            'melli_code' => '1234567890',
            'birthdate' => '1990-05-15',
            'province' => 'Tehran',
            'gender' => 'male',
            'melli_card' => 'kyc/cards/card.jpg',
            'video' => 'kyc/videos/video.mp4',
            'status' => 0,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'kycs' => [
                        [
                            'id',
                            'fname',
                            'lname',
                            'melli_code',
                            'birthdate',
                            'created_at',
                            'province',
                            'gender',
                            'melli_card',
                            'video',
                            'status',
                            'status_badge',
                            'errors',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'from',
                        'to',
                    ],
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    public function test_search_filters_by_melli_code_partial_match(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createKyc($user, ['melli_code' => '0012345678', 'fname' => 'Match']);
        $this->createKyc($user, ['melli_code' => '9988776655', 'fname' => 'Miss']);

        $this->getJson(self::INDEX_PATH.'?search=123456')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.kycs.0.fname', 'Match')
            ->assertJsonPath('data.kycs.0.melli_code', '0012345678');
    }

    public function test_search_trims_whitespace_around_melli_code(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createKyc($user, ['melli_code' => '5566778899', 'fname' => 'Trimmed']);

        $this->getJson(self::INDEX_PATH.'?search='.urlencode('  556677  '))
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.kycs.0.fname', 'Trimmed');
    }

    public function test_search_with_no_matches_returns_empty_collection(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createKyc($user, ['melli_code' => '1111111111']);

        $this->getJson(self::INDEX_PATH.'?search=NoMatchNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.kycs', []);
    }

    public function test_empty_search_behaves_like_no_filter(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createKyc($user, ['melli_code' => '1111111111']);
        $this->createKyc($user, ['melli_code' => '2222222222']);

        $this->getJson(self::INDEX_PATH.'?search=')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_pagination_defaults_to_page_one_and_ten_per_page(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        for ($i = 1; $i <= 3; $i++) {
            $this->createKyc($user, ['melli_code' => "100000000{$i}"]);
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 3);
    }

    public function test_pagination_respects_per_page_and_page(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        for ($i = 1; $i <= 5; $i++) {
            $this->createKyc($user, ['melli_code' => "200000000{$i}"]);
        }

        $this->getJson(self::INDEX_PATH.'?'.http_build_query([
            'per_page' => 2,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonPath('data.pagination.from', 3)
            ->assertJsonPath('data.pagination.to', 4)
            ->assertJsonCount(2, 'data.kycs');
    }

    // -------------------------------------------------------------------------
    // Ordering
    // -------------------------------------------------------------------------

    public function test_kycs_are_ordered_by_created_at_desc(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();

        $older = $this->createKyc($user, ['fname' => 'Older', 'melli_code' => '3000000001']);
        $older->forceFill(['created_at' => now()->subDays(2)])->save();

        $newer = $this->createKyc($user, ['fname' => 'Newer', 'melli_code' => '3000000002']);
        $newer->forceFill(['created_at' => now()->subHour()])->save();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.kycs.0.id', $newer->id)
            ->assertJsonPath('data.kycs.1.id', $older->id);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_not_found_for_missing_id(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson($this->kycPath(99999))->assertNotFound();
    }

    public function test_show_returns_full_json_structure_with_user_and_verify_text(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'KYC User', 'code' => '5555']);
        $verifyText = $this->createKycVerifyText(['text' => 'Read this phrase aloud']);
        $kyc = $this->createKyc($user, [
            'fname' => 'Sara',
            'lname' => 'Ahmadi',
            'melli_code' => '0123456789',
            'birthdate' => '1995-03-20',
            'province' => 'Isfahan',
            'gender' => 'female',
            'melli_card' => 'kyc/cards/sara.jpg',
            'video' => 'kyc/videos/sara.mp4',
            'status' => 0,
            'verify_text_id' => $verifyText->id,
        ]);

        $this->getJson($this->kycPath($kyc))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'fname',
                    'lname',
                    'melli_code',
                    'birthdate',
                    'created_at',
                    'province',
                    'gender',
                    'melli_card',
                    'video',
                    'status',
                    'status_badge',
                    'errors',
                    'user' => ['id', 'name', 'code'],
                    'verify_text' => ['id', 'text'],
                ],
            ])
            ->assertJsonPath('data.id', $kyc->id)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.name', 'KYC User')
            ->assertJsonPath('data.user.code', '5555')
            ->assertJsonPath('data.verify_text.id', $verifyText->id)
            ->assertJsonPath('data.verify_text.text', 'Read this phrase aloud');
    }

    public function test_show_formats_birthdate_and_created_at_as_jalali(): void
    {
        $this->actingAsSuperAdmin();

        Carbon::setTestNow('2024-06-15 14:30:00');

        $user = $this->createUser();
        $kyc = $this->createKyc($user, [
            'birthdate' => '1990-01-15',
        ]);
        $kyc->forceFill(['created_at' => Carbon::parse('2024-06-15 14:30:00')])->save();

        $birthdateJalali = Jalalian::fromCarbon(Carbon::parse('1990-01-15'))->format('Y/m/d');
        $createdAtJalali = Jalalian::fromCarbon(Carbon::parse('2024-06-15 14:30:00'))->format('Y/m/d');

        $this->getJson($this->kycPath($kyc))
            ->assertOk()
            ->assertJsonPath('data.birthdate', $birthdateJalali)
            ->assertJsonPath('data.created_at', $createdAtJalali);

        Carbon::setTestNow();
    }

    public function test_show_includes_status_badge_for_pending_kyc(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $kyc = $this->createKyc($user, ['status' => 0]);

        $this->getJson($this->kycPath($kyc))
            ->assertOk()
            ->assertJsonPath(
                'data.status_badge',
                '<span class="badge badge-info">در انتظار بررسی</span>'
            );
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_returns_not_found_for_missing_id(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->kycPath(99999), ['kyc_errors' => []])->assertNotFound();
    }

    public function test_update_with_empty_kyc_errors_approves_kyc(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $user = $this->createUser();
        $kyc = $this->createKyc($user, [
            'status' => 0,
            'errors' => ['video' => 'old error'],
        ]);

        $this->putJson($this->kycPath($kyc), ['kyc_errors' => []])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.status', 1)
            ->assertJsonPath('data.errors', null);

        $this->assertDatabaseHas('kycs', [
            'id' => $kyc->id,
            'status' => 1,
        ], 'sqlite');

        $this->assertNull($kyc->fresh()->errors);
        Notification::assertNothingSent();
    }

    public function test_update_without_kyc_errors_key_approves_kyc(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $user = $this->createUser();
        $kyc = $this->createKyc($user, ['status' => 0]);

        $this->putJson($this->kycPath($kyc), [])
            ->assertOk()
            ->assertJsonPath('data.status', 1)
            ->assertJsonPath('data.errors', null);

        Notification::assertNothingSent();
    }

    public function test_update_with_non_empty_kyc_errors_rejects_kyc(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $user = $this->createUser();
        $kyc = $this->createKyc($user, ['status' => 0]);
        $errors = [
            'melli_card' => 'تصویر کارت ملی نامعتبر است',
            'video' => 'ویدیو واضح نیست',
        ];

        $this->putJson($this->kycPath($kyc), ['kyc_errors' => $errors])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.status', -1)
            ->assertJsonPath('data.errors', $errors);

        $this->assertDatabaseHas('kycs', [
            'id' => $kyc->id,
            'status' => -1,
        ], 'sqlite');

        $this->assertSame($errors, $kyc->fresh()->errors);
    }

    public function test_update_reject_sends_kyc_denied_notification_to_user(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $user = $this->createUser();
        $kyc = $this->createKyc($user, ['status' => 0]);
        $errors = ['video' => 'ویدیو واضح نیست'];

        $this->putJson($this->kycPath($kyc), ['kyc_errors' => $errors])->assertOk();

        Notification::assertSentTo(
            $user,
            KycDeniedNotification::class,
            function (KycDeniedNotification $notification) use ($user) {
                $payload = $notification->toArray($user);

                return ($payload['message'] ?? null) === 'احراز هویت شما تایید نشد'
                    && ($payload['related-to'] ?? null) === 'kyc';
            }
        );
    }

    public function test_update_approve_does_not_send_notification(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $user = $this->createUser();
        $kyc = $this->createKyc($user, ['status' => 0]);

        $this->putJson($this->kycPath($kyc), ['kyc_errors' => []])->assertOk();

        Notification::assertNothingSent();
    }

    public function test_update_returns_fresh_data_after_status_change(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $kyc = $this->createKyc($user, [
            'status' => 0,
            'fname' => 'Before',
        ]);

        $this->putJson($this->kycPath($kyc), ['kyc_errors' => []])
            ->assertOk()
            ->assertJsonPath('data.id', $kyc->id)
            ->assertJsonPath('data.fname', 'Before')
            ->assertJsonPath('data.status', 1)
            ->assertJsonPath(
                'data.status_badge',
                '<span class="badge badge-success">تایید شده</span>'
            );
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_update_rejects_non_array_kyc_errors(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $kyc = $this->createKyc($user);

        $this->putJson($this->kycPath($kyc), ['kyc_errors' => 'not-an-array'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['kyc_errors']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function kycPath(int|Kyc $kyc): string
    {
        $id = $kyc instanceof Kyc ? $kyc->id : $kyc;

        return self::INDEX_PATH.'/'.$id;
    }

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

    private function createKycVerifyText(array $overrides = []): KycVerifyText
    {
        return KycVerifyText::create(array_merge([
            'text' => 'Verify phrase '.Str::random(8),
        ], $overrides));
    }

    private function createKyc(User $user, array $overrides = []): Kyc
    {
        return Kyc::create(array_merge([
            'user_id' => $user->id,
            'fname' => 'Test',
            'lname' => 'User',
            'melli_code' => (string) random_int(1000000000, 9999999999),
            'birthdate' => '1990-01-01',
            'province' => 'Tehran',
            'gender' => 'male',
            'melli_card' => 'kyc/cards/default.jpg',
            'video' => 'kyc/videos/default.mp4',
            'status' => 0,
            'errors' => null,
            'verify_text_id' => null,
        ], $overrides));
    }
}

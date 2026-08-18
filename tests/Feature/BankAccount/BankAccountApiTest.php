<?php

namespace Tests\Feature\BankAccount;

use App\Models\BankAccount;
use App\Models\Kyc;
use App\Models\User;
use App\Notifications\KycDeniedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesBankAccountApiSchema;
use Tests\TestCase;

class BankAccountApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesBankAccountApiSchema;

    private const INDEX_PATH = '/api/bank-accounts';

    private const INDEX_SUCCESS_MESSAGE = 'Bank account records retrieved successfully.';

    private const SHOW_SUCCESS_MESSAGE = 'Bank account record retrieved successfully.';

    private const UPDATE_SUCCESS_MESSAGE = 'اطلاعات با موفقیت ثبت شد';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBankAccountApiSchema();
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
        $this->getJson($this->bankAccountPath(1))->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $this->putJson($this->bankAccountPath(1), ['bank_account_errors' => []])->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $user = $this->createUser();
        $this->createKyc($user);
        $bankAccount = $this->createBankAccount($user);

        $this->getJson($this->bankAccountPath($bankAccount))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE);

        $this->putJson($this->bankAccountPath($bankAccount), ['bank_account_errors' => []])
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
        $this->createKyc($user);
        $bankAccount = $this->createBankAccount($user);

        $this->getJson($this->bankAccountPath($bankAccount))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE);

        $this->putJson($this->bankAccountPath($bankAccount), ['bank_account_errors' => []])
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
            ->assertJsonPath('data.bankAccounts', [])
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

        $user = $this->createUser(['name' => 'Bank Owner']);
        $this->createKyc($user, [
            'fname' => 'Ali',
            'lname' => 'Rezaei',
        ]);
        $this->createBankAccount($user, [
            'bank_name' => 'Melli',
            'shaba_num' => 'IR123456789012345678901234',
            'card_num' => '6037991234567890',
            'status' => 0,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'bankAccounts' => [
                        [
                            'id',
                            'bank_name',
                            'shaba_num',
                            'card_num',
                            'status',
                            'status_badge',
                            'errors',
                            'created_at',
                            'bankable' => [
                                'id',
                                'name',
                                'fname',
                                'lname',
                            ],
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
            ])
            ->assertJsonPath('data.bankAccounts.0.bankable.name', 'Bank Owner')
            ->assertJsonPath('data.bankAccounts.0.bankable.fname', 'Ali')
            ->assertJsonPath('data.bankAccounts.0.bankable.lname', 'Rezaei');
    }

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    public function test_search_filters_by_card_num_partial_match(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createBankAccount($user, [
            'card_num' => '6037991234567890',
            'shaba_num' => 'IR111111111111111111111111',
            'bank_name' => 'Match Card',
        ]);
        $this->createBankAccount($user, [
            'card_num' => '5022298765432109',
            'shaba_num' => 'IR222222222222222222222222',
            'bank_name' => 'Miss Card',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=123456')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.bankAccounts.0.bank_name', 'Match Card')
            ->assertJsonPath('data.bankAccounts.0.card_num', '6037991234567890');
    }

    public function test_search_filters_by_shaba_num_partial_match(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createBankAccount($user, [
            'card_num' => '6037991111111111',
            'shaba_num' => 'IR987654321098765432109876',
            'bank_name' => 'Match Shaba',
        ]);
        $this->createBankAccount($user, [
            'card_num' => '5022292222222222',
            'shaba_num' => 'IR111111111111111111111111',
            'bank_name' => 'Miss Shaba',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=987654')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.bankAccounts.0.bank_name', 'Match Shaba')
            ->assertJsonPath('data.bankAccounts.0.shaba_num', 'IR987654321098765432109876');
    }

    public function test_search_trims_whitespace_around_term(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createBankAccount($user, [
            'card_num' => '6037995566778899',
            'bank_name' => 'Trimmed',
        ]);

        $this->getJson(self::INDEX_PATH.'?search='.urlencode('  556677  '))
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.bankAccounts.0.bank_name', 'Trimmed');
    }

    public function test_search_with_no_matches_returns_empty_collection(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createBankAccount($user, ['card_num' => '6037991111111111']);

        $this->getJson(self::INDEX_PATH.'?search=NoMatchNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.bankAccounts', []);
    }

    public function test_empty_search_behaves_like_no_filter(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createBankAccount($user, ['card_num' => '6037991111111111']);
        $this->createBankAccount($user, ['card_num' => '6037992222222222']);

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
            $this->createBankAccount($user, ['card_num' => "603799100000000{$i}"]);
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
            $this->createBankAccount($user, ['card_num' => "603799200000000{$i}"]);
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
            ->assertJsonCount(2, 'data.bankAccounts');
    }

    // -------------------------------------------------------------------------
    // Ordering
    // -------------------------------------------------------------------------

    public function test_bank_accounts_are_ordered_by_created_at_desc(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();

        $older = $this->createBankAccount($user, ['bank_name' => 'Older', 'card_num' => '6037993000000001']);
        $older->forceFill(['created_at' => now()->subDays(2)])->save();

        $newer = $this->createBankAccount($user, ['bank_name' => 'Newer', 'card_num' => '6037993000000002']);
        $newer->forceFill(['created_at' => now()->subHour()])->save();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.bankAccounts.0.id', $newer->id)
            ->assertJsonPath('data.bankAccounts.1.id', $older->id);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_returns_not_found_for_missing_id(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson($this->bankAccountPath(99999))->assertNotFound();
    }

    public function test_show_returns_full_json_structure_with_bankable_and_kyc(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Bank Account User']);
        $this->createKyc($user, [
            'fname' => 'Sara',
            'lname' => 'Ahmadi',
        ]);
        $bankAccount = $this->createBankAccount($user, [
            'bank_name' => 'Tejarat',
            'shaba_num' => 'IR012345678901234567890123',
            'card_num' => '6274123456789012',
            'status' => 0,
        ]);

        $this->getJson($this->bankAccountPath($bankAccount))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SHOW_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'bank_name',
                    'shaba_num',
                    'card_num',
                    'status',
                    'status_badge',
                    'errors',
                    'created_at',
                    'bankable' => ['id', 'name', 'fname', 'lname'],
                ],
            ])
            ->assertJsonPath('data.id', $bankAccount->id)
            ->assertJsonPath('data.bank_name', 'Tejarat')
            ->assertJsonPath('data.bankable.id', $user->id)
            ->assertJsonPath('data.bankable.name', 'Bank Account User')
            ->assertJsonPath('data.bankable.fname', 'Sara')
            ->assertJsonPath('data.bankable.lname', 'Ahmadi');
    }

    public function test_show_formats_created_at_as_jalali(): void
    {
        $this->actingAsSuperAdmin();

        Carbon::setTestNow('2024-06-15 14:30:00');

        $user = $this->createUser();
        $bankAccount = $this->createBankAccount($user);
        $bankAccount->forceFill(['created_at' => Carbon::parse('2024-06-15 14:30:00')])->save();

        $createdAtJalali = Jalalian::fromCarbon(Carbon::parse('2024-06-15 14:30:00'))->format('Y/m/d');

        $this->getJson($this->bankAccountPath($bankAccount))
            ->assertOk()
            ->assertJsonPath('data.created_at', $createdAtJalali);

        Carbon::setTestNow();
    }

    public function test_show_includes_status_badge_for_pending_bank_account(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $bankAccount = $this->createBankAccount($user, ['status' => 0]);

        $this->getJson($this->bankAccountPath($bankAccount))
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

        $this->putJson($this->bankAccountPath(99999), ['bank_account_errors' => []])->assertNotFound();
    }

    public function test_update_with_empty_bank_account_errors_approves_bank_account(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $user = $this->createUser();
        $bankAccount = $this->createBankAccount($user, [
            'status' => 0,
            'errors' => ['card_num_err' => 'old error'],
        ]);

        $this->putJson($this->bankAccountPath($bankAccount), ['bank_account_errors' => []])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.status', 1)
            ->assertJsonPath('data.errors', null);

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $bankAccount->id,
            'status' => 1,
        ], 'sqlite');

        $this->assertNull($bankAccount->fresh()->errors);
        Notification::assertNothingSent();
    }

    public function test_update_without_bank_account_errors_key_approves_bank_account(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $user = $this->createUser();
        $bankAccount = $this->createBankAccount($user, ['status' => 0]);

        $this->putJson($this->bankAccountPath($bankAccount), [])
            ->assertOk()
            ->assertJsonPath('data.status', 1)
            ->assertJsonPath('data.errors', null);

        Notification::assertNothingSent();
    }

    public function test_update_with_non_empty_bank_account_errors_rejects_bank_account(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $user = $this->createUser();
        $bankAccount = $this->createBankAccount($user, ['status' => 0]);
        $errors = [
            'bank_name_err' => 'نام بانک نامعتبر است',
            'shaba_num_err' => 'شماره شبا نامعتبر است',
            'card_num_err' => 'شماره کارت نامعتبر است',
        ];

        $this->putJson($this->bankAccountPath($bankAccount), ['bank_account_errors' => $errors])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.status', -1)
            ->assertJsonPath('data.errors', $errors);

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $bankAccount->id,
            'status' => -1,
        ], 'sqlite');

        $this->assertSame($errors, $bankAccount->fresh()->errors);
    }

    public function test_update_reject_sends_kyc_denied_notification_to_user(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $user = $this->createUser();
        $bankAccount = $this->createBankAccount($user, ['status' => 0]);
        $errors = ['card_num_err' => 'شماره کارت نامعتبر است'];

        $this->putJson($this->bankAccountPath($bankAccount), ['bank_account_errors' => $errors])->assertOk();

        Notification::assertSentTo(
            $user,
            KycDeniedNotification::class,
            function (KycDeniedNotification $notification) use ($user) {
                $payload = $notification->toArray($user);

                return ($payload['message'] ?? null) === 'حساب بانکی تایید نشد.'
                    && ($payload['related-to'] ?? null) === 'kyc';
            }
        );
    }

    public function test_update_approve_does_not_send_notification(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $user = $this->createUser();
        $bankAccount = $this->createBankAccount($user, ['status' => 0]);

        $this->putJson($this->bankAccountPath($bankAccount), ['bank_account_errors' => []])->assertOk();

        Notification::assertNothingSent();
    }

    public function test_update_returns_fresh_data_after_status_change(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createKyc($user, ['fname' => 'Before', 'lname' => 'User']);
        $bankAccount = $this->createBankAccount($user, [
            'status' => 0,
            'bank_name' => 'Melli',
        ]);

        $this->putJson($this->bankAccountPath($bankAccount), ['bank_account_errors' => []])
            ->assertOk()
            ->assertJsonPath('data.id', $bankAccount->id)
            ->assertJsonPath('data.bank_name', 'Melli')
            ->assertJsonPath('data.status', 1)
            ->assertJsonPath('data.bankable.fname', 'Before')
            ->assertJsonPath(
                'data.status_badge',
                '<span class="badge badge-success">تایید شده</span>'
            );
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_update_rejects_non_array_bank_account_errors(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $bankAccount = $this->createBankAccount($user);

        $this->putJson($this->bankAccountPath($bankAccount), ['bank_account_errors' => 'not-an-array'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['bank_account_errors']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function bankAccountPath(int|BankAccount $bankAccount): string
    {
        $id = $bankAccount instanceof BankAccount ? $bankAccount->id : $bankAccount;

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

    private function createBankAccount(User $user, array $overrides = []): BankAccount
    {
        return BankAccount::create(array_merge([
            'bank_name' => 'Melli',
            'shaba_num' => 'IR'.Str::random(22),
            'card_num' => (string) random_int(1000000000000000, 9999999999999999),
            'status' => 0,
            'errors' => null,
            'bankable_type' => User::class,
            'bankable_id' => $user->id,
        ], $overrides));
    }
}

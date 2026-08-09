<?php

namespace Tests\Feature\Deposit;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesDepositApiSchema;
use Tests\TestCase;

class DepositApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesDepositApiSchema;

    private const INDEX_PATH = '/api/deposits';

    private const EXPORT_PATH = '/api/deposits/export';

    private const SUCCESS_MESSAGE = 'Deposits retrieved successfully.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDepositApiSchema();
    }

    // -------------------------------------------------------------------------
    // Auth — index
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_authenticated_super_admin_receives_success_envelope(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_receives_success_envelope(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Happy path / structure
    // -------------------------------------------------------------------------

    public function test_empty_dataset_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::SUCCESS_MESSAGE)
            ->assertJsonPath('data.payments', [])
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

        $user = $this->createUser(['name' => 'Deposit Owner']);
        $this->createPayment($user);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('message', self::SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'payments' => [
                        [
                            'id',
                            'user_id',
                            'user_name',
                            'amount',
                            'ref_id',
                            'card_pan',
                            'gateway',
                            'product_title',
                            'product',
                            'created_at',
                            'created_at_time',
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
    // Data mapping
    // -------------------------------------------------------------------------

    public function test_returns_correct_user_name_from_related_user(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Ali Depositor']);
        $payment = $this->createPayment($user);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.payments.0.id', $payment->id)
            ->assertJsonPath('data.payments.0.user_name', 'Ali Depositor');
    }

    public function test_missing_user_relation_returns_dash_for_user_name(): void
    {
        $this->actingAsSuperAdmin();

        $paymentId = DB::table('payments')->insertGetId([
            'user_id' => 999999,
            'ref_id' => 'REF-MISSING-USER',
            'product' => 'irr',
            'amount' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.payments.0.id', $paymentId)
            ->assertJsonPath('data.payments.0.user_name', '-');
    }

    public function test_null_field_fallbacks_return_dashes_and_zero_amount(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $paymentId = DB::table('payments')->insertGetId([
            'user_id' => $user->id,
            'ref_id' => null,
            'card_pan' => null,
            'gate_way' => null,
            'product' => 'irr',
            'amount' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.payments.0.id', $paymentId)
            ->assertJsonPath('data.payments.0.ref_id', '-')
            ->assertJsonPath('data.payments.0.card_pan', '-')
            ->assertJsonPath('data.payments.0.gateway', '-')
            ->assertJsonPath('data.payments.0.amount', '0');
    }

    public function test_gateway_prefers_gateway_attribute_over_gate_way_column(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $paymentId = DB::table('payments')->insertGetId([
            'user_id' => $user->id,
            'gateway' => 'Zarinpal',
            'gate_way' => 'Mellat',
            'ref_id' => 'REF-GATEWAY-PREF',
            'product' => 'irr',
            'amount' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson(self::INDEX_PATH.'?search=REF-GATEWAY-PREF')
            ->assertOk()
            ->assertJsonPath('data.payments.0.id', $paymentId)
            ->assertJsonPath('data.payments.0.gateway', 'Zarinpal');
    }

    public function test_gate_way_is_used_when_gateway_attribute_is_null(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $payment = $this->createPayment($user, [
            'gate_way' => 'Saman',
            'ref_id' => 'REF-GATE-WAY',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=REF-GATE-WAY')
            ->assertOk()
            ->assertJsonPath('data.payments.0.id', $payment->id)
            ->assertJsonPath('data.payments.0.gateway', 'Saman');
    }

    public function test_product_title_for_irr(): void
    {
        $this->assertProductTitle('irr', 'ریال');
    }

    public function test_product_title_for_psc(): void
    {
        $this->assertProductTitle('psc', 'PSC');
    }

    public function test_product_title_for_red(): void
    {
        $this->assertProductTitle('red', 'رنگ قرمز');
    }

    public function test_product_title_for_blue(): void
    {
        $this->assertProductTitle('blue', 'رنگ آبی');
    }

    public function test_product_title_for_yellow(): void
    {
        $this->assertProductTitle('yellow', 'رنگ زرد');
    }

    public function test_amount_is_thousand_separated_string(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $payment = $this->createPayment($user, [
            'amount' => 1234567,
            'ref_id' => 'REF-AMOUNT-FMT',
        ]);

        $this->getJson(self::INDEX_PATH.'?search=REF-AMOUNT-FMT')
            ->assertOk()
            ->assertJsonPath('data.payments.0.id', $payment->id)
            ->assertJsonPath('data.payments.0.amount', '1,234,567');
    }

    public function test_jalali_date_formatting_for_created_at_fields(): void
    {
        $this->actingAsSuperAdmin();

        $createdAt = Carbon::parse('2024-03-20 14:30:45');
        Carbon::setTestNow($createdAt);

        $user = $this->createUser();
        $payment = $this->createPayment($user, [
            'ref_id' => 'REF-JALALI',
        ]);

        $jalali = Jalalian::fromDateTime($createdAt);

        $this->getJson(self::INDEX_PATH.'?search=REF-JALALI')
            ->assertOk()
            ->assertJsonPath('data.payments.0.id', $payment->id)
            ->assertJsonPath('data.payments.0.created_at', $jalali->format('Y/m/d'))
            ->assertJsonPath('data.payments.0.created_at_time', $jalali->format('H:i:s'));

        Carbon::setTestNow();
    }

    // -------------------------------------------------------------------------
    // Ordering
    // -------------------------------------------------------------------------

    public function test_payments_are_ordered_by_created_at_desc(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();

        $older = $this->createPayment($user, ['ref_id' => 'REF-OLDER']);
        $older->forceFill(['created_at' => now()->subDays(2)])->save();

        $newer = $this->createPayment($user, ['ref_id' => 'REF-NEWER']);
        $newer->forceFill(['created_at' => now()->subHour()])->save();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.payments.0.id', $newer->id)
            ->assertJsonPath('data.payments.1.id', $older->id);
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_default_per_page_is_ten(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();

        for ($i = 0; $i < 12; $i++) {
            $this->createPayment($user, [
                'ref_id' => 'REF-PAGE-DEFAULT-'.$i,
            ]);
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 10)
            ->assertJsonCount(10, 'data.payments');
    }

    public function test_custom_per_page_is_respected(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();

        for ($i = 0; $i < 8; $i++) {
            $this->createPayment($user, [
                'ref_id' => 'REF-CUSTOM-PAGE-'.$i,
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=5')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 5)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.total', 8)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 5)
            ->assertJsonCount(5, 'data.payments');
    }

    public function test_page_two_returns_correct_slice_and_meta(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();

        for ($i = 0; $i < 15; $i++) {
            $this->createPayment($user, [
                'ref_id' => 'REF-PAGE-TWO-'.$i,
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=5&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 5)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 15)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonPath('data.pagination.from', 6)
            ->assertJsonPath('data.pagination.to', 10)
            ->assertJsonCount(5, 'data.payments');
    }

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    public function test_search_filters_by_ref_id_partial_match(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $match = $this->createPayment($user, ['ref_id' => 'ABC-12345-XYZ']);
        $this->createPayment($user, ['ref_id' => 'OTHER-REF']);

        $this->getJson(self::INDEX_PATH.'?search=12345')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonCount(1, 'data.payments')
            ->assertJsonPath('data.payments.0.id', $match->id);
    }

    public function test_empty_search_returns_all_payments(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createPayment($user, ['ref_id' => 'REF-ONE']);
        $this->createPayment($user, ['ref_id' => 'REF-TWO']);

        $this->getJson(self::INDEX_PATH.'?search=')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2)
            ->assertJsonCount(2, 'data.payments');
    }

    public function test_non_matching_search_returns_empty_payments(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $this->createPayment($user, ['ref_id' => 'REF-EXISTING']);

        $this->getJson(self::INDEX_PATH.'?search=DOES-NOT-EXIST')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.payments', [])
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    // -------------------------------------------------------------------------
    // Auth — export
    // -------------------------------------------------------------------------

    public function test_unauthenticated_export_returns_unauthorized(): void
    {
        $this->get(self::EXPORT_PATH)->assertUnauthorized();
    }

    public function test_authenticated_super_admin_export_returns_ok(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(self::EXPORT_PATH)->assertOk();
    }

    public function test_authenticated_regular_admin_export_returns_ok(): void
    {
        $this->actingAsRegularAdmin();

        $this->get(self::EXPORT_PATH)->assertOk();
    }

    // -------------------------------------------------------------------------
    // Export
    // -------------------------------------------------------------------------

    public function test_export_downloads_transactions_xlsx_file(): void
    {
        $this->actingAsSuperAdmin();

        $this->get(self::EXPORT_PATH)
            ->assertOk()
            ->assertDownload('transactions.xlsx');
    }

    public function test_export_response_has_spreadsheet_content_type(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->get(self::EXPORT_PATH)->assertOk();

        $contentType = $response->headers->get('content-type');

        $this->assertNotNull($contentType);
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $contentType
        );
    }

    public function test_export_succeeds_when_payments_exist(): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser(['name' => 'Export Depositor']);
        $this->createPayment($user, [
            'ref_id' => 'REF-EXPORT-001',
            'amount' => 250000,
            'gate_way' => 'Zarinpal',
            'product' => 'irr',
        ]);

        $response = $this->get(self::EXPORT_PATH)
            ->assertOk()
            ->assertDownload('transactions.xlsx');

        $this->assertNotEmpty($response->streamedContent());
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function assertProductTitle(string $product, string $expectedTitle): void
    {
        $this->actingAsSuperAdmin();

        $user = $this->createUser();
        $payment = $this->createPayment($user, [
            'product' => $product,
            'ref_id' => 'REF-PRODUCT-'.$product,
        ]);

        $this->getJson(self::INDEX_PATH.'?search=REF-PRODUCT-'.$product)
            ->assertOk()
            ->assertJsonPath('data.payments.0.id', $payment->id)
            ->assertJsonPath('data.payments.0.product', $product)
            ->assertJsonPath('data.payments.0.product_title', $expectedTitle);
    }

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Deposit Citizen '.Str::random(5),
            'email' => Str::uuid().'@example.com',
            'code' => (string) random_int(1000, 9999),
            'password' => 'secret',
            'ip' => '127.0.0.1',
        ], $overrides));
    }

    private function createPayment(User $user, array $overrides = []): Payment
    {
        return Payment::create(array_merge([
            'user_id' => $user->id,
            'ref_id' => 'REF-'.Str::upper(Str::random(8)),
            'card_pan' => '6037-****-****-1234',
            'gate_way' => 'Mellat',
            'product' => 'irr',
            'amount' => 1000,
            'status' => 'success',
        ], $overrides));
    }
}

<?php

namespace Tests\Feature\IsicCode;

use App\Imports\IsicCodesImport;
use App\Models\IsicCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesIsicCodeApiSchema;
use Tests\TestCase;

class IsicCodeApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesIsicCodeApiSchema;

    private const INDEX_PATH = '/api/isic-codes';

    private const IMPORT_PATH = '/api/isic-codes/import';

    private const INDEX_SUCCESS_MESSAGE = 'لیست کدهای ISIC با موفقیت دریافت شد.';

    private const STORE_SUCCESS_MESSAGE = 'کد ISIC جدید با موفقیت ایجاد شد.';

    private const IMPORT_SUCCESS_MESSAGE = 'درون‌ریزی کدهای ISIC در صف پردازش قرار گرفت.';

    private const IMPORT_ERROR_MESSAGE = 'بروز خطا در پردازش فایل درون‌ریزی.';

    private const APPROVE_SUCCESS_MESSAGE = 'کد ISIC با موفقیت تایید شد.';

    private const DENY_SUCCESS_MESSAGE = 'کد ISIC در وضعیت انتظار تایید قرار گرفت.';

    private const DESTROY_SUCCESS_MESSAGE = 'کد ISIC با موفقیت حذف شد.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpIsicCodeApiSchema();
        Storage::fake('public');
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_store_returns_unauthorized(): void
    {
        $this->postJson(self::INDEX_PATH, $this->validStorePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_import_returns_unauthorized(): void
    {
        $this->postJson(self::IMPORT_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_approve_returns_unauthorized(): void
    {
        $isicCode = $this->createIsicCode();

        $this->postJson($this->approvePath($isicCode))->assertUnauthorized();
    }

    public function test_unauthenticated_deny_returns_unauthorized(): void
    {
        $isicCode = $this->createIsicCode();

        $this->postJson($this->denyPath($isicCode))->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $isicCode = $this->createIsicCode();

        $this->deleteJson($this->isicCodePath($isicCode))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'Super Admin ISIC',
            'code' => '1111',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        Excel::shouldReceive('queueImport')->once()->andReturnNull();

        $this->postImport($this->fakeXlsx())
            ->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::IMPORT_SUCCESS_MESSAGE);

        $toApprove = $this->createIsicCode(['verified' => false, 'code' => 2222]);

        $this->postJson($this->approvePath($toApprove))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::APPROVE_SUCCESS_MESSAGE);

        $toDeny = $this->createIsicCode(['verified' => true, 'code' => 3333]);

        $this->postJson($this->denyPath($toDeny))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DENY_SUCCESS_MESSAGE);

        $toDelete = $this->createIsicCode();

        $this->deleteJson($this->isicCodePath($toDelete))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'Regular Admin ISIC',
            'code' => '4444',
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true);

        Excel::shouldReceive('queueImport')->once()->andReturnNull();

        $this->postImport($this->fakeXlsx())
            ->assertStatus(202)
            ->assertJsonPath('success', true);

        $toApprove = $this->createIsicCode(['verified' => false]);

        $this->postJson($this->approvePath($toApprove))->assertOk();

        $toDeny = $this->createIsicCode(['verified' => true]);

        $this->postJson($this->denyPath($toDeny))->assertOk();

        $toDelete = $this->createIsicCode();

        $this->deleteJson($this->isicCodePath($toDelete))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_empty_dataset_returns_empty_collection_and_pagination_meta(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data.isic_codes', [])
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

        $this->createIsicCode([
            'name' => 'Structure Code',
            'code' => 123456,
            'verified' => true,
        ]);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'isic_codes' => [
                        [
                            'id',
                            'name',
                            'code',
                            'verified',
                            'created_at',
                            'updated_at',
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

    public function test_search_by_name(): void
    {
        $this->actingAsSuperAdmin();

        $this->createIsicCode(['name' => 'UniqueNameNeedle', 'code' => 1001]);
        $this->createIsicCode(['name' => 'Other Name', 'code' => 1002]);

        $this->getJson(self::INDEX_PATH.'?search=UniqueNameNeedle')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.isic_codes.0.name', 'UniqueNameNeedle');
    }

    public function test_search_by_code(): void
    {
        $this->actingAsSuperAdmin();

        $this->createIsicCode(['name' => 'Code Match', 'code' => 9876543210]);
        $this->createIsicCode(['name' => 'Other', 'code' => 1111]);

        $this->getJson(self::INDEX_PATH.'?search=9876543210')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.isic_codes.0.code', 9876543210);
    }

    public function test_search_trims_whitespace(): void
    {
        $this->actingAsSuperAdmin();

        $this->createIsicCode(['name' => 'TrimTarget', 'code' => 2001]);
        $this->createIsicCode(['name' => 'Other', 'code' => 2002]);

        $this->getJson(self::INDEX_PATH.'?search='.urlencode('  TrimTarget  '))
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.isic_codes.0.name', 'TrimTarget');
    }

    public function test_index_orders_unverified_before_verified_then_by_name(): void
    {
        $this->actingAsSuperAdmin();

        $verifiedAlpha = $this->createIsicCode([
            'name' => 'Alpha',
            'code' => 1,
            'verified' => true,
        ]);
        $verifiedBeta = $this->createIsicCode([
            'name' => 'Beta',
            'code' => 2,
            'verified' => true,
        ]);
        $unverifiedZulu = $this->createIsicCode([
            'name' => 'Zulu',
            'code' => 3,
            'verified' => false,
        ]);

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $this->assertSame($unverifiedZulu->id, $response->json('data.isic_codes.0.id'));
        $this->assertSame($verifiedAlpha->id, $response->json('data.isic_codes.1.id'));
        $this->assertSame($verifiedBeta->id, $response->json('data.isic_codes.2.id'));
    }

    public function test_default_per_page_is_ten(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 12; $i++) {
            $this->createIsicCode([
                'name' => "Code {$i}",
                'code' => 1000 + $i,
            ]);
        }

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonCount(10, 'data.isic_codes');
    }

    public function test_custom_per_page_and_page_are_respected(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->createIsicCode([
                'name' => "Paged {$i}",
                'code' => 2000 + $i,
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonCount(2, 'data.isic_codes');
    }

    public function test_per_page_above_fifty_is_capped(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 55; $i++) {
            $this->createIsicCode([
                'name' => "Cap {$i}",
                'code' => 3000 + $i,
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=100')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 50)
            ->assertJsonCount(50, 'data.isic_codes')
            ->assertJsonPath('data.pagination.total', 55)
            ->assertJsonPath('data.pagination.last_page', 2);
    }

    public function test_per_page_zero_or_invalid_falls_back_to_ten(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 11; $i++) {
            $this->createIsicCode([
                'name' => "Fallback {$i}",
                'code' => 4000 + $i,
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?per_page=0')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonCount(10, 'data.isic_codes');

        $this->getJson(self::INDEX_PATH.'?per_page=-5')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10);

        $this->getJson(self::INDEX_PATH.'?per_page=invalid')
            ->assertOk()
            ->assertJsonPath('data.pagination.per_page', 10);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_isic_code_and_returns_resource(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'New Industry',
            'code' => '55555',
            'verified' => true,
        ]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.isic_code.name', 'New Industry')
            ->assertJsonPath('data.isic_code.code', '55555')
            ->assertJsonPath('data.isic_code.verified', true);

        $this->assertDatabaseHas('isic_codes', [
            'name' => 'New Industry',
            'code' => 55555,
            'verified' => true,
        ]);

        $this->assertNotNull($response->json('data.isic_code.id'));
        $this->assertArrayHasKey('created_at', $response->json('data.isic_code'));
        $this->assertArrayHasKey('updated_at', $response->json('data.isic_code'));
    }

    public function test_store_defaults_verified_to_true_when_omitted(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [
            'name' => 'Default Verified',
            'code' => '66666',
        ])
            ->assertCreated()
            ->assertJsonPath('data.isic_code.verified', true);

        $this->assertDatabaseHas('isic_codes', [
            'name' => 'Default Verified',
            'verified' => true,
        ]);
    }

    public function test_store_accepts_explicit_verified_false(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'Unverified Store',
            'code' => '77777',
            'verified' => false,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.isic_code.verified', false);

        $this->assertDatabaseHas('isic_codes', [
            'name' => 'Unverified Store',
            'verified' => false,
        ]);
    }

    public function test_store_strips_non_digits_from_code(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'Stripped Code',
            'code' => '12-34',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.isic_code.code', '1234');

        $this->assertDatabaseHas('isic_codes', [
            'name' => 'Stripped Code',
            'code' => 1234,
        ]);
    }

    public function test_store_trims_name(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'name' => '  Trimmed Name  ',
            'code' => '88888',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.isic_code.name', 'Trimmed Name');

        $this->assertDatabaseHas('isic_codes', [
            'name' => 'Trimmed Name',
        ]);
    }

    public function test_store_missing_name_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [
            'code' => '1234',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_missing_code_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [
            'name' => 'No Code',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_store_non_digit_code_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [
            'name' => 'Bad Code',
            'code' => 'abcdef',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_store_name_too_long_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'name' => str_repeat('a', 256),
            'code' => '99999',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // -------------------------------------------------------------------------
    // Import
    // -------------------------------------------------------------------------

    public function test_import_missing_file_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::IMPORT_PATH, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_import_invalid_mime_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $txt = UploadedFile::fake()->create('codes.txt', 100, 'text/plain');
        $pdf = UploadedFile::fake()->create('codes.pdf', 100, 'application/pdf');

        $this->postImport($txt)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);

        $this->postImport($pdf)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_import_valid_xlsx_queues_import_and_returns_accepted(): void
    {
        $this->actingAsSuperAdmin();

        Excel::shouldReceive('queueImport')
            ->once()
            ->withArgs(function ($import, string $path, string $disk): bool {
                return $import instanceof IsicCodesImport
                    && str_contains($path, 'isic_codes/imports')
                    && $disk === 'public';
            })
            ->andReturnNull();

        $this->postImport($this->fakeXlsx())
            ->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::IMPORT_SUCCESS_MESSAGE);

        $files = Storage::disk('public')->allFiles('isic_codes/imports');
        $this->assertNotEmpty($files);
    }

    public function test_import_valid_xls_queues_import_and_returns_accepted(): void
    {
        $this->actingAsSuperAdmin();

        Excel::shouldReceive('queueImport')
            ->once()
            ->andReturnNull();

        $xls = UploadedFile::fake()->create(
            'codes.xls',
            100,
            'application/vnd.ms-excel'
        );

        $this->postImport($xls)
            ->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::IMPORT_SUCCESS_MESSAGE);
    }

    public function test_import_exception_returns_server_error_and_logs(): void
    {
        $this->actingAsSuperAdmin();
        Log::spy();

        Excel::shouldReceive('queueImport')
            ->once()
            ->andThrow(new \RuntimeException('Unable to queue import'));

        $this->postImport($this->fakeXlsx())
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::IMPORT_ERROR_MESSAGE);

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'ISIC codes import failed'
                    && isset($context['exception'])
                    && $context['exception'] instanceof \RuntimeException;
            });
    }

    public function test_import_stores_file_on_public_disk_under_imports_path(): void
    {
        $this->actingAsSuperAdmin();

        Excel::shouldReceive('queueImport')->once()->andReturnNull();

        $this->postImport($this->fakeXlsx())->assertStatus(202);

        $files = Storage::disk('public')->allFiles('isic_codes/imports');
        $this->assertCount(1, $files);
        $this->assertStringContainsString('isic_codes/imports', $files[0]);
    }

    // -------------------------------------------------------------------------
    // Approve
    // -------------------------------------------------------------------------

    public function test_approve_sets_verified_and_regenerates_seven_digit_code(): void
    {
        $this->actingAsSuperAdmin();

        $isicCode = $this->createIsicCode([
            'name' => 'To Approve',
            'code' => 42,
            'verified' => false,
        ]);

        $response = $this->postJson($this->approvePath($isicCode))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::APPROVE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.isic_code.verified', true);

        $newCode = $response->json('data.isic_code.code');

        $this->assertIsInt($newCode);
        $this->assertGreaterThanOrEqual(1_000_000, $newCode);
        $this->assertLessThanOrEqual(9_999_999, $newCode);
        $this->assertSame(7, strlen((string) $newCode));
        $this->assertNotSame(42, $newCode);

        $this->assertDatabaseHas('isic_codes', [
            'id' => $isicCode->id,
            'verified' => true,
            'code' => $newCode,
        ]);
    }

    public function test_approve_nonexistent_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH.'/999999/approve')
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Deny
    // -------------------------------------------------------------------------

    public function test_deny_sets_verified_false_and_preserves_code(): void
    {
        $this->actingAsSuperAdmin();

        $isicCode = $this->createIsicCode([
            'name' => 'To Deny',
            'code' => 7654321,
            'verified' => true,
        ]);

        $this->postJson($this->denyPath($isicCode))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DENY_SUCCESS_MESSAGE)
            ->assertJsonPath('data.isic_code.verified', false)
            ->assertJsonPath('data.isic_code.code', 7654321);

        $this->assertDatabaseHas('isic_codes', [
            'id' => $isicCode->id,
            'verified' => false,
            'code' => 7654321,
        ]);
    }

    public function test_deny_nonexistent_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH.'/999999/deny')
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_isic_code(): void
    {
        $this->actingAsSuperAdmin();

        $isicCode = $this->createIsicCode(['name' => 'To Delete']);

        $this->deleteJson($this->isicCodePath($isicCode))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('isic_codes', ['id' => $isicCode->id]);
    }

    public function test_destroy_nonexistent_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson(self::INDEX_PATH.'/999999')
            ->assertNotFound();
    }

    public function test_destroy_does_not_delete_other_isic_codes(): void
    {
        $this->actingAsSuperAdmin();

        $target = $this->createIsicCode(['name' => 'Target']);
        $other = $this->createIsicCode(['name' => 'Other']);

        $this->deleteJson($this->isicCodePath($target))->assertOk();

        $this->assertDatabaseMissing('isic_codes', ['id' => $target->id]);
        $this->assertDatabaseHas('isic_codes', ['id' => $other->id]);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function test_verified_is_cast_as_boolean_in_json_responses(): void
    {
        $this->actingAsSuperAdmin();

        $this->createIsicCode([
            'name' => 'Bool Check',
            'code' => 55,
            'verified' => false,
        ]);

        $response = $this->getJson(self::INDEX_PATH)->assertOk();

        $this->assertFalse($response->json('data.isic_codes.0.verified'));
        $this->assertIsBool($response->json('data.isic_codes.0.verified'));

        $created = $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'name' => 'Bool Store',
            'code' => '66',
            'verified' => true,
        ]))->assertCreated();

        $this->assertTrue($created->json('data.isic_code.verified'));
        $this->assertIsBool($created->json('data.isic_code.verified'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function isicCodePath(IsicCode $isicCode): string
    {
        return self::INDEX_PATH.'/'.$isicCode->id;
    }

    private function approvePath(IsicCode $isicCode): string
    {
        return $this->isicCodePath($isicCode).'/approve';
    }

    private function denyPath(IsicCode $isicCode): string
    {
        return $this->isicCodePath($isicCode).'/deny';
    }

    /**
     * @return array<string, mixed>
     */
    private function validStorePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test ISIC Code',
            'code' => '123456',
            'verified' => true,
        ], $overrides);
    }

    private function fakeXlsx(): UploadedFile
    {
        return UploadedFile::fake()->create(
            'codes.xlsx',
            100,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    private function postImport(UploadedFile $file)
    {
        return $this->post(self::IMPORT_PATH, [
            'file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);
    }
}

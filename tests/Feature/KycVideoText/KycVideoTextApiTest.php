<?php

namespace Tests\Feature\KycVideoText;

use App\Models\KycVerifyText;
use Illuminate\Support\Carbon;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesKycVideoTextApiSchema;
use Tests\TestCase;

class KycVideoTextApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesKycVideoTextApiSchema;

    private const INDEX_PATH = '/api/kyc-video-texts';

    private const INDEX_SUCCESS_MESSAGE = 'KYC video texts retrieved successfully.';

    private const STORE_SUCCESS_MESSAGE = 'متن احراز ویدیویی با موفقیت ثبت شد.';

    private const UPDATE_SUCCESS_MESSAGE = 'متن احراز ویدیویی با موفقیت به‌روزرسانی شد.';

    private const DESTROY_SUCCESS_MESSAGE = 'متن احراز ویدیویی با موفقیت حذف شد.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpKycVideoTextApiSchema();
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
        $this->postJson(self::INDEX_PATH, $this->validStorePayload())->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $this->putJson($this->videoTextPath(1), $this->validUpdatePayload())->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $this->deleteJson($this->videoTextPath(1))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'text' => 'Super admin video text',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $videoText = $this->createVideoText(['text' => 'Updatable text']);

        $this->putJson($this->videoTextPath($videoText), $this->validUpdatePayload([
            'text' => 'Updated by super admin',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $toDelete = $this->createVideoText(['text' => 'To delete']);

        $this->deleteJson($this->videoTextPath($toDelete))
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
            'text' => 'Regular admin video text',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $videoText = $this->createVideoText(['text' => 'Regular updatable']);

        $this->putJson($this->videoTextPath($videoText), $this->validUpdatePayload([
            'text' => 'Updated by regular admin',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $toDelete = $this->createVideoText(['text' => 'Regular delete target']);

        $this->deleteJson($this->videoTextPath($toDelete))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
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
            ->assertJsonPath('data', [])
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.last_page', 1)
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 0)
            ->assertJsonPath('pagination.from', null)
            ->assertJsonPath('pagination.to', null)
            ->assertJsonPath('pagination.has_more', false);
    }

    public function test_index_returns_full_json_structure(): void
    {
        $this->actingAsSuperAdmin();

        $this->createVideoText(['text' => 'Structure test text']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    [
                        'id',
                        'text',
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
                    'has_more',
                ],
            ]);
    }

    public function test_index_resource_fields_match_created_text(): void
    {
        $this->actingAsSuperAdmin();

        $videoText = $this->createVideoText(['text' => 'متن احراز هویت ویدیویی']);

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.0.id', $videoText->id)
            ->assertJsonPath('data.0.text', 'متن احراز هویت ویدیویی')
            ->assertJsonPath('data.0.created_at', $videoText->created_at->toJSON())
            ->assertJsonPath('data.0.updated_at', $videoText->updated_at->toJSON());
    }

    public function test_index_orders_records_by_latest_first(): void
    {
        $this->actingAsSuperAdmin();

        Carbon::setTestNow('2024-01-01 10:00:00');
        $older = $this->createVideoText(['text' => 'Older text']);

        Carbon::setTestNow('2024-01-01 12:00:00');
        $newer = $this->createVideoText(['text' => 'Newer text']);

        Carbon::setTestNow('2024-01-01 11:00:00');
        $middle = $this->createVideoText(['text' => 'Middle text']);

        Carbon::setTestNow();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $middle->id)
            ->assertJsonPath('data.2.id', $older->id);
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_pagination_respects_per_page_and_page(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 1; $i <= 5; $i++) {
            $this->createVideoText(['text' => "Video text {$i}"]);
        }

        $this->getJson(self::INDEX_PATH.'?'.http_build_query([
            'per_page' => 2,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertJsonPath('pagination.current_page', 2)
            ->assertJsonPath('pagination.per_page', 2)
            ->assertJsonPath('pagination.total', 5)
            ->assertJsonPath('pagination.last_page', 3)
            ->assertJsonCount(2, 'data');
    }

    public function test_pagination_defaults_to_page_one_and_ten_per_page(): void
    {
        $this->actingAsSuperAdmin();

        KycVerifyText::factory()->count(3)->create();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 3)
            ->assertJsonPath('pagination.from', 1)
            ->assertJsonPath('pagination.to', 3);
    }

    public function test_pagination_has_more_is_true_when_more_pages_exist(): void
    {
        $this->actingAsSuperAdmin();

        KycVerifyText::factory()->count(5)->create();

        $this->getJson(self::INDEX_PATH.'?'.http_build_query([
            'per_page' => 2,
            'page' => 1,
        ]))
            ->assertOk()
            ->assertJsonPath('pagination.has_more', true)
            ->assertJsonPath('pagination.from', 1)
            ->assertJsonPath('pagination.to', 2);
    }

    public function test_pagination_has_more_is_false_on_last_page(): void
    {
        $this->actingAsSuperAdmin();

        KycVerifyText::factory()->count(5)->create();

        $this->getJson(self::INDEX_PATH.'?'.http_build_query([
            'per_page' => 2,
            'page' => 3,
        ]))
            ->assertOk()
            ->assertJsonPath('pagination.has_more', false)
            ->assertJsonPath('pagination.from', 5)
            ->assertJsonPath('pagination.to', 5)
            ->assertJsonCount(1, 'data');
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_record_and_returns_resource_with_persian_message(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload([
            'text' => 'لطفاً این جمله را بخوانید',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.text', 'لطفاً این جمله را بخوانید')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'text',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('kyc_verify_texts', [
            'text' => 'لطفاً این جمله را بخوانید',
        ]);
    }

    public function test_store_persists_exact_unicode_text(): void
    {
        $this->actingAsSuperAdmin();

        $unicodeText = 'متن 🇮🇷 با emoji و کاراکترهای خاص «»';

        $this->postJson(self::INDEX_PATH, ['text' => $unicodeText])
            ->assertOk()
            ->assertJsonPath('data.text', $unicodeText);

        $this->assertDatabaseHas('kyc_verify_texts', ['text' => $unicodeText]);
    }

    public function test_store_returns_200_ok_response_structure(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validStorePayload())
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'text',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // Store validation
    // -------------------------------------------------------------------------

    public function test_store_requires_text(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text']);

        $this->assertDatabaseCount('kyc_verify_texts', 0);
    }

    public function test_store_rejects_empty_text(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, ['text' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text']);

        $this->assertDatabaseCount('kyc_verify_texts', 0);
    }

    public function test_store_rejects_null_text(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, ['text' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text']);

        $this->assertDatabaseCount('kyc_verify_texts', 0);
    }

    public function test_store_rejects_non_string_text(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, ['text' => ['not', 'a', 'string']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text']);

        $this->assertDatabaseCount('kyc_verify_texts', 0);
    }

    public function test_store_does_not_create_record_when_validation_fails(): void
    {
        $this->actingAsSuperAdmin();

        $this->createVideoText(['text' => 'Existing text']);

        $this->postJson(self::INDEX_PATH, ['text' => ''])
            ->assertStatus(422);

        $this->assertDatabaseCount('kyc_verify_texts', 1);
        $this->assertDatabaseHas('kyc_verify_texts', ['text' => 'Existing text']);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_changes_text_and_returns_resource_with_persian_message(): void
    {
        $this->actingAsSuperAdmin();

        $videoText = $this->createVideoText(['text' => 'Original text']);

        $this->putJson($this->videoTextPath($videoText), $this->validUpdatePayload([
            'text' => 'Updated video text',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.id', $videoText->id)
            ->assertJsonPath('data.text', 'Updated video text')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'text',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_update_persists_change_in_database(): void
    {
        $this->actingAsSuperAdmin();

        $videoText = $this->createVideoText(['text' => 'Before update']);

        $this->putJson($this->videoTextPath($videoText), ['text' => 'After update'])
            ->assertOk();

        $this->assertDatabaseHas('kyc_verify_texts', [
            'id' => $videoText->id,
            'text' => 'After update',
        ]);
        $this->assertDatabaseMissing('kyc_verify_texts', [
            'id' => $videoText->id,
            'text' => 'Before update',
        ]);
    }

    public function test_update_nonexistent_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->videoTextPath(999999), $this->validUpdatePayload())
            ->assertNotFound();
    }

    public function test_update_requires_text(): void
    {
        $this->actingAsSuperAdmin();

        $videoText = $this->createVideoText(['text' => 'Unchanged']);

        $this->putJson($this->videoTextPath($videoText), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text']);

        $this->assertDatabaseHas('kyc_verify_texts', [
            'id' => $videoText->id,
            'text' => 'Unchanged',
        ]);
    }

    public function test_update_rejects_empty_text(): void
    {
        $this->actingAsSuperAdmin();

        $videoText = $this->createVideoText(['text' => 'Unchanged']);

        $this->putJson($this->videoTextPath($videoText), ['text' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text']);

        $this->assertDatabaseHas('kyc_verify_texts', [
            'id' => $videoText->id,
            'text' => 'Unchanged',
        ]);
    }

    public function test_update_rejects_non_string_text(): void
    {
        $this->actingAsSuperAdmin();

        $videoText = $this->createVideoText(['text' => 'Unchanged']);

        $this->putJson($this->videoTextPath($videoText), ['text' => ['invalid']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['text']);

        $this->assertDatabaseHas('kyc_verify_texts', [
            'id' => $videoText->id,
            'text' => 'Unchanged',
        ]);
    }

    public function test_update_does_not_affect_other_records(): void
    {
        $this->actingAsSuperAdmin();

        $target = $this->createVideoText(['text' => 'Target text']);
        $other = $this->createVideoText(['text' => 'Other text']);

        $this->putJson($this->videoTextPath($target), ['text' => 'Modified target'])
            ->assertOk();

        $this->assertDatabaseHas('kyc_verify_texts', [
            'id' => $target->id,
            'text' => 'Modified target',
        ]);
        $this->assertDatabaseHas('kyc_verify_texts', [
            'id' => $other->id,
            'text' => 'Other text',
        ]);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_hard_deletes_record(): void
    {
        $this->actingAsSuperAdmin();

        $videoText = $this->createVideoText(['text' => 'To delete']);

        $this->deleteJson($this->videoTextPath($videoText))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE)
            ->assertJsonMissing(['data']);

        $this->assertDatabaseMissing('kyc_verify_texts', ['id' => $videoText->id]);
    }

    public function test_destroy_nonexistent_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson($this->videoTextPath(999999))->assertNotFound();
    }

    public function test_destroy_does_not_delete_other_records(): void
    {
        $this->actingAsSuperAdmin();

        $target = $this->createVideoText(['text' => 'Delete me']);
        $other = $this->createVideoText(['text' => 'Keep me']);

        $this->deleteJson($this->videoTextPath($target))->assertOk();

        $this->assertDatabaseMissing('kyc_verify_texts', ['id' => $target->id]);
        $this->assertDatabaseHas('kyc_verify_texts', [
            'id' => $other->id,
            'text' => 'Keep me',
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function videoTextPath(int|KycVerifyText $videoText): string
    {
        $id = $videoText instanceof KycVerifyText ? $videoText->id : $videoText;

        return self::INDEX_PATH.'/'.$id;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validStorePayload(array $overrides = []): array
    {
        return array_merge([
            'text' => 'Default KYC video verification text',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'text' => 'Updated KYC video verification text',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createVideoText(array $overrides = []): KycVerifyText
    {
        return KycVerifyText::factory()->create($overrides);
    }
}

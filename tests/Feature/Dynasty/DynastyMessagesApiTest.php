<?php

namespace Tests\Feature\Dynasty;

use App\Models\Dynasty\DynastyMessage;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesDynastyApiSchema;
use Tests\TestCase;

class DynastyMessagesApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesDynastyApiSchema;

    private const INDEX_PATH = '/api/dynasty/messages';

    private const INDEX_SUCCESS_MESSAGE = 'پیام‌های سلسله با موفقیت بارگذاری شدند.';

    private const STORE_SUCCESS_MESSAGE = 'اطلاعات با موفقیت ثبت شد';

    private const UPDATE_SUCCESS_MESSAGE = 'اطلاعات با موفقیت ثبت شد';

    private const DESTROY_SUCCESS_MESSAGE = 'پیام با موفقیت حذف شد';

    private const NOT_FOUND_MESSAGE = 'پیام یافت نشد';

    /** @var array<string, string> */
    private const TYPE_TITLES = [
        'requester_confirmation_message' => 'پیام تایید درخواست کننده',
        'reciever_message' => 'پیام دریافت کننده درخواست',
        'reciever_accept_message' => 'پیام تایید پذیرش پیوستن به سلسله',
        'requester_accept_message' => 'پیام ارسالی به درخواست کننده مبنی بر پذیرش درخواست',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDynastyApiSchema();
    }

    private function messagePath(int|DynastyMessage $message): string
    {
        $id = $message instanceof DynastyMessage ? $message->id : $message;

        return self::INDEX_PATH.'/'.$id;
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
        $this->postJson(self::INDEX_PATH, $this->validDynastyMessageStorePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_update_returns_unauthorized(): void
    {
        $this->putJson($this->messagePath(1), $this->validDynastyMessageUpdatePayload())
            ->assertUnauthorized();
    }

    public function test_unauthenticated_destroy_returns_unauthorized(): void
    {
        $this->deleteJson($this->messagePath(1))->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_all_endpoints(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE);

        $response = $this->postJson(self::INDEX_PATH, $this->validDynastyMessageStorePayload([
            'type' => 'requester_confirmation_message',
            'content' => 'Super admin message',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE);

        $id = $response->json('data.id');

        $this->putJson($this->messagePath($id), $this->validDynastyMessageUpdatePayload([
            'content' => 'Updated by super',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE);

        $this->deleteJson($this->messagePath($id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);
    }

    public function test_authenticated_regular_admin_can_access_all_endpoints(): void
    {
        $this->actingAsRegularAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $response = $this->postJson(self::INDEX_PATH, $this->validDynastyMessageStorePayload([
            'type' => 'reciever_message',
            'content' => 'Regular admin message',
        ]))->assertOk();

        $id = $response->json('data.id');

        $this->putJson($this->messagePath($id), $this->validDynastyMessageUpdatePayload([
            'content' => 'Updated by regular',
        ]))->assertOk();

        $this->deleteJson($this->messagePath($id))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Happy path / Index
    // -------------------------------------------------------------------------

    public function test_index_returns_empty_collection(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonPath('data', []);
    }

    public function test_index_returns_full_json_structure_with_type_titles(): void
    {
        $this->actingAsSuperAdmin();

        foreach (self::TYPE_TITLES as $type => $title) {
            $this->createDynastyMessage([
                'type' => $type,
                'message' => "Content for {$type}",
            ]);
        }

        $response = $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::INDEX_SUCCESS_MESSAGE)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    [
                        'id',
                        'type',
                        'type_title',
                        'message',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonCount(4, 'data');

        $items = collect($response->json('data'))->keyBy('type');

        foreach (self::TYPE_TITLES as $type => $title) {
            $this->assertSame($title, $items[$type]['type_title']);
            $this->assertSame("Content for {$type}", $items[$type]['message']);
        }
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_message_mapping_content_to_message_column(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson(self::INDEX_PATH, $this->validDynastyMessageStorePayload([
            'type' => 'requester_accept_message',
            'content' => 'Mapped content body',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::STORE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.type', 'requester_accept_message')
            ->assertJsonPath('data.type_title', self::TYPE_TITLES['requester_accept_message'])
            ->assertJsonPath('data.message', 'Mapped content body');

        $id = $response->json('data.id');

        $this->assertDatabaseHas('dynasty_messages', [
            'id' => $id,
            'type' => 'requester_accept_message',
            'message' => 'Mapped content body',
        ]);
    }

    public function test_store_returns_type_title_for_each_valid_type(): void
    {
        $this->actingAsSuperAdmin();

        foreach (self::TYPE_TITLES as $type => $title) {
            DynastyMessage::query()->delete();

            $this->postJson(self::INDEX_PATH, $this->validDynastyMessageStorePayload([
                'type' => $type,
                'content' => "Body {$type}",
            ]))
                ->assertOk()
                ->assertJsonPath('data.type', $type)
                ->assertJsonPath('data.type_title', $title)
                ->assertJsonPath('data.message', "Body {$type}");
        }
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_changes_message_content_only(): void
    {
        $this->actingAsSuperAdmin();

        $message = $this->createDynastyMessage([
            'type' => 'reciever_accept_message',
            'message' => 'Original',
        ]);

        $this->putJson($this->messagePath($message), $this->validDynastyMessageUpdatePayload([
            'content' => 'Updated content',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::UPDATE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.id', $message->id)
            ->assertJsonPath('data.type', 'reciever_accept_message')
            ->assertJsonPath('data.type_title', self::TYPE_TITLES['reciever_accept_message'])
            ->assertJsonPath('data.message', 'Updated content');

        $this->assertDatabaseHas('dynasty_messages', [
            'id' => $message->id,
            'type' => 'reciever_accept_message',
            'message' => 'Updated content',
        ]);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_message(): void
    {
        $this->actingAsSuperAdmin();

        $message = $this->createDynastyMessage([
            'type' => 'reciever_message',
            'message' => 'To delete',
        ]);

        $this->deleteJson($this->messagePath($message))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::DESTROY_SUCCESS_MESSAGE);

        $this->assertDatabaseMissing('dynasty_messages', [
            'id' => $message->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_store_requires_type_and_content(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'content']);
    }

    public function test_store_rejects_invalid_type(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson(self::INDEX_PATH, $this->validDynastyMessageStorePayload([
            'type' => 'invalid_type',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_store_rejects_duplicate_type(): void
    {
        $this->actingAsSuperAdmin();

        $this->createDynastyMessage([
            'type' => 'requester_confirmation_message',
            'message' => 'Existing',
        ]);

        $this->postJson(self::INDEX_PATH, $this->validDynastyMessageStorePayload([
            'type' => 'requester_confirmation_message',
            'content' => 'Duplicate',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_update_requires_content(): void
    {
        $this->actingAsSuperAdmin();

        $message = $this->createDynastyMessage();

        $this->putJson($this->messagePath($message), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    }

    public function test_update_rejects_non_string_content(): void
    {
        $this->actingAsSuperAdmin();

        $message = $this->createDynastyMessage();

        $this->putJson($this->messagePath($message), [
            'content' => ['not', 'a', 'string'],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    }

    // -------------------------------------------------------------------------
    // Not found
    // -------------------------------------------------------------------------

    public function test_update_returns_not_found_for_missing_message(): void
    {
        $this->actingAsSuperAdmin();

        $this->putJson($this->messagePath(99999), $this->validDynastyMessageUpdatePayload())
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::NOT_FOUND_MESSAGE);
    }

    public function test_destroy_returns_not_found_for_missing_message(): void
    {
        $this->actingAsSuperAdmin();

        $this->deleteJson($this->messagePath(99999))
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::NOT_FOUND_MESSAGE);
    }

    // -------------------------------------------------------------------------
    // Error paths
    // -------------------------------------------------------------------------

    public function test_index_returns_500_when_query_fails(): void
    {
        $this->actingAsSuperAdmin();

        Schema::drop('dynasty_messages');

        $this->getJson(self::INDEX_PATH)
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بارگذاری پیام‌های سلسله');
    }

    public function test_store_returns_500_when_create_fails(): void
    {
        $this->actingAsSuperAdmin();

        DynastyMessage::creating(function () {
            throw new \RuntimeException('forced message create failure');
        });

        $this->postJson(self::INDEX_PATH, $this->validDynastyMessageStorePayload([
            'type' => 'requester_confirmation_message',
            'content' => 'Will fail',
        ]))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در ثبت اطلاعات');
    }

    public function test_update_returns_500_when_update_fails(): void
    {
        $this->actingAsSuperAdmin();

        $message = $this->createDynastyMessage();

        DynastyMessage::updating(function () {
            throw new \RuntimeException('forced message update failure');
        });

        $this->putJson($this->messagePath($message), $this->validDynastyMessageUpdatePayload([
            'content' => 'Will fail',
        ]))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در بروزرسانی اطلاعات');
    }

    public function test_destroy_returns_500_when_delete_fails(): void
    {
        $this->actingAsSuperAdmin();

        $message = $this->createDynastyMessage();

        DynastyMessage::deleting(function () {
            throw new \RuntimeException('forced message delete failure');
        });

        $this->deleteJson($this->messagePath($message))
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'خطا در حذف پیام');
    }
}

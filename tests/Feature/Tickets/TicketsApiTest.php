<?php

namespace Tests\Feature\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketResponded;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesTicketsApiSchema;
use Tests\TestCase;

class TicketsApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesTicketsApiSchema;

    private const INDEX_PATH = '/api/tickets';

    private const DEPARTMENTS_PATH = '/api/tickets/departments';

    private const VALIDATION_ERROR_MESSAGE = 'خطا در اعتبارسنجی';

    private const RESPONSE_SUCCESS_MESSAGE = 'پاسخ تیکت ارسال شد';

    private const TRANSFER_SUCCESS_MESSAGE = 'تیکت به واحد مورد نظر ارجاع داده شد';

    private const DEPARTMENTS = [
        ['value' => 'technical_support', 'label' => 'پشتیبانی فنی'],
        ['value' => 'citizens_safety', 'label' => 'امنیت شهروندان'],
        ['value' => 'investment', 'label' => 'سرمایه گذاری'],
        ['value' => 'inspection', 'label' => 'بازرسی'],
        ['value' => 'protection', 'label' => 'حراست'],
        ['value' => 'ztb', 'label' => 'مدیریت کل ز.ت.ب'],
    ];

    private const VALID_DEPARTMENTS = [
        'technical_support',
        'citizens_safety',
        'investment',
        'inspection',
        'protection',
        'ztb',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTicketsApiSchema();
        Storage::fake('public');
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_index_returns_unauthorized(): void
    {
        $this->getJson(self::INDEX_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_departments_returns_unauthorized(): void
    {
        $this->getJson(self::DEPARTMENTS_PATH)->assertUnauthorized();
    }

    public function test_unauthenticated_send_response_returns_unauthorized(): void
    {
        $this->post($this->responsePath(1), ['response' => 'hello'], [
            'Accept' => 'application/json',
        ])->assertUnauthorized();
    }

    public function test_unauthenticated_transfer_returns_unauthorized(): void
    {
        $this->postJson($this->transferPath(1), [
            'department' => 'technical_support',
            'importance' => 0,
        ])->assertUnauthorized();
    }

    public function test_authenticated_super_admin_can_access_endpoints(): void
    {
        $this->actingAsSuperAdmin();
        $ticket = $this->createTicket(['department' => 'technical_support']);

        $this->getJson(self::DEPARTMENTS_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson(self::INDEX_PATH.'?department=technical_support')
            ->assertOk()
            ->assertJsonPath('success', true);

        Notification::fake();

        $this->post($this->responsePath($ticket), ['response' => 'Admin reply'], [
            'Accept' => 'application/json',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson($this->transferPath($ticket), [
            'department' => 'investment',
            'importance' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_authenticated_regular_admin_can_access_endpoints(): void
    {
        $this->actingAsRegularAdmin();
        $ticket = $this->createTicket(['department' => 'protection']);

        $this->getJson(self::DEPARTMENTS_PATH)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson(self::INDEX_PATH.'?department=protection')
            ->assertOk()
            ->assertJsonPath('success', true);

        Notification::fake();

        $this->post($this->responsePath($ticket), ['response' => 'Regular reply'], [
            'Accept' => 'application/json',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson($this->transferPath($ticket), [
            'department' => 'ztb',
            'importance' => -1,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // -------------------------------------------------------------------------
    // getDepartments
    // -------------------------------------------------------------------------

    public function test_get_departments_returns_all_six_value_label_pairs(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson(self::DEPARTMENTS_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(6, 'data.departments')
            ->assertExactJson([
                'success' => true,
                'data' => [
                    'departments' => self::DEPARTMENTS,
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_index_filters_by_single_department(): void
    {
        $this->actingAsSuperAdmin();

        $match = $this->createTicket(['department' => 'technical_support', 'title' => 'Match']);
        $this->createTicket(['department' => 'investment', 'title' => 'Other']);

        $response = $this->getJson(self::INDEX_PATH.'?department=technical_support')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.tickets');

        $this->assertSame($match->id, $response->json('data.tickets.0.id'));
    }

    public function test_index_filters_by_array_of_departments(): void
    {
        $this->actingAsSuperAdmin();

        $a = $this->createTicket(['department' => 'technical_support']);
        $b = $this->createTicket(['department' => 'investment']);
        $this->createTicket(['department' => 'ztb']);

        $response = $this->getJson(self::INDEX_PATH.'?'.http_build_query([
            'department' => ['technical_support', 'investment'],
        ]))
            ->assertOk()
            ->assertJsonCount(2, 'data.tickets');

        $ids = collect($response->json('data.tickets'))->pluck('id')->all();
        $this->assertContains($a->id, $ids);
        $this->assertContains($b->id, $ids);
    }

    public function test_index_excludes_other_departments(): void
    {
        $this->actingAsSuperAdmin();

        $this->createTicket(['department' => 'inspection']);
        $this->createTicket(['department' => 'protection']);

        $this->getJson(self::INDEX_PATH.'?department=citizens_safety')
            ->assertOk()
            ->assertJsonCount(0, 'data.tickets')
            ->assertJsonPath('data.pagination.total', 0);
    }

    public function test_index_without_department_param_returns_empty_due_to_where_in_null(): void
    {
        $this->actingAsSuperAdmin();

        $this->createTicket(['department' => 'technical_support']);
        $this->createTicket(['department' => null]);

        // Controller does whereIn('department', (array) null) => whereIn(..., [null]).
        // SQL NULL never matches IN (NULL), so the result set is empty.
        $this->getJson(self::INDEX_PATH)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data.tickets')
            ->assertJsonPath('data.pagination.total', 0);
    }

    public function test_index_searches_by_code(): void
    {
        $this->actingAsSuperAdmin();

        $match = $this->createTicket([
            'department' => 'technical_support',
            'code' => 'UNIQUE-CODE-99',
        ]);
        $this->createTicket([
            'department' => 'technical_support',
            'code' => 'OTHER-CODE',
        ]);

        $response = $this->getJson(self::INDEX_PATH.'?department=technical_support&search=UNIQUE-CODE')
            ->assertOk()
            ->assertJsonCount(1, 'data.tickets');

        $this->assertSame($match->id, $response->json('data.tickets.0.id'));
    }

    public function test_index_searches_by_title(): void
    {
        $this->actingAsSuperAdmin();

        $match = $this->createTicket([
            'department' => 'investment',
            'title' => 'Special Title Alpha',
        ]);
        $this->createTicket([
            'department' => 'investment',
            'title' => 'Something else',
        ]);

        $response = $this->getJson(self::INDEX_PATH.'?department=investment&search=Alpha')
            ->assertOk()
            ->assertJsonCount(1, 'data.tickets');

        $this->assertSame($match->id, $response->json('data.tickets.0.id'));
    }

    public function test_index_searches_by_sender_name(): void
    {
        $this->actingAsSuperAdmin();

        $sender = User::factory()->create(['name' => 'Ali Searchable']);
        $match = $this->createTicket([
            'department' => 'ztb',
            'user_id' => $sender->id,
        ]);
        $this->createTicket([
            'department' => 'ztb',
            'user_id' => User::factory()->create(['name' => 'Other Person'])->id,
        ]);

        $response = $this->getJson(self::INDEX_PATH.'?department=ztb&search=Searchable')
            ->assertOk()
            ->assertJsonCount(1, 'data.tickets');

        $this->assertSame($match->id, $response->json('data.tickets.0.id'));
        $this->assertSame('Ali Searchable', $response->json('data.tickets.0.sender.name'));
    }

    public function test_index_searches_by_sender_email(): void
    {
        $this->actingAsSuperAdmin();

        $sender = User::factory()->create(['email' => 'unique.findme@example.com']);
        $match = $this->createTicket([
            'department' => 'protection',
            'user_id' => $sender->id,
        ]);
        $this->createTicket([
            'department' => 'protection',
            'user_id' => User::factory()->create(['email' => 'other@example.com'])->id,
        ]);

        $response = $this->getJson(self::INDEX_PATH.'?department=protection&search=findme@example')
            ->assertOk()
            ->assertJsonCount(1, 'data.tickets');

        $this->assertSame($match->id, $response->json('data.tickets.0.id'));
    }

    public function test_index_search_with_no_matches_returns_empty(): void
    {
        $this->actingAsSuperAdmin();

        $this->createTicket([
            'department' => 'inspection',
            'title' => 'Known title',
            'code' => 'KNOWN-1',
        ]);

        $this->getJson(self::INDEX_PATH.'?department=inspection&search=zzzz-no-match')
            ->assertOk()
            ->assertJsonCount(0, 'data.tickets')
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.from', null)
            ->assertJsonPath('data.pagination.to', null);
    }

    public function test_index_pagination_defaults_to_page_one_and_per_page_ten(): void
    {
        $this->actingAsSuperAdmin();

        foreach (range(1, 12) as $i) {
            $this->createTicket([
                'department' => 'technical_support',
                'title' => "Ticket {$i}",
                'created_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->getJson(self::INDEX_PATH.'?department=technical_support')
            ->assertOk()
            ->assertJsonCount(10, 'data.tickets')
            ->assertJsonPath('data.pagination.total', 12)
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.pagination.from', 1)
            ->assertJsonPath('data.pagination.to', 10);

        $this->assertArrayHasKey('tickets', $response->json('data'));
        $this->assertArrayHasKey('pagination', $response->json('data'));
    }

    public function test_index_respects_custom_per_page_and_page(): void
    {
        $this->actingAsSuperAdmin();

        foreach (range(1, 5) as $i) {
            $this->createTicket([
                'department' => 'citizens_safety',
                'created_at' => now()->subMinutes($i),
            ]);
        }

        $this->getJson(self::INDEX_PATH.'?department=citizens_safety&per_page=2&page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data.tickets')
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.current_page', 2)
            ->assertJsonPath('data.pagination.total', 5)
            ->assertJsonPath('data.pagination.last_page', 3)
            ->assertJsonPath('data.pagination.from', 3)
            ->assertJsonPath('data.pagination.to', 4);
    }

    public function test_index_orders_newest_first(): void
    {
        $this->actingAsSuperAdmin();

        $older = $this->createTicket([
            'department' => 'investment',
            'title' => 'Older',
            'created_at' => now()->subDay(),
        ]);
        $newer = $this->createTicket([
            'department' => 'investment',
            'title' => 'Newer',
            'created_at' => now(),
        ]);

        $response = $this->getJson(self::INDEX_PATH.'?department=investment')
            ->assertOk()
            ->assertJsonCount(2, 'data.tickets');

        $this->assertSame($newer->id, $response->json('data.tickets.0.id'));
        $this->assertSame($older->id, $response->json('data.tickets.1.id'));
    }

    public function test_index_response_includes_ticket_resource_shape(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create([
            'name' => 'Sender Name',
            'email' => 'sender@example.com',
            'phone' => '09121234567',
        ]);
        $ticket = $this->createTicket([
            'department' => 'technical_support',
            'user_id' => $user->id,
            'code' => 'SHAPE-1',
            'title' => 'Shape title',
            'content' => 'Shape content',
            'status' => 0,
            'importance' => 1,
        ]);

        $this->getJson(self::INDEX_PATH.'?department=technical_support')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tickets.0.id', $ticket->id)
            ->assertJsonPath('data.tickets.0.code', 'SHAPE-1')
            ->assertJsonPath('data.tickets.0.title', 'Shape title')
            ->assertJsonPath('data.tickets.0.content', 'Shape content')
            ->assertJsonPath('data.tickets.0.status', 0)
            ->assertJsonPath('data.tickets.0.department', 'technical_support')
            ->assertJsonPath('data.tickets.0.importance', 1)
            ->assertJsonPath('data.tickets.0.priority_title', 'زیاد')
            ->assertJsonPath('data.tickets.0.status_label', 'جدید')
            ->assertJsonPath('data.tickets.0.sender.id', $user->id)
            ->assertJsonPath('data.tickets.0.sender.name', 'Sender Name')
            ->assertJsonPath('data.tickets.0.sender.email', 'sender@example.com')
            ->assertJsonPath('data.tickets.0.sender.phone', '09121234567')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tickets' => [
                        [
                            'id',
                            'code',
                            'title',
                            'content',
                            'status',
                            'department',
                            'importance',
                            'attachment',
                            'created_at',
                            'updated_at',
                            'priority_title',
                            'status_label',
                            'sender' => ['id', 'name', 'email', 'phone'],
                        ],
                    ],
                    'pagination' => [
                        'total',
                        'per_page',
                        'current_page',
                        'last_page',
                        'from',
                        'to',
                    ],
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // sendResponse
    // -------------------------------------------------------------------------

    public function test_send_response_without_attachment_creates_response_and_sets_status(): void
    {
        $admin = $this->actingAsSuperAdmin();
        Notification::fake();

        $ticket = $this->createTicket([
            'department' => 'technical_support',
            'status' => 0,
            'code' => 'RSP-001',
        ]);

        $this->post($this->responsePath($ticket), [
            'response' => 'پاسخ تستی',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::RESPONSE_SUCCESS_MESSAGE)
            ->assertJsonPath('data.ticket.id', $ticket->id)
            ->assertJsonPath('data.ticket.status', 1)
            ->assertJsonPath('data.ticket.status_label', 'پاسخ داده شده')
            ->assertJsonCount(1, 'data.ticket.responses');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 1,
        ], 'sqlite');

        $this->assertDatabaseHas('ticket_responses', [
            'ticket_id' => $ticket->id,
            'response' => 'پاسخ تستی',
            'attachment' => '',
            'responser_name' => $admin->name,
            'responser_id' => $admin->id,
        ], 'sqlite');
    }

    public function test_send_response_with_pdf_attachment_stores_file(): void
    {
        $admin = $this->actingAsSuperAdmin();
        Notification::fake();

        $ticket = $this->createTicket(['department' => 'investment', 'code' => 'PDF-1']);
        $file = UploadedFile::fake()->create('reply.pdf', 100, 'application/pdf');

        $response = $this->post($this->responsePath($ticket), [
            'response' => 'با پیوست',
            'attachment' => $file,
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::RESPONSE_SUCCESS_MESSAGE);

        $storedPath = $response->json('data.ticket.responses.0.attachment');
        $this->assertNotEmpty($storedPath);
        $this->assertStringContainsString('tickets/ticketResponses', $storedPath);
        Storage::disk('public')->assertExists($storedPath);

        $this->assertDatabaseHas('ticket_responses', [
            'ticket_id' => $ticket->id,
            'responser_id' => $admin->id,
            'attachment' => $storedPath,
        ], 'sqlite');
    }

    public function test_send_response_with_image_attachments_are_accepted(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        foreach (['png' => 'image/png', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg'] as $ext => $mime) {
            $ticket = $this->createTicket([
                'department' => 'inspection',
                'code' => 'IMG-'.$ext,
            ]);
            $file = UploadedFile::fake()->create("photo.{$ext}", 50, $mime);

            $response = $this->post($this->responsePath($ticket), [
                'response' => "Image {$ext}",
                'attachment' => $file,
            ], ['Accept' => 'application/json'])
                ->assertOk()
                ->assertJsonPath('success', true);

            Storage::disk('public')->assertExists($response->json('data.ticket.responses.0.attachment'));
        }
    }

    public function test_send_response_missing_response_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $ticket = $this->createTicket(['department' => 'ztb']);

        $this->post($this->responsePath($ticket), [], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::VALIDATION_ERROR_MESSAGE)
            ->assertJsonValidationErrors(['response']);
    }

    public function test_send_response_invalid_attachment_mime_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $ticket = $this->createTicket(['department' => 'ztb']);
        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

        $this->post($this->responsePath($ticket), [
            'response' => 'با فایل نامعتبر',
            'attachment' => $file,
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::VALIDATION_ERROR_MESSAGE)
            ->assertJsonValidationErrors(['attachment']);
    }

    public function test_send_response_nonexistent_ticket_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $this->post($this->responsePath(99999), [
            'response' => 'orphan reply',
        ], ['Accept' => 'application/json'])
            ->assertNotFound();
    }

    public function test_send_response_notifies_sender_with_ticket_code(): void
    {
        $this->actingAsSuperAdmin();
        Notification::fake();

        $sender = User::factory()->create();
        $ticket = $this->createTicket([
            'department' => 'protection',
            'user_id' => $sender->id,
            'code' => 'NOTIFY-42',
        ]);

        $this->post($this->responsePath($ticket), [
            'response' => 'پاسخ اعلان',
        ], ['Accept' => 'application/json'])
            ->assertOk();

        Notification::assertSentTo(
            $sender,
            TicketResponded::class,
            function (TicketResponded $notification) use ($sender) {
                $payload = $notification->toArray($sender);
                $message = $payload['message'] ?? '';

                return str_contains($message, 'NOTIFY-42')
                    && $message === 'به تیکت شما به شماره NOTIFY-42 پاسخ داده شد';
            }
        );
    }

    public function test_send_response_uses_authenticated_admin_as_responser(): void
    {
        $admin = $this->actingAsRegularAdmin();
        Notification::fake();

        $ticket = $this->createTicket(['department' => 'citizens_safety', 'code' => 'ADM-1']);

        $this->post($this->responsePath($ticket), [
            'response' => 'From regular admin',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.ticket.responses.0.responser_name', $admin->name)
            ->assertJsonPath('data.ticket.responses.0.responser_id', $admin->id);

        $this->assertDatabaseHas('ticket_responses', [
            'ticket_id' => $ticket->id,
            'responser_name' => 'Regular Admin',
            'responser_id' => $admin->id,
        ], 'sqlite');
    }

    // -------------------------------------------------------------------------
    // transfer
    // -------------------------------------------------------------------------

    public function test_transfer_succeeds_for_each_valid_department(): void
    {
        $this->actingAsSuperAdmin();

        foreach (self::VALID_DEPARTMENTS as $department) {
            $ticket = $this->createTicket([
                'department' => 'technical_support',
                'importance' => 0,
            ]);

            $this->postJson($this->transferPath($ticket), [
                'department' => $department,
                'importance' => 0,
            ])
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', self::TRANSFER_SUCCESS_MESSAGE)
                ->assertJsonPath('data.ticket.department', $department);

            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'department' => $department,
                'importance' => 0,
            ], 'sqlite');
        }
    }

    public function test_transfer_succeeds_for_each_valid_importance(): void
    {
        $this->actingAsSuperAdmin();

        foreach ([-1, 0, 1] as $importance) {
            $ticket = $this->createTicket([
                'department' => 'investment',
                'importance' => 0,
            ]);

            $expectedPriority = match ($importance) {
                -1 => 'کم',
                0 => 'متوسط',
                1 => 'زیاد',
            };

            $this->postJson($this->transferPath($ticket), [
                'department' => 'investment',
                'importance' => $importance,
            ])
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', self::TRANSFER_SUCCESS_MESSAGE)
                ->assertJsonPath('data.ticket.importance', $importance)
                ->assertJsonPath('data.ticket.priority_title', $expectedPriority);

            $this->assertDatabaseHas('tickets', [
                'id' => $ticket->id,
                'importance' => $importance,
            ], 'sqlite');
        }
    }

    public function test_transfer_missing_department_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $ticket = $this->createTicket(['department' => 'ztb']);

        $this->postJson($this->transferPath($ticket), [
            'importance' => 0,
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::VALIDATION_ERROR_MESSAGE)
            ->assertJsonValidationErrors(['department']);
    }

    public function test_transfer_invalid_department_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $ticket = $this->createTicket(['department' => 'ztb']);

        $this->postJson($this->transferPath($ticket), [
            'department' => 'unknown_unit',
            'importance' => 0,
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::VALIDATION_ERROR_MESSAGE)
            ->assertJsonValidationErrors(['department']);
    }

    public function test_transfer_missing_importance_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $ticket = $this->createTicket(['department' => 'ztb']);

        $this->postJson($this->transferPath($ticket), [
            'department' => 'technical_support',
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::VALIDATION_ERROR_MESSAGE)
            ->assertJsonValidationErrors(['importance']);
    }

    public function test_transfer_invalid_importance_returns_validation_error(): void
    {
        $this->actingAsSuperAdmin();

        $ticket = $this->createTicket(['department' => 'ztb']);

        $this->postJson($this->transferPath($ticket), [
            'department' => 'technical_support',
            'importance' => 5,
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', self::VALIDATION_ERROR_MESSAGE)
            ->assertJsonValidationErrors(['importance']);
    }

    public function test_transfer_nonexistent_ticket_returns_not_found(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson($this->transferPath(99999), [
            'department' => 'technical_support',
            'importance' => 0,
        ])->assertNotFound();
    }

    public function test_transfer_updates_department_and_importance_in_database(): void
    {
        $this->actingAsSuperAdmin();

        $ticket = $this->createTicket([
            'department' => 'technical_support',
            'importance' => -1,
        ]);

        $this->postJson($this->transferPath($ticket), [
            'department' => 'citizens_safety',
            'importance' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.ticket.department', 'citizens_safety')
            ->assertJsonPath('data.ticket.importance', 1);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'department' => 'citizens_safety',
            'importance' => 1,
        ], 'sqlite');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function responsePath(Ticket|int $ticket): string
    {
        $id = $ticket instanceof Ticket ? $ticket->id : $ticket;

        return "/api/tickets/{$id}/response";
    }

    private function transferPath(Ticket|int $ticket): string
    {
        $id = $ticket instanceof Ticket ? $ticket->id : $ticket;

        return "/api/tickets/{$id}/transfer";
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createTicket(array $overrides = []): Ticket
    {
        return Ticket::factory()->create($overrides);
    }
}

<?php

namespace Tests\Unit\BulkMessage;

use App\Jobs\SendBulkEmailJob;
use App\Mail\BulkMessageMail;
use App\Models\BulkMessageLog;
use App\Models\User;
use App\Services\BulkMessage\MessagePlaceholderService;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesBulkMessageSchema;
use Tests\TestCase;

class SendBulkEmailJobTest extends TestCase
{
    use CreatesBulkMessageSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBulkMessageSchema();
        Mail::fake();
    }

    public function test_job_sends_personalized_email_per_user(): void
    {
        User::create(['name' => 'A', 'email' => 'a@test.com', 'code' => 'hm-1', 'password' => 'x', 'ip' => '1.1.1.1']);
        User::create(['name' => 'B', 'email' => 'b@test.com', 'code' => 'hm-2', 'password' => 'x', 'ip' => '1.1.1.1']);

        $job = new SendBulkEmailJob([1, 2], '<p>Hello |name|</p>', 'bulk-uuid');
        $job->handle(app(MessagePlaceholderService::class));

        Mail::assertSent(BulkMessageMail::class, 2);
    }

    public function test_job_replaces_placeholders_in_email(): void
    {
        User::create(['name' => 'Ali', 'email' => 'ali@test.com', 'code' => 'hm-1', 'password' => 'x', 'ip' => '1.1.1.1']);

        $job = new SendBulkEmailJob([1], '<p>Hello |name|</p>', 'bulk-uuid');
        $job->handle(app(MessagePlaceholderService::class));

        Mail::assertSent(BulkMessageMail::class, function (BulkMessageMail $mail) {
            return str_contains($mail->htmlContent, 'Hello Ali');
        });
    }

    public function test_job_logs_failed_status_when_mail_send_throws(): void
    {
        User::create(['name' => 'Fail', 'email' => 'fail@test.com', 'code' => 'hm-9', 'password' => 'x', 'ip' => '1.1.1.1']);

        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \RuntimeException('smtp down'));

        $job = new SendBulkEmailJob([1], '<p>Hello</p>', 'bulk-fail');
        $job->handle(app(MessagePlaceholderService::class));

        $this->assertDatabaseHas('bulk_message_logs', [
            'bulk_send_id' => 'bulk-fail',
            'channel' => 'email',
            'user_id' => 1,
            'status' => 'failed',
            'error' => 'smtp down',
        ]);
        $this->assertSame(1, BulkMessageLog::query()->where('bulk_send_id', 'bulk-fail')->count());
    }
}

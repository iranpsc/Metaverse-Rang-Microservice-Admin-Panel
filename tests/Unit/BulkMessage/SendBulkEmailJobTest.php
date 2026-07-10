<?php

namespace Tests\Unit\BulkMessage;

use App\Jobs\SendBulkEmailJob;
use App\Mail\BulkMessageMail;
use App\Models\User;
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
        $job->handle(app(\App\Services\BulkMessage\MessagePlaceholderService::class));

        Mail::assertSent(BulkMessageMail::class, 2);
    }

    public function test_job_replaces_placeholders_in_email(): void
    {
        User::create(['name' => 'Ali', 'email' => 'ali@test.com', 'code' => 'hm-1', 'password' => 'x', 'ip' => '1.1.1.1']);

        $job = new SendBulkEmailJob([1], '<p>Hello |name|</p>', 'bulk-uuid');
        $job->handle(app(\App\Services\BulkMessage\MessagePlaceholderService::class));

        Mail::assertSent(BulkMessageMail::class, function (BulkMessageMail $mail) {
            return str_contains($mail->htmlContent, 'Hello Ali');
        });
    }
}

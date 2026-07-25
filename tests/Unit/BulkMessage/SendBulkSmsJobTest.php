<?php

namespace Tests\Unit\BulkMessage;

use App\Jobs\SendBulkSmsJob;
use App\Models\User;
use App\Services\BulkMessage\KavenegarBulkSmsService;
use Tests\Concerns\CreatesBulkMessageSchema;
use Tests\TestCase;

class SendBulkSmsJobTest extends TestCase
{
    use CreatesBulkMessageSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBulkMessageSchema();
    }

    public function test_job_calls_sms_service_with_user_ids_and_template(): void
    {
        $user = User::create([
            'name' => 'Test',
            'email' => 't@test.com',
            'code' => 'hm-1',
            'phone' => '09121234567',
            'password' => 'x',
            'ip' => '1.1.1.1',
        ]);

        $mock = $this->mock(KavenegarBulkSmsService::class);
        $mock->shouldReceive('sendBulk')
            ->once()
            ->withArgs(function ($users, $template, $sendId) use ($user) {
                return $users->count() === 1
                    && $users->first()->id === $user->id
                    && $template === 'سلام'
                    && $sendId === 'bulk-uuid';
            })
            ->andReturn([
                'sent' => 1,
                'failed' => 0,
                'message_ids' => [1],
                'errors' => [],
                'user_results' => [
                    ['user_id' => $user->id, 'message_id' => 1, 'status' => 'sent', 'error' => null],
                ],
            ]);

        $job = new SendBulkSmsJob([$user->id], 'سلام', 'bulk-uuid');
        $job->handle(app(KavenegarBulkSmsService::class));
    }

    public function test_job_retries_on_failure(): void
    {
        $job = new SendBulkSmsJob([1], 'test', 'bulk-uuid');

        $this->assertSame(3, $job->tries);
        $this->assertSame([30, 60, 120], $job->backoff);
    }
}

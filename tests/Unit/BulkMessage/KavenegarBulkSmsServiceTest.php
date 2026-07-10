<?php

namespace Tests\Unit\BulkMessage;

use App\Models\User;
use App\Services\BulkMessage\KavenegarBulkSmsService;
use App\Services\BulkMessage\MessagePlaceholderService;
use Kavenegar\Exceptions\ApiException;
use Kavenegar\KavenegarApi;
use Tests\Concerns\CreatesBulkMessageSchema;
use Tests\TestCase;

class KavenegarBulkSmsServiceTest extends TestCase
{
    use CreatesBulkMessageSchema;

    private KavenegarBulkSmsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBulkMessageSchema();
        $this->service = new KavenegarBulkSmsService(new MessagePlaceholderService);
        config(['kavenegar.sender' => '10008663']);
    }

    private function mockKavenegar(): \Mockery\MockInterface
    {
        $mock = \Mockery::mock(KavenegarApi::class);
        $this->app->instance('kavenegar', $mock);

        return $mock;
    }

    public function test_normalizes_phone_with_plus_98_prefix(): void
    {
        $this->assertSame('09121234567', $this->service->normalizePhone('+989121234567'));
    }

    public function test_normalizes_phone_strips_spaces_and_dashes(): void
    {
        $this->assertSame('09121234567', $this->service->normalizePhone('0912 123-4567'));
    }

    public function test_skips_users_with_invalid_phone(): void
    {
        $mock = $this->mockKavenegar();
        $mock->shouldReceive('SendArray')->once()->andReturn([(object) ['messageid' => 1, 'status' => 1]]);

        $users = collect([
            $this->makeUser(['phone' => null]),
            $this->makeUser(['phone' => '123']),
            $this->makeUser(['phone' => '09121234567']),
        ]);

        $summary = $this->service->sendBulk($users, 'سلام |name|');

        $this->assertSame(1, $summary['sent']);
    }

    public function test_replaces_placeholders_in_message(): void
    {
        $mock = $this->mockKavenegar();
        $mock->shouldReceive('SendArray')
            ->once()
            ->withArgs(function ($senders, $receptors, $messages) {
                return $messages[0] === 'سلام علی - ali@example.com - hm-100';
            })
            ->andReturn([(object) ['messageid' => 1, 'status' => 1]]);

        $users = collect([
            $this->makeUser([
                'name' => 'علی',
                'email' => 'ali@example.com',
                'code' => 'hm-100',
                'phone' => '09121234567',
            ]),
        ]);

        $this->service->sendBulk($users, 'سلام |name| - |email| - |code|');
    }

    public function test_chunks_into_batches_of_200(): void
    {
        $mock = $this->mockKavenegar();
        $mock->shouldReceive('SendArray')
            ->twice()
            ->andReturnUsing(function ($senders, $receptors) {
                $count = count($receptors);
                $entries = [];
                for ($i = 0; $i < $count; $i++) {
                    $entries[] = (object) ['messageid' => $i + 1, 'status' => 1];
                }

                return $entries;
            });

        $users = collect();
        for ($i = 0; $i < 250; $i++) {
            $users->push($this->makeUser([
                'id' => $i + 1,
                'phone' => '0912'.str_pad((string) ($i % 10000000), 7, '0', STR_PAD_LEFT),
            ]));
        }

        $summary = $this->service->sendBulk($users, 'test');

        $this->assertSame(250, $summary['sent']);
    }

    public function test_builds_equal_length_arrays(): void
    {
        $mock = $this->mockKavenegar();
        $mock->shouldReceive('SendArray')
            ->once()
            ->withArgs(function ($senders, $receptors, $messages) {
                return count($senders) === count($receptors) && count($receptors) === count($messages);
            })
            ->andReturn([(object) ['messageid' => 123, 'status' => 1]]);

        $users = collect([$this->makeUser(['phone' => '09121234567'])]);

        $this->service->sendBulk($users, 'hello');
    }

    public function test_uses_localmessageids_for_deduplication(): void
    {
        $mock = $this->mockKavenegar();
        $mock->shouldReceive('SendArray')
            ->once()
            ->withArgs(function ($senders, $receptors, $messages, $date, $type, $localIds) {
                return $localIds[0] === 'bulk-send-1-user-5';
            })
            ->andReturn([(object) ['messageid' => 1, 'status' => 1]]);

        $users = collect([$this->makeUser(['id' => 5, 'phone' => '09121234567'])]);

        $this->service->sendBulk($users, 'hello', 'send-1');
    }

    public function test_handles_api_exception_gracefully(): void
    {
        $mock = $this->mockKavenegar();
        $mock->shouldReceive('SendArray')
            ->once()
            ->andThrow(new ApiException('اعتبار کافی نیست', 418));

        $users = collect([$this->makeUser(['phone' => '09121234567'])]);

        $summary = $this->service->sendBulk($users, 'hello');

        $this->assertSame(0, $summary['sent']);
        $this->assertSame(1, $summary['failed']);
        $this->assertNotEmpty($summary['errors']);
    }

    public function test_returns_summary_with_sent_and_failed_counts(): void
    {
        $mock = $this->mockKavenegar();
        $mock->shouldReceive('SendArray')
            ->once()
            ->andReturn([(object) ['messageid' => 99, 'status' => 1]]);

        $users = collect([$this->makeUser(['phone' => '09121234567'])]);

        $summary = $this->service->sendBulk($users, 'hello');

        $this->assertArrayHasKey('sent', $summary);
        $this->assertArrayHasKey('failed', $summary);
        $this->assertArrayHasKey('message_ids', $summary);
        $this->assertSame(1, $summary['sent']);
        $this->assertContains(99, $summary['message_ids']);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = new User;
        $user->forceFill(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'code' => 'hm-1',
            'phone' => '09121234567',
        ], $attributes));
        $user->id = $attributes['id'] ?? 1;

        return $user;
    }
}

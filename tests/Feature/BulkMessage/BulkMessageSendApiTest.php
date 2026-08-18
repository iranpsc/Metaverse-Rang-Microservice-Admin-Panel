<?php

namespace Tests\Feature\BulkMessage;

use App\Jobs\SendBulkEmailJob;
use App\Jobs\SendBulkSmsJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\ActsAsSuperAdmin;
use Tests\Concerns\CreatesBulkMessageSchema;
use Tests\TestCase;

class BulkMessageSendApiTest extends TestCase
{
    use ActsAsSuperAdmin;
    use CreatesBulkMessageSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBulkMessageSchema();
        Queue::fake();
    }

    public function test_send_requires_channel_and_target_type(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/api/bulk-messages/send', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['channel', 'target_type']);
    }

    public function test_send_requires_email_content_when_channel_is_email(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/api/bulk-messages/send', [
            'channel' => 'email',
            'target_type' => 'all',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email_content']);
    }

    public function test_send_requires_sms_content_when_channel_is_sms(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/api/bulk-messages/send', [
            'channel' => 'sms',
            'target_type' => 'all',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['sms_content']);
    }

    public function test_send_requires_level_ids_when_target_is_levels(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/api/bulk-messages/send', [
            'channel' => 'sms',
            'sms_content' => 'سلام',
            'target_type' => 'levels',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['level_ids']);
    }

    public function test_send_requires_code_range_when_target_is_code_range(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/api/bulk-messages/send', [
            'channel' => 'sms',
            'sms_content' => 'سلام',
            'target_type' => 'code_range',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['code_from', 'code_to']);
    }

    public function test_send_requires_user_ids_when_target_is_selected_users(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/api/bulk-messages/send', [
            'channel' => 'sms',
            'sms_content' => 'سلام',
            'target_type' => 'selected_users',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['user_ids']);
    }

    public function test_send_dispatches_jobs_for_selected_users(): void
    {
        $this->actingAsSuperAdmin();

        $selected = User::create(['name' => 'Selected', 'email' => 's@test.com', 'code' => 'hm-1', 'password' => 'x', 'ip' => '1.1.1.1']);
        User::create(['name' => 'Other', 'email' => 'o@test.com', 'code' => 'hm-2', 'password' => 'x', 'ip' => '1.1.1.1']);

        $this->postJson('/api/bulk-messages/send', [
            'channel' => 'sms',
            'sms_content' => 'سلام |name|',
            'target_type' => 'selected_users',
            'user_ids' => [$selected->id],
        ])->assertAccepted();

        Queue::assertPushed(SendBulkSmsJob::class, function (SendBulkSmsJob $job) use ($selected) {
            return $job->userIds === [$selected->id];
        });
    }

    public function test_search_users_by_name_or_code(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::create(['name' => 'Ali Reza', 'email' => 'ali@test.com', 'code' => 'hm-2000999', 'password' => 'x', 'ip' => '1.1.1.1']);
        User::create(['name' => 'Other', 'email' => 'other@test.com', 'code' => 'hm-3000001', 'password' => 'x', 'ip' => '1.1.1.1']);

        $byName = $this->getJson('/api/bulk-messages/users/search?search=Ali')
            ->assertOk()
            ->json('data.options');

        $this->assertCount(1, $byName);
        $this->assertSame($user->id, $byName[0]['value']);

        $byCode = $this->getJson('/api/bulk-messages/users/search?search=2000999')
            ->assertOk()
            ->json('data.options');

        $this->assertCount(1, $byCode);
        $this->assertSame($user->id, $byCode[0]['value']);

        $byCodeWithPrefix = $this->getJson('/api/bulk-messages/users/search?search=hm-2000999')
            ->assertOk()
            ->json('data.options');

        $this->assertCount(1, $byCodeWithPrefix);
        $this->assertSame($user->id, $byCodeWithPrefix[0]['value']);
    }

    public function test_send_dispatches_sms_jobs_when_sms_channel_selected(): void
    {
        $this->actingAsSuperAdmin();

        User::create(['name' => 'U', 'email' => 'u@test.com', 'code' => 'hm-1', 'password' => 'x', 'ip' => '1.1.1.1']);

        $this->postJson('/api/bulk-messages/send', [
            'channel' => 'sms',
            'sms_content' => 'سلام',
            'target_type' => 'all',
        ])->assertAccepted();

        Queue::assertPushed(SendBulkSmsJob::class);
        Queue::assertNotPushed(SendBulkEmailJob::class);
    }

    public function test_send_dispatches_email_jobs_when_email_channel_selected(): void
    {
        $this->actingAsSuperAdmin();

        User::create(['name' => 'U', 'email' => 'u@test.com', 'code' => 'hm-1', 'password' => 'x', 'ip' => '1.1.1.1']);

        $this->postJson('/api/bulk-messages/send', [
            'channel' => 'email',
            'email_content' => '<p>سلام</p>',
            'target_type' => 'all',
        ])->assertAccepted();

        Queue::assertPushed(SendBulkEmailJob::class);
        Queue::assertNotPushed(SendBulkSmsJob::class);
    }

    public function test_send_returns_202_accepted(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJson('/api/bulk-messages/send', [
            'channel' => 'sms',
            'sms_content' => 'سلام',
            'target_type' => 'all',
        ]);

        $response->assertAccepted()
            ->assertJsonStructure(['success', 'data' => ['bulk_send_id'], 'message']);
    }

    public function test_send_chunks_users_into_200_per_sms_job(): void
    {
        $this->actingAsSuperAdmin();

        for ($i = 0; $i < 350; $i++) {
            User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@test.com",
                'code' => 'hm-'.$i,
                'password' => 'x',
                'ip' => '1.1.1.1',
            ]);
        }

        $this->postJson('/api/bulk-messages/send', [
            'channel' => 'sms',
            'sms_content' => 'سلام',
            'target_type' => 'all',
        ])->assertAccepted();

        Queue::assertPushed(SendBulkSmsJob::class, 2);
    }
}

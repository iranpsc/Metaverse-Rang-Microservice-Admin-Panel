<?php

namespace App\Jobs;

use App\Models\BulkMessageLog;
use App\Models\User;
use App\Services\BulkMessage\KavenegarBulkSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBulkSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public function __construct(
        public readonly array $userIds,
        public readonly string $smsContent,
        public readonly string $bulkSendId
    ) {
    }

    public function handle(KavenegarBulkSmsService $smsService): void
    {
        $users = User::query()
            ->whereIn('id', $this->userIds)
            ->get(['id', 'name', 'email', 'code', 'phone']);

        $summary = $smsService->sendBulk($users, $this->smsContent, $this->bulkSendId);

        foreach ($summary['user_results'] as $result) {
            BulkMessageLog::create([
                'bulk_send_id' => $this->bulkSendId,
                'channel' => 'sms',
                'user_id' => $result['user_id'],
                'message_id' => $result['message_id'],
                'status' => $result['status'],
                'error' => $result['error'],
                'created_at' => now(),
            ]);
        }
    }
}

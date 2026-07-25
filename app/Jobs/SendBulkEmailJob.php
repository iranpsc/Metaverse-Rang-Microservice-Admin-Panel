<?php

namespace App\Jobs;

use App\Mail\BulkMessageMail;
use App\Models\BulkMessageLog;
use App\Models\User;
use App\Services\BulkMessage\MessagePlaceholderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendBulkEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public function __construct(
        public readonly array $userIds,
        public readonly string $emailContent,
        public readonly string $bulkSendId
    ) {
    }

    public function handle(MessagePlaceholderService $placeholderService): void
    {
        $users = User::query()
            ->whereIn('id', $this->userIds)
            ->whereNotNull('email')
            ->get(['id', 'name', 'email', 'code']);

        foreach ($users as $user) {
            try {
                $personalizedHtml = $placeholderService->replace($this->emailContent, $user);
                $plainContent = strip_tags($personalizedHtml);

                Mail::to($user->email)->send(new BulkMessageMail($personalizedHtml, $plainContent));

                BulkMessageLog::create([
                    'bulk_send_id' => $this->bulkSendId,
                    'channel' => 'email',
                    'user_id' => $user->id,
                    'message_id' => null,
                    'status' => 'sent',
                    'error' => null,
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                BulkMessageLog::create([
                    'bulk_send_id' => $this->bulkSendId,
                    'channel' => 'email',
                    'user_id' => $user->id,
                    'message_id' => null,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'created_at' => now(),
                ]);
            }
        }
    }
}

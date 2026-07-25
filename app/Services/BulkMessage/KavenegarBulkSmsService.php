<?php

namespace App\Services\BulkMessage;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Kavenegar\Exceptions\ApiException;
use Kavenegar\Exceptions\HttpException;
use Kavenegar\Laravel\Facade as Kavenegar;

class KavenegarBulkSmsService
{
    private const BATCH_SIZE = 200;

    public function __construct(
        private readonly MessagePlaceholderService $placeholderService
    ) {
    }

    public function sendBulk(Collection $users, string $template, ?string $sendId = null): array
    {
        $sender = config('kavenegar.sender');
        $recipients = [];

        foreach ($users as $user) {
            $phone = $this->normalizePhone($user->phone);
            if (! $phone) {
                continue;
            }

            $recipients[] = [
                'receptor' => $phone,
                'sender' => $sender,
                'message' => $this->placeholderService->replace($template, $user),
                'localid' => $sendId ? "bulk-{$sendId}-user-{$user->id}" : null,
                'user_id' => $user->id,
            ];
        }

        $summary = [
            'sent' => 0,
            'failed' => 0,
            'message_ids' => [],
            'errors' => [],
            'user_results' => [],
        ];

        foreach (array_chunk($recipients, self::BATCH_SIZE) as $batch) {
            if (empty($batch)) {
                continue;
            }

            $receptors = array_column($batch, 'receptor');
            $senders = array_column($batch, 'sender');
            $messages = array_column($batch, 'message');
            $localIds = array_column($batch, 'localid');

            try {
                $entries = Kavenegar::SendArray($senders, $receptors, $messages, null, null, $localIds);

                foreach ($entries as $index => $entry) {
                    $summary['sent']++;
                    $summary['message_ids'][] = $entry->messageid;
                    $summary['user_results'][] = [
                        'user_id' => $batch[$index]['user_id'] ?? null,
                        'message_id' => $entry->messageid,
                        'status' => 'sent',
                        'error' => null,
                    ];
                }
            } catch (ApiException|HttpException $e) {
                $summary['failed'] += count($batch);
                $summary['errors'][] = [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ];

                foreach ($batch as $item) {
                    $summary['user_results'][] = [
                        'user_id' => $item['user_id'],
                        'message_id' => null,
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ];
                }

                Log::error('Kavenegar SendArray failed', [
                    'batch_size' => count($batch),
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);
            }
        }

        return $summary;
    }

    public function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $normalized = preg_replace('/[\s\-()]/', '', $phone);

        if (str_starts_with($normalized, '+98')) {
            $normalized = '0' . substr($normalized, 3);
        } elseif (str_starts_with($normalized, '98') && strlen($normalized) === 12) {
            $normalized = '0' . substr($normalized, 2);
        }

        if (! preg_match('/^09\d{9}$/', $normalized)) {
            return null;
        }

        return $normalized;
    }
}

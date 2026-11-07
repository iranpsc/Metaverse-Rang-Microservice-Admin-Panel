<?php

namespace App\Jobs;

use App\Services\ContentWebhooks\DeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverContentWebhook implements ShouldQueue
{
    use Queueable;

    public int $timeout = 45;

    // Delivery attempts are persisted separately; releases and disabled delivery
    // must not exhaust the queue's attempt counter.
    public int $tries = 0;

    public function __construct(public string $cycleId)
    {
        $this->onConnection(config('content_webhooks.queue_connection'))->onQueue(config('content_webhooks.queue'));
    }

    public function handle(DeliveryService $delivery): void
    {
        if (config("queue.connections.{$this->connection}.driver") === 'sync') {
            // A synchronous job cannot be released back onto the queue. Complete
            // the pack, transport, and finalization passes in this request.
            for ($pass = 0; $pass < 3; $pass += 1) {
                $delay = $delivery->deliver($this->cycleId);
                if ($delay !== 0) {
                    return;
                }
            }

            return;
        }

        $delay = $delivery->deliver($this->cycleId);
        if ($delay !== null) {
            $this->release($delay);
        }
    }
}

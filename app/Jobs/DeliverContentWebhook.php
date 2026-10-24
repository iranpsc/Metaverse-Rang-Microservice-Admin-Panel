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
        $this->onConnection('content-webhooks')->onQueue('content-webhooks');
    }

    public function handle(DeliveryService $delivery): void
    {
        $delay = $delivery->deliver($this->cycleId);
        if ($delay !== null) {
            $this->release($delay);
        }
    }
}

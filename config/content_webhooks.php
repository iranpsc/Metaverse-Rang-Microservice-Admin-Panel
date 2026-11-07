<?php

return [
    'enabled' => env('CONTENT_WEBHOOKS_ENABLED', false),
    'url' => env('WEBHOOK_URL'),
    'secret' => env('WEBHOOK_SECRET'),
    'queue_connection' => env('CONTENT_WEBHOOK_QUEUE_CONNECTION', env('APP_ENV') === 'local' ? 'sync' : 'content-webhooks'),
    'queue' => 'content-webhooks',
];

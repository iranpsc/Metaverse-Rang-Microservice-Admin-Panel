<?php

return [
    'enabled' => env('CONTENT_WEBHOOKS_ENABLED', false),
    'url' => env('WEBHOOK_URL'),
    'secret' => env('WEBHOOK_SECRET'),
    'queue' => 'content-webhooks',
];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'translation_model' => env('OPENAI_TRANSLATION_MODEL', 'gpt-5-mini'),
    ],

    'cloudflare' => [
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'endpoint' => env('CLOUDFLARE_AI_ENDPOINT', 'https://api.cloudflare.com/client/v4/accounts/'.env('CLOUDFLARE_ACCOUNT_ID').'/ai/run'),
        'translation_model' => env('CLOUDFLARE_TRANSLATION_MODEL', '@cf/meta/m2m100-1.2b'),
        'translation_batch_size' => env('CLOUDFLARE_TRANSLATION_BATCH_SIZE', 5),
        'translation_requests_per_minute' => env('CLOUDFLARE_TRANSLATION_REQUESTS_PER_MINUTE', 300),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];

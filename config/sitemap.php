<?php

return [
    'url' => env('SITEMAP_GENERATOR_URL', 'http://sitemap-generator-service:8071'),
    'token' => env('SITEMAP_GENERATOR_TOKEN', env('INTERNAL_SERVICE_SECRET', '')),
];

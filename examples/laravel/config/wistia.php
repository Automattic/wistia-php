<?php
declare(strict_types=1);

// env() belongs only in config files so config:cache remains safe to use.
return [
    'api_token' => env('WISTIA_API_TOKEN', ''),
    'webhook_secret' => env('WISTIA_WEBHOOK_SECRET', ''),
];

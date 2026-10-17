<?php
declare(strict_types=1);

// env() belongs only in config files so config:cache remains safe to use.
// After changing secrets, rebuild the consumer's config cache and restart any
// long-lived workers. Never commit the generated cache: it contains these values.
return [
    // Outbound API authentication, used by Configuration/Client.
    'api_token' => env('WISTIA_API_TOKEN', ''),
    // Inbound signature verification, independent of the API token.
    'webhook_secret' => env('WISTIA_WEBHOOK_SECRET', ''),
];

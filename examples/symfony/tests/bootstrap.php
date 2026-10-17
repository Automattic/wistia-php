<?php
declare(strict_types=1);

// Deliberately do not load Dotenv or any consumer/library .env for these tests.
require dirname(__DIR__) . '/vendor/autoload.php';

foreach ([
    'APP_ENV' => 'test',
    'APP_DEBUG' => '0',
    'KERNEL_CLASS' => App\Kernel::class,
    'APP_SECRET' => 'synthetic-application-secret',
    'DEFAULT_URI' => 'http://localhost',
    'WISTIA_API_TOKEN' => 'synthetic-api-token',
    'WISTIA_WEBHOOK_SECRET' => 'synthetic-webhook-secret',
] as $name => $value) {
    $_SERVER[$name] = $_ENV[$name] = $value;
}

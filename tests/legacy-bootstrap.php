<?php
declare(strict_types=1);

require __DIR__ . '/legacy-guard.php';

// Historical suite also requires its original runtime and client; it is not compatible with P02.
require dirname(__DIR__) . '/vendor/autoload.php';

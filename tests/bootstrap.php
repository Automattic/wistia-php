<?php
declare(strict_types=1);

// Redaction assertions must inspect captured arguments, regardless of the caller's INI.
ini_set('zend.exception_ignore_args', '0');

// No credentials or live-test configuration is loaded by the offline suite.
require dirname(__DIR__) . '/vendor/autoload.php';

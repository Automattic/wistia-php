<?php
declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

use Automattic\Wistia\WebhookParser;
use Automattic\Wistia\WebhookVerifier;
use Automattic\Wistia\Webhook\VerificationException;
use Automattic\Wistia\Webhook\ParsingException;

// No framework/application caller graph: full traces can be inspected finitely here.
if (ini_get('zend.exception_ignore_args') !== '0') { throw new RuntimeException('Arguments must be captured.'); }
$secret = 'isolated-private-webhook-secret';
$private = 'isolated-private-body-sentinel';
$parser = new WebhookParser(new WebhookVerifier($secret));
$valid = '{"hook":{"uuid":"h"},"events":[],"extra":"' . $private . '"}';
$malformed = '{' . $private;
$wrongShape = '{"hook":{"uuid":"h"},"events":{},"extra":"' . $private . '"}';
$cases = [[$valid, null, VerificationException::class], [$valid, $private, VerificationException::class],
    [$valid, str_repeat('0', 64), VerificationException::class],
    [$malformed, null, VerificationException::class],
    [$malformed, hash_hmac('sha256', $malformed, $secret), ParsingException::class],
    [$wrongShape, hash_hmac('sha256', $wrongShape, $secret), ParsingException::class]];
foreach ($cases as [$body, $signature, $expected]) {
    try {
        $parser->parse($body, $signature);
        throw new RuntimeException('Expected local failure.');
    } catch (VerificationException | ParsingException $error) {
        if (!$error instanceof $expected || $error->getPrevious() !== null) { throw new RuntimeException('Wrong failure.'); }
        $frames = 0;
        foreach ($error->getTrace() as $frame) {
            if (!str_starts_with($frame['class'] ?? '', 'Automattic\\Wistia\\')) { continue; }
            ++$frames;
            if (!array_key_exists('args', $frame)) { throw new RuntimeException('Arguments missing.'); }
            foreach ($frame['args'] as $arg) {
                if (!$arg instanceof SensitiveParameterValue && !is_scalar($arg) && $arg !== null) {
                    throw new RuntimeException('Unprotected library argument.');
                }
            }
        }
        if ($frames === 0) { throw new RuntimeException('No library frames.'); }
        $output = (string) $error . print_r($error, true) . print_r($error->getTrace(), true)
            . json_encode(get_object_vars($error), JSON_THROW_ON_ERROR);
        if (str_contains($output, $secret) || str_contains($output, $private)) { throw new RuntimeException('Private webhook data leaked.'); }
    }
}
echo "Sanitized isolated webhook failures: 6 cases; arguments enabled.\n";

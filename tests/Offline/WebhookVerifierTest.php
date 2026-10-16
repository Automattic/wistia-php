<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\WebhookVerifier;
use Automattic\Wistia\Webhook\VerificationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WebhookVerifierTest extends TestCase
{
    public function testAcceptsIndependentSha256HmacVector(): void
    {
        // RFC 4231 test case 1: bytes, key and expected digest are independent of this implementation.
        (new WebhookVerifier(str_repeat("\x0b", 20)))->verify('Hi There',
            'b0344c61d8db38535ca8afceaf0bf12b881dc200c9833da726e9376c2e32cff7');
        self::assertTrue(true);
    }

    #[DataProvider('invalidSignatures')]
    public function testRejectsMissingMalformedAndInvalidSignatures(?string $signature): void
    {
        $this->expectException(VerificationException::class);
        (new WebhookVerifier('synthetic-secret'))->verify('{"a":1}', $signature);
    }

    public static function invalidSignatures(): array
    {
        return [[null], [''], [' '], [str_repeat('a', 63)], [str_repeat('a', 65)],
            [str_repeat('g', 64)], [str_repeat('A', 64)], ['sha256=' . str_repeat('a', 64)],
            [base64_encode(str_repeat('x', 32))], [str_repeat('0', 64)], [str_repeat('a', 64) . "\n"]];
    }

    #[DataProvider('changedBytes')]
    public function testNeverNormalizesRawBytes(string $changed): void
    {
        $this->expectException(VerificationException::class);
        (new WebhookVerifier('synthetic-secret'))->verify($changed, hash_hmac('sha256', '{"a":1}', 'synthetic-secret'));
    }

    public static function changedBytes(): array
    {
        return [['{"a":2}'], ['{ "a":1}'], ["{\"a\":1}\n"], ["{\"a\":1}\0"]];
    }

    public function testSecretCannotBeDebuggedOrSerialized(): void
    {
        $verifier = new WebhookVerifier('private-webhook-secret');
        self::assertStringNotContainsString('private-webhook-secret', print_r($verifier, true));
        ob_start(); var_dump($verifier); $debug = ob_get_clean();
        self::assertStringNotContainsString('private-webhook-secret', $debug);
        $this->expectException(\LogicException::class);
        serialize($verifier);
    }

    public function testEmptySecretFailsLocally(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new WebhookVerifier('');
    }
}

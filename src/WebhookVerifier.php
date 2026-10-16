<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Webhook\VerificationException;

final readonly class WebhookVerifier
{
    public function __construct(#[\SensitiveParameter] private string $secret)
    {
        if ($secret === '') {
            throw new \InvalidArgumentException('Configure a nonempty webhook secret.');
        }
    }

    /** Verify the original bytes against the bare SHA-256 hex X-Wistia-Signature. */
    public function verify(#[\SensitiveParameter] string $rawBody, #[\SensitiveParameter] ?string $signature): void
    {
        if ($signature === null || preg_match('/\A[0-9a-f]{64}\z/D', $signature) !== 1) {
            throw VerificationException::create();
        }
        if (!hash_equals(hash_hmac('sha256', $rawBody, $this->secret), $signature)) {
            throw VerificationException::create();
        }
    }

    public function __debugInfo(): array { return []; }
    public function __serialize(): array { throw new \LogicException('Webhook secrets cannot be serialized.'); }
}

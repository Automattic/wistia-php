<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webhook;

final readonly class Envelope
{
    /** @param list<Event> $events @internal Constructed by WebhookParser after verification. */
    public function __construct(
        #[\SensitiveParameter] public string $hookUuid,
        #[\SensitiveParameter] public array $events,
        #[\SensitiveParameter] private \stdClass $data,
        #[\SensitiveParameter] private string $rawBody,
    ) {}

    /** Deliberate access to the complete decoded document, including unknown fields. */
    public function data(): \stdClass { return $this->data; }

    /** Exact original bytes, including numeric spellings not representable in PHP. */
    public function rawBody(): string { return $this->rawBody; }

    public function __debugInfo(): array { return []; }
    public function __serialize(): array { throw new \LogicException('Webhook envelope data must be accessed deliberately.'); }
}

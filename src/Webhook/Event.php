<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webhook;

final readonly class Event
{
    /** @internal Constructed by WebhookParser after envelope validation. */
    public function __construct(
        #[\SensitiveParameter] public string $uuid,
        #[\SensitiveParameter] public string $type,
        #[\SensitiveParameter] public string $generatedAt,
        #[\SensitiveParameter] private \stdClass $data,
    ) {}

    /** Deliberate access to potentially sensitive application data; objects remain objects. */
    public function payload(): mixed { return $this->data->payload; }

    /** Includes unknown fields such as metadata, without a closed event-type schema. */
    public function data(): \stdClass { return $this->data; }

    public function __debugInfo(): array { return []; }
    public function __serialize(): array { throw new \LogicException('Webhook event data must be accessed deliberately.'); }
}

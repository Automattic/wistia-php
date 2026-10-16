<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Webhook\Envelope;
use Automattic\Wistia\Webhook\Event;
use Automattic\Wistia\Webhook\ParsingException;

final readonly class WebhookParser
{
    public function __construct(#[\SensitiveParameter] private WebhookVerifier $verifier) {}

    /** Verification is mandatory and always precedes JSON decoding. */
    public function parse(#[\SensitiveParameter] string $rawBody, #[\SensitiveParameter] ?string $signature): Envelope
    {
        $this->verifier->verify($rawBody, $signature);
        try {
            $data = json_decode($rawBody, false, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        } catch (\JsonException) {
            // Native decoder errors can carry input arguments. Never retain their cause.
            throw ParsingException::create();
        }
        if (!$data instanceof \stdClass || !isset($data->hook) || !$data->hook instanceof \stdClass
            || !self::hasString($data->hook, 'uuid') || !isset($data->events) || !is_array($data->events)) {
            throw ParsingException::create();
        }
        $events = [];
        foreach ($data->events as $event) {
            if (!$event instanceof \stdClass || !self::hasString($event, 'uuid')
                || !self::hasString($event, 'type') || !self::hasString($event, 'generated_at')
                || !property_exists($event, 'payload')) {
                throw ParsingException::create();
            }
            $events[] = new Event($event->uuid, $event->type, $event->generated_at, $event);
        }
        return new Envelope($data->hook->uuid, $events, $data, $rawBody);
    }

    private static function hasString(#[\SensitiveParameter] \stdClass $data, string $field): bool
    {
        return isset($data->{$field}) && is_string($data->{$field}) && $data->{$field} !== '';
    }

    public function __debugInfo(): array { return []; }
    public function __serialize(): array { throw new \LogicException('Webhook parser configuration cannot be serialized.'); }
}

<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webinars;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent fields from null. */
final readonly class WebinarRegistrationCreated
{
    public function __construct(
        #[\SensitiveParameter] public string $visitor_key,
        #[\SensitiveParameter] public string $personalized_event_url,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['visitor_key', 'personalized_event_url'], ['visitor_key', 'personalized_event_url']);
        return new self(
            Wire::string($value->visitor_key),
            Wire::string($value->personalized_event_url),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

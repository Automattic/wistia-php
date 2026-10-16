<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webinars;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent fields from null. */
final readonly class RegistrationPollResponse
{
    public function __construct(
        #[\SensitiveParameter] public ?string $poll_question_text,
        #[\SensitiveParameter] public ?string $poll_option_text,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['poll_question_text', 'poll_option_text'], []);
        return new self(
            property_exists($value, 'poll_question_text') ? ($value->poll_question_text === null ? null : Wire::string($value->poll_question_text)) : null,
            property_exists($value, 'poll_option_text') ? ($value->poll_option_text === null ? null : Wire::string($value->poll_option_text)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

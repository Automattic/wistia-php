<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webinars;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent fields from null. */
final readonly class WebinarFolder
{
    public function __construct(
        #[\SensitiveParameter] public string $id,
        #[\SensitiveParameter] public string $index_url,
        #[\SensitiveParameter] public string $url,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'index_url', 'url'], ['id', 'index_url', 'url']);
        return new self(
            Wire::string($value->id),
            Wire::string($value->index_url),
            Wire::string($value->url),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

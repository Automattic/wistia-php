<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class ReviewBundle
{
    public function __construct(
        #[\SensitiveParameter] public string|null $hashed_id,
        #[\SensitiveParameter] public string|null $name,
        #[\SensitiveParameter] public string|null $url,
        #[\SensitiveParameter] public bool|null $allow_downloads,
        #[\SensitiveParameter] public string|null $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['hashed_id', 'name', 'url', 'allow_downloads', 'cursor'], []);
        return new self(
            property_exists($value, 'hashed_id') ? Wire::string($value->hashed_id) : null,
            property_exists($value, 'name') ? Wire::string($value->name) : null,
            property_exists($value, 'url') ? Wire::string($value->url) : null,
            property_exists($value, 'allow_downloads') ? Wire::boolean($value->allow_downloads) : null,
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

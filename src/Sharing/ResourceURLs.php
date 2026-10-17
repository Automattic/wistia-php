<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class ResourceURLs
{
    public function __construct(
        #[\SensitiveParameter] public string $type,
        #[\SensitiveParameter] public string $hashed_id,
        #[\SensitiveParameter] public ResourceURLsUrls $urls,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['type', 'hashed_id', 'urls'], ['type', 'hashed_id', 'urls']);
        return new self(
            Wire::string($value->type),
            Wire::string($value->hashed_id),
            ResourceURLsUrls::fromWire($value->urls),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

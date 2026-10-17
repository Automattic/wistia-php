<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeDataFoldersItemMedias
{
    public function __construct(
        #[\SensitiveParameter] public string|null $url,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['url'], []);
        return new self(
            property_exists($value, 'url') ? Wire::string($value->url) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

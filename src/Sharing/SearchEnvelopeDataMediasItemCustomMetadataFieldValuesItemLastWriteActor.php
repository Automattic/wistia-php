<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeDataMediasItemCustomMetadataFieldValuesItemLastWriteActor
{
    public function __construct(
        #[\SensitiveParameter] public string $type,
        #[\SensitiveParameter] public string $id,
        #[\SensitiveParameter] public string $name,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['type', 'id', 'name'], ['type', 'id', 'name']);
        return new self(
            Wire::oneOf(Wire::string($value->type), ['contact']),
            Wire::string($value->id),
            Wire::string($value->name),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

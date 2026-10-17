<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeDataMediasItemCustomMetadataFieldValuesItemLastWrite
{
    public function __construct(
        #[\SensitiveParameter] public string $at,
        #[\SensitiveParameter] public string $source,
        #[\SensitiveParameter] public SearchEnvelopeDataMediasItemCustomMetadataFieldValuesItemLastWriteActor|null $actor,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['at', 'source', 'actor'], ['at', 'source', 'actor']);
        return new self(
            Wire::string($value->at),
            Wire::oneOf(Wire::string($value->source), ['ui', 'api', 'mcp']),
            ($value->actor === null ? null : SearchEnvelopeDataMediasItemCustomMetadataFieldValuesItemLastWriteActor::fromWire($value->actor)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

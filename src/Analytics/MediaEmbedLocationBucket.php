<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class MediaEmbedLocationBucket
{
    public function __construct(
        #[\SensitiveParameter] public string|null $timestamp,
        /** @var list<MediaEmbedLocation>|null */ #[\SensitiveParameter] public array|null $entries,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['timestamp','entries'], []);
        return new self(
            property_exists($value, 'timestamp') ? Wire::string($value->timestamp) : null,
            property_exists($value, 'entries') ? RecordList::decode($value->entries, MediaEmbedLocation::fromWire(...)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

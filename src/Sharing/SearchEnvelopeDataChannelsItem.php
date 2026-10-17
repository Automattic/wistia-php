<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeDataChannelsItem
{
    public function __construct(
        #[\SensitiveParameter] public int $id,
        #[\SensitiveParameter] public string $hashed_id,
        #[\SensitiveParameter] public string $name,
        #[\SensitiveParameter] public string $description,
        #[\SensitiveParameter] public int $media_count,
        #[\SensitiveParameter] public string $created,
        #[\SensitiveParameter] public string $updated,
        #[\SensitiveParameter] public int|null $live_stream_event_count,
        #[\SensitiveParameter] public bool|null $auto_publish_enabled,
        #[\SensitiveParameter] public bool|null $podcast_enabled,
        #[\SensitiveParameter] public string|null $custom_url,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'hashed_id', 'name', 'description', 'media_count', 'created', 'updated', 'live_stream_event_count', 'auto_publish_enabled', 'podcast_enabled', 'custom_url'], ['id', 'hashed_id', 'name', 'description', 'media_count', 'created', 'updated']);
        return new self(
            Wire::integer($value->id),
            Wire::string($value->hashed_id),
            Wire::string($value->name),
            Wire::string($value->description),
            Wire::integer($value->media_count),
            Wire::string($value->created),
            Wire::string($value->updated),
            property_exists($value, 'live_stream_event_count') ? Wire::integer($value->live_stream_event_count) : null,
            property_exists($value, 'auto_publish_enabled') ? ($value->auto_publish_enabled === null ? null : Wire::boolean($value->auto_publish_enabled)) : null,
            property_exists($value, 'podcast_enabled') ? ($value->podcast_enabled === null ? null : Wire::boolean($value->podcast_enabled)) : null,
            property_exists($value, 'custom_url') ? ($value->custom_url === null ? null : Wire::string($value->custom_url)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

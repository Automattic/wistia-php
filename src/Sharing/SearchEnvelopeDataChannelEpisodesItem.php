<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeDataChannelEpisodesItem
{
    public function __construct(
        #[\SensitiveParameter] public int $id,
        #[\SensitiveParameter] public string $hashed_id,
        #[\SensitiveParameter] public string|null $title,
        #[\SensitiveParameter] public string $description,
        #[\SensitiveParameter] public string $summary,
        #[\SensitiveParameter] public string|null $channel_hashed_id,
        #[\SensitiveParameter] public string|null $media_hashed_id,
        #[\SensitiveParameter] public string|null $live_stream_event_hashed_id,
        #[\SensitiveParameter] public bool $published,
        #[\SensitiveParameter] public string $created,
        #[\SensitiveParameter] public string $updated,
        #[\SensitiveParameter] public string|null $publish_at,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'hashed_id', 'title', 'description', 'summary', 'channel_hashed_id', 'media_hashed_id', 'live_stream_event_hashed_id', 'published', 'created', 'updated', 'publish_at'], ['id', 'hashed_id', 'description', 'summary', 'channel_hashed_id', 'media_hashed_id', 'published', 'created', 'updated']);
        return new self(
            Wire::integer($value->id),
            Wire::string($value->hashed_id),
            property_exists($value, 'title') ? ($value->title === null ? null : Wire::string($value->title)) : null,
            Wire::string($value->description),
            Wire::string($value->summary),
            ($value->channel_hashed_id === null ? null : Wire::string($value->channel_hashed_id)),
            ($value->media_hashed_id === null ? null : Wire::string($value->media_hashed_id)),
            property_exists($value, 'live_stream_event_hashed_id') ? ($value->live_stream_event_hashed_id === null ? null : Wire::string($value->live_stream_event_hashed_id)) : null,
            Wire::boolean($value->published),
            Wire::string($value->created),
            Wire::string($value->updated),
            property_exists($value, 'publish_at') ? ($value->publish_at === null ? null : Wire::string($value->publish_at)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

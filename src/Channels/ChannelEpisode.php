<?php
declare(strict_types=1);

namespace Automattic\Wistia\Channels;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent from null. */
final readonly class ChannelEpisode
{
    public function __construct(
        #[\SensitiveParameter] public ?int $id,
        #[\SensitiveParameter] public string $channel_hashed_id,
        #[\SensitiveParameter] public string $created,
        #[\SensitiveParameter] public ?string $cursor,
        #[\SensitiveParameter] public string $description,
        #[\SensitiveParameter] public string $summary,
        #[\SensitiveParameter] public string $hashed_id,
        #[\SensitiveParameter] public string $media_hashed_id,
        #[\SensitiveParameter] public ?string $live_stream_event_hashed_id,
        #[\SensitiveParameter] public bool $published,
        #[\SensitiveParameter] public ?string $publish_at,
        #[\SensitiveParameter] public ?string $title,
        #[\SensitiveParameter] public string $updated,
        #[\SensitiveParameter] public ?ChannelEpisodePodcastSettingsRecord $podcast_settings,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'channel_hashed_id', 'created', 'cursor', 'description', 'summary', 'hashed_id', 'media_hashed_id', 'live_stream_event_hashed_id', 'published', 'publish_at', 'title', 'updated', 'podcast_settings'], ['channel_hashed_id', 'created', 'description', 'summary', 'hashed_id', 'media_hashed_id', 'published', 'title', 'updated']);
        return new self(
            property_exists($value, 'id') ? Wire::integer($value->id) : null,
            Wire::string($value->channel_hashed_id),
            Wire::string($value->created),
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            Wire::string($value->description),
            Wire::string($value->summary),
            Wire::string($value->hashed_id),
            Wire::string($value->media_hashed_id),
            property_exists($value, 'live_stream_event_hashed_id') ? ($value->live_stream_event_hashed_id === null ? null : Wire::string($value->live_stream_event_hashed_id)) : null,
            Wire::boolean($value->published),
            property_exists($value, 'publish_at') ? Wire::string($value->publish_at) : null,
            ($value->title === null ? null : Wire::string($value->title)),
            Wire::string($value->updated),
            property_exists($value, 'podcast_settings') ? ChannelEpisodePodcastSettingsRecord::fromWire($value->podcast_settings) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

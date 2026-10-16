<?php
declare(strict_types=1);

namespace Automattic\Wistia\Webinars;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent fields from null. */
final readonly class Webinar
{
    public function __construct(
        #[\SensitiveParameter] public string $id,
        #[\SensitiveParameter] public string $hashed_id,
        #[\SensitiveParameter] public string $title,
        #[\SensitiveParameter] public ?string $description,
        #[\SensitiveParameter] public ?string $scheduled_for,
        #[\SensitiveParameter] public ?int $event_duration,
        #[\SensitiveParameter] public string $time_zone,
        #[\SensitiveParameter] public string $lifecycle_status,
        #[\SensitiveParameter] public string $registration_status,
        #[\SensitiveParameter] public string $created_at,
        #[\SensitiveParameter] public string $updated_at,
        #[\SensitiveParameter] public string $audience_link,
        #[\SensitiveParameter] public string $host_link,
        #[\SensitiveParameter] public string $panelist_link,
        #[\SensitiveParameter] public ?string $thumbnail_url,
        #[\SensitiveParameter] public ?WebinarFolder $folder,
        #[\SensitiveParameter] public ?string $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'hashed_id', 'title', 'description', 'scheduled_for', 'event_duration', 'time_zone', 'lifecycle_status', 'registration_status', 'created_at', 'updated_at', 'audience_link', 'host_link', 'panelist_link', 'thumbnail_url', 'folder', 'cursor'], ['id', 'hashed_id', 'title', 'time_zone', 'lifecycle_status', 'registration_status', 'created_at', 'updated_at', 'audience_link', 'host_link', 'panelist_link']);
        return new self(
            Wire::string($value->id),
            Wire::string($value->hashed_id),
            Wire::string($value->title),
            property_exists($value, 'description') ? ($value->description === null ? null : Wire::string($value->description)) : null,
            property_exists($value, 'scheduled_for') ? ($value->scheduled_for === null ? null : Wire::string($value->scheduled_for)) : null,
            property_exists($value, 'event_duration') ? ($value->event_duration === null ? null : Wire::integer($value->event_duration)) : null,
            Wire::string($value->time_zone),
            Wire::oneOf(Wire::string($value->lifecycle_status), ['pending', 'ready', 'starting', 'started', 'ended', 'vod_ready', 'failed']),
            Wire::string($value->registration_status),
            Wire::string($value->created_at),
            Wire::string($value->updated_at),
            Wire::string($value->audience_link),
            Wire::string($value->host_link),
            Wire::string($value->panelist_link),
            property_exists($value, 'thumbnail_url') ? ($value->thumbnail_url === null ? null : Wire::string($value->thumbnail_url)) : null,
            property_exists($value, 'folder') ? ($value->folder === null ? null : WebinarFolder::fromWire($value->folder)) : null,
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

<?php
declare(strict_types=1);

namespace Automattic\Wistia\Channels;

use Automattic\Wistia\Media\Wire;

/** Typed response record; presentFields distinguishes absent from null. */
final readonly class ChannelEpisodePodcastSettingsRecord
{
    public function __construct(
        #[\SensitiveParameter] public ?string $episode_type,
        #[\SensitiveParameter] public ?int $episode_number,
        #[\SensitiveParameter] public ?int $season_number,
        #[\SensitiveParameter] public ?bool $explicit_content,
        #[\SensitiveParameter] public ?bool $hide_from_feed,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['episode_type', 'episode_number', 'season_number', 'explicit_content', 'hide_from_feed'], []);
        return new self(
            property_exists($value, 'episode_type') ? ($value->episode_type === null ? null : Wire::oneOf(Wire::string($value->episode_type), ['full', 'trailer', 'bonus'])) : null,
            property_exists($value, 'episode_number') ? ($value->episode_number === null ? null : Wire::integer($value->episode_number)) : null,
            property_exists($value, 'season_number') ? ($value->season_number === null ? null : Wire::integer($value->season_number)) : null,
            property_exists($value, 'explicit_content') ? Wire::boolean($value->explicit_content) : null,
            property_exists($value, 'hide_from_feed') ? Wire::boolean($value->hide_from_feed) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

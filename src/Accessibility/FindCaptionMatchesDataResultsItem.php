<?php
declare(strict_types=1);

namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Media\Wire;

/** Typed wire record. presentFields distinguishes omitted fields from explicit null. */
final readonly class FindCaptionMatchesDataResultsItem
{
    public function __construct(
        #[\SensitiveParameter] public string $media_hashed_id,
        #[\SensitiveParameter] public string $status,
        /** @var list<FindCaptionMatchesDataResultsItemTracksItem>|null */
        #[\SensitiveParameter] public ?array $tracks,
        #[\SensitiveParameter] public ?FindCaptionMatchesDataResultsItemTracksItem $track,
        #[\SensitiveParameter] public ?string $match_status,
        #[\SensitiveParameter] public ?int $match_count,
        /** @var list<FindCaptionMatchesDataResultsItemMatchesItem>|null */
        #[\SensitiveParameter] public ?array $matches,
        #[\SensitiveParameter] public ?bool $matches_truncated,
        #[\SensitiveParameter] public ?int $next_occurrence,
        /** @var list<FindCaptionMatchesDataResultsItemMatchesItem>|null */
        #[\SensitiveParameter] public ?array $suggestions,
        #[\SensitiveParameter] public ?bool $suggestions_truncated,
        #[\SensitiveParameter] public ?string $hint,
        #[\SensitiveParameter] public ?string $recovery,
        /** @var list<string> */ public array $presentFields,
    ) {}

    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['media_hashed_id', 'status', 'tracks', 'track', 'match_status', 'match_count', 'matches', 'matches_truncated', 'next_occurrence', 'suggestions', 'suggestions_truncated', 'hint', 'recovery'], ['media_hashed_id', 'status']);
        return new self(
            Wire::string($value->media_hashed_id),
            Wire::oneOf(Wire::string($value->status), ['ok', 'media_not_found', 'invalid_media', 'no_tracks', 'language_required', 'track_not_found', 'no_active_payload', 'response_too_large']),
            property_exists($value, 'tracks') ? Wire::items($value->tracks, static fn($v) => FindCaptionMatchesDataResultsItemTracksItem::fromWire($v)) : null,
            property_exists($value, 'track') ? FindCaptionMatchesDataResultsItemTracksItem::fromWire($value->track) : null,
            property_exists($value, 'match_status') ? Wire::oneOf(Wire::string($value->match_status), ['resolved', 'ambiguous', 'not_found', 'occurrence_not_found']) : null,
            property_exists($value, 'match_count') ? Wire::integer($value->match_count) : null,
            property_exists($value, 'matches') ? Wire::items($value->matches, static fn($v) => FindCaptionMatchesDataResultsItemMatchesItem::fromWire($v)) : null,
            property_exists($value, 'matches_truncated') ? Wire::boolean($value->matches_truncated) : null,
            property_exists($value, 'next_occurrence') ? Wire::range(Wire::integer($value->next_occurrence), 1, null) : null,
            property_exists($value, 'suggestions') ? Wire::items($value->suggestions, static fn($v) => FindCaptionMatchesDataResultsItemMatchesItem::fromWire($v)) : null,
            property_exists($value, 'suggestions_truncated') ? Wire::boolean($value->suggestions_truncated) : null,
            property_exists($value, 'hint') ? ($value->hint === null ? null : Wire::string($value->hint)) : null,
            property_exists($value, 'recovery') ? Wire::string($value->recovery) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

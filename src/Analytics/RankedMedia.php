<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class RankedMedia
{
    public function __construct(
        #[\SensitiveParameter] public int|null $plays,
        #[\SensitiveParameter] public float|null $play_rate,
        #[\SensitiveParameter] public float|null $engagement_rate,
        #[\SensitiveParameter] public int|null $unique_visitors,
        #[\SensitiveParameter] public float|null $played_time,
        #[\SensitiveParameter] public string|null $media_name,
        #[\SensitiveParameter] public string|null $media_hashed_id,
        #[\SensitiveParameter] public string|null $media_type,
        #[\SensitiveParameter] public string|null $media_thumbnail_url,
        #[\SensitiveParameter] public float|null $media_duration,
        #[\SensitiveParameter] public string|null $media_uploaded_at,
        #[\SensitiveParameter] public string|null $media_deleted_at,
        #[\SensitiveParameter] public int|null $unique_loads,
        #[\SensitiveParameter] public int|null $unique_plays,
        #[\SensitiveParameter] public int|null $form_conversions,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['plays','play_rate','engagement_rate','unique_visitors','played_time','media_name','media_hashed_id','media_type','media_thumbnail_url','media_duration','media_uploaded_at','media_deleted_at','unique_loads','unique_plays','form_conversions'], ['media_hashed_id']);
        return new self(
            property_exists($value, 'plays') && $value->plays !== null ? Wire::integer($value->plays) : null,
            property_exists($value, 'play_rate') && $value->play_rate !== null ? Wire::number($value->play_rate) : null,
            property_exists($value, 'engagement_rate') && $value->engagement_rate !== null ? Wire::number($value->engagement_rate) : null,
            property_exists($value, 'unique_visitors') && $value->unique_visitors !== null ? Wire::integer($value->unique_visitors) : null,
            property_exists($value, 'played_time') && $value->played_time !== null ? Wire::number($value->played_time) : null,
            property_exists($value, 'media_name') && $value->media_name !== null ? Wire::string($value->media_name) : null,
            property_exists($value, 'media_hashed_id') && $value->media_hashed_id !== null ? Wire::string($value->media_hashed_id) : null,
            property_exists($value, 'media_type') && $value->media_type !== null ? Wire::string($value->media_type) : null,
            property_exists($value, 'media_thumbnail_url') && $value->media_thumbnail_url !== null ? Wire::string($value->media_thumbnail_url) : null,
            property_exists($value, 'media_duration') && $value->media_duration !== null ? Wire::number($value->media_duration) : null,
            property_exists($value, 'media_uploaded_at') && $value->media_uploaded_at !== null ? Wire::string($value->media_uploaded_at) : null,
            property_exists($value, 'media_deleted_at') && $value->media_deleted_at !== null ? Wire::string($value->media_deleted_at) : null,
            property_exists($value, 'unique_loads') && $value->unique_loads !== null ? Wire::integer($value->unique_loads) : null,
            property_exists($value, 'unique_plays') && $value->unique_plays !== null ? Wire::integer($value->unique_plays) : null,
            property_exists($value, 'form_conversions') && $value->form_conversions !== null ? Wire::integer($value->form_conversions) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class AccountEmbedLocation
{
    public function __construct(
        #[\SensitiveParameter] public string|null $embed_domain,
        #[\SensitiveParameter] public int|null $media_count,
        #[\SensitiveParameter] public int|null $loads,
        #[\SensitiveParameter] public int|null $plays,
        #[\SensitiveParameter] public float|null $play_rate,
        #[\SensitiveParameter] public float|null $played_time,
        #[\SensitiveParameter] public float|null $engagement_rate,
        #[\SensitiveParameter] public int|null $unique_visitors,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['embed_domain','media_count','loads','plays','play_rate','played_time','engagement_rate','unique_visitors'], []);
        return new self(
            property_exists($value, 'embed_domain') && $value->embed_domain !== null ? Wire::string($value->embed_domain) : null,
            property_exists($value, 'media_count') && $value->media_count !== null ? Wire::integer($value->media_count) : null,
            property_exists($value, 'loads') && $value->loads !== null ? Wire::integer($value->loads) : null,
            property_exists($value, 'plays') && $value->plays !== null ? Wire::integer($value->plays) : null,
            property_exists($value, 'play_rate') && $value->play_rate !== null ? Wire::number($value->play_rate) : null,
            property_exists($value, 'played_time') && $value->played_time !== null ? Wire::number($value->played_time) : null,
            property_exists($value, 'engagement_rate') && $value->engagement_rate !== null ? Wire::number($value->engagement_rate) : null,
            property_exists($value, 'unique_visitors') && $value->unique_visitors !== null ? Wire::integer($value->unique_visitors) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

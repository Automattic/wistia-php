<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class MediaEmbedLocation
{
    public function __construct(
        #[\SensitiveParameter] public string|null $embed_domain,
        #[\SensitiveParameter] public string|null $embed_path,
        #[\SensitiveParameter] public string|null $embed_url,
        #[\SensitiveParameter] public string|null $page_title,
        #[\SensitiveParameter] public int|null $loads,
        #[\SensitiveParameter] public int|null $unique_loads,
        #[\SensitiveParameter] public int|null $plays,
        #[\SensitiveParameter] public int|null $unique_plays,
        #[\SensitiveParameter] public float|null $play_rate,
        #[\SensitiveParameter] public float|null $played_time,
        #[\SensitiveParameter] public float|null $engagement_rate,
        #[\SensitiveParameter] public int|null $unique_visitors,
        #[\SensitiveParameter] public float|null $cta_conversion_rate,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['embed_domain','embed_path','embed_url','page_title','loads','unique_loads','plays','unique_plays','play_rate','played_time','engagement_rate','unique_visitors','cta_conversion_rate'], []);
        return new self(
            property_exists($value, 'embed_domain') && $value->embed_domain !== null ? Wire::string($value->embed_domain) : null,
            property_exists($value, 'embed_path') && $value->embed_path !== null ? Wire::string($value->embed_path) : null,
            property_exists($value, 'embed_url') && $value->embed_url !== null ? Wire::string($value->embed_url) : null,
            property_exists($value, 'page_title') && $value->page_title !== null ? Wire::string($value->page_title) : null,
            property_exists($value, 'loads') && $value->loads !== null ? Wire::integer($value->loads) : null,
            property_exists($value, 'unique_loads') && $value->unique_loads !== null ? Wire::integer($value->unique_loads) : null,
            property_exists($value, 'plays') && $value->plays !== null ? Wire::integer($value->plays) : null,
            property_exists($value, 'unique_plays') && $value->unique_plays !== null ? Wire::integer($value->unique_plays) : null,
            property_exists($value, 'play_rate') && $value->play_rate !== null ? Wire::number($value->play_rate) : null,
            property_exists($value, 'played_time') && $value->played_time !== null ? Wire::number($value->played_time) : null,
            property_exists($value, 'engagement_rate') && $value->engagement_rate !== null ? Wire::number($value->engagement_rate) : null,
            property_exists($value, 'unique_visitors') && $value->unique_visitors !== null ? Wire::integer($value->unique_visitors) : null,
            property_exists($value, 'cta_conversion_rate') && $value->cta_conversion_rate !== null ? Wire::number($value->cta_conversion_rate) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

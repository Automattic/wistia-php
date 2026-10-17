<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class Event
{
    public function __construct(
        #[\SensitiveParameter] public string|null $received_at,
        #[\SensitiveParameter] public string|null $event_key,
        #[\SensitiveParameter] public string|null $visitor_key,
        #[\SensitiveParameter] public string|null $embed_url,
        #[\SensitiveParameter] public float|null $percent_viewed,
        #[\SensitiveParameter] public string|null $ip,
        #[\SensitiveParameter] public string|null $org,
        #[\SensitiveParameter] public string|null $country,
        #[\SensitiveParameter] public string|null $region,
        #[\SensitiveParameter] public string|null $city,
        #[\SensitiveParameter] public float|null $lat,
        #[\SensitiveParameter] public float|null $lon,
        #[\SensitiveParameter] public string|null $email,
        #[\SensitiveParameter] public string|null $media_id,
        #[\SensitiveParameter] public string|null $media_name,
        #[\SensitiveParameter] public string|null $media_url,
        #[\SensitiveParameter] public string|null $iframe_heatmap_url,
        #[\SensitiveParameter] public Thumbnail|null $thumbnail,
        #[\SensitiveParameter] public int|string|null $conversion_type,
        #[\SensitiveParameter] public ConversionData|null $conversion_data,
        #[\SensitiveParameter] public UserAgentDetails|null $user_agent_details,
        #[\SensitiveParameter] public \stdClass|null $attributes,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['received_at', 'event_key', 'visitor_key', 'embed_url', 'percent_viewed', 'ip', 'org', 'country', 'region', 'city', 'lat', 'lon', 'email', 'media_id', 'media_name', 'media_url', 'iframe_heatmap_url', 'thumbnail', 'conversion_type', 'conversion_data', 'user_agent_details', 'attributes'], []);
        return new self(
            property_exists($value, 'received_at') ? (Wire::string($value->received_at)) : null,
            property_exists($value, 'event_key') ? (Wire::string($value->event_key)) : null,
            property_exists($value, 'visitor_key') ? (Wire::string($value->visitor_key)) : null,
            property_exists($value, 'embed_url') ? (Wire::string($value->embed_url)) : null,
            property_exists($value, 'percent_viewed') ? (Wire::number($value->percent_viewed)) : null,
            property_exists($value, 'ip') ? (Wire::string($value->ip)) : null,
            property_exists($value, 'org') ? (Wire::string($value->org)) : null,
            property_exists($value, 'country') ? (Wire::string($value->country)) : null,
            property_exists($value, 'region') ? (Wire::string($value->region)) : null,
            property_exists($value, 'city') ? (Wire::string($value->city)) : null,
            property_exists($value, 'lat') ? (Wire::number($value->lat)) : null,
            property_exists($value, 'lon') ? (Wire::number($value->lon)) : null,
            property_exists($value, 'email') ? ($value->email === null ? null : (Wire::string($value->email))) : null,
            property_exists($value, 'media_id') ? (Wire::string($value->media_id)) : null,
            property_exists($value, 'media_name') ? (Wire::string($value->media_name)) : null,
            property_exists($value, 'media_url') ? (Wire::string($value->media_url)) : null,
            property_exists($value, 'iframe_heatmap_url') ? (Wire::string($value->iframe_heatmap_url)) : null,
            property_exists($value, 'thumbnail') ? (Thumbnail::fromWire($value->thumbnail)) : null,
            property_exists($value, 'conversion_type') ? (is_int($value->conversion_type) ? $value->conversion_type : Wire::string($value->conversion_type)) : null,
            property_exists($value, 'conversion_data') ? (ConversionData::fromWire($value->conversion_data)) : null,
            property_exists($value, 'user_agent_details') ? (UserAgentDetails::fromWire($value->user_agent_details)) : null,
            property_exists($value, 'attributes') ? (self::attributes($value->attributes)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    private static function attributes(#[\SensitiveParameter] mixed $value): \stdClass
    {
        if (!$value instanceof \stdClass) { throw new \UnexpectedValueException('Expected event attributes object.'); }
        return $value;
    }
    public function __debugInfo(): array { return []; }
}

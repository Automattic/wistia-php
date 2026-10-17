<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class MediaTrafficGroup
{
    public function __construct(
        #[\SensitiveParameter] public string|null $utm_campaign,
        #[\SensitiveParameter] public string|null $utm_source,
        #[\SensitiveParameter] public string|null $utm_medium,
        #[\SensitiveParameter] public string|null $referrer_domain,
        #[\SensitiveParameter] public string|null $viewer_screen_size,
        #[\SensitiveParameter] public int|null $loads,
        #[\SensitiveParameter] public int|null $plays,
        #[\SensitiveParameter] public float|null $engagement_rate,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['utm_campaign','utm_source','utm_medium','referrer_domain','viewer_screen_size','loads','plays','engagement_rate'], []);
        return new self(
            property_exists($value, 'utm_campaign') && $value->utm_campaign !== null ? Wire::string($value->utm_campaign) : null,
            property_exists($value, 'utm_source') && $value->utm_source !== null ? Wire::string($value->utm_source) : null,
            property_exists($value, 'utm_medium') && $value->utm_medium !== null ? Wire::string($value->utm_medium) : null,
            property_exists($value, 'referrer_domain') && $value->referrer_domain !== null ? Wire::string($value->referrer_domain) : null,
            property_exists($value, 'viewer_screen_size') && $value->viewer_screen_size !== null ? Wire::string($value->viewer_screen_size) : null,
            property_exists($value, 'loads') && $value->loads !== null ? Wire::integer($value->loads) : null,
            property_exists($value, 'plays') && $value->plays !== null ? Wire::integer($value->plays) : null,
            property_exists($value, 'engagement_rate') && $value->engagement_rate !== null ? Wire::number($value->engagement_rate) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

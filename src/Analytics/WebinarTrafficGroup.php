<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class WebinarTrafficGroup
{
    public function __construct(
        #[\SensitiveParameter] public ?string $utm_campaign,
        #[\SensitiveParameter] public ?string $utm_source,
        #[\SensitiveParameter] public ?string $utm_medium,
        #[\SensitiveParameter] public ?string $referrer_domain,
        #[\SensitiveParameter] public ?int $registrations,
        #[\SensitiveParameter] public ?int $attendees,
        #[\SensitiveParameter] public ?int $impressions,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['utm_campaign','utm_source','utm_medium','referrer_domain','registrations','attendees','impressions'], []);
        return new self(
            property_exists($value, 'utm_campaign') ? ($value->utm_campaign === null ? null : Wire::string($value->utm_campaign)) : null,
            property_exists($value, 'utm_source') ? ($value->utm_source === null ? null : Wire::string($value->utm_source)) : null,
            property_exists($value, 'utm_medium') ? ($value->utm_medium === null ? null : Wire::string($value->utm_medium)) : null,
            property_exists($value, 'referrer_domain') ? ($value->referrer_domain === null ? null : Wire::string($value->referrer_domain)) : null,
            property_exists($value, 'registrations') ? ($value->registrations === null ? null : Wire::integer($value->registrations)) : null,
            property_exists($value, 'attendees') ? ($value->attendees === null ? null : Wire::integer($value->attendees)) : null,
            property_exists($value, 'impressions') ? ($value->impressions === null ? null : Wire::integer($value->impressions)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

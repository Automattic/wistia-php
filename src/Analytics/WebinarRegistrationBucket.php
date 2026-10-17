<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class WebinarRegistrationBucket
{
    public function __construct(
        #[\SensitiveParameter] public ?string $timestamp,
        #[\SensitiveParameter] public ?int $registrations,
        #[\SensitiveParameter] public ?int $impressions,
        #[\SensitiveParameter] public ?int $on_demand_registrations,
        #[\SensitiveParameter] public ?int $on_demand_impressions,
        #[\SensitiveParameter] public ?float $completion_rate,
        #[\SensitiveParameter] public ?float $on_demand_completion_rate,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['timestamp','registrations','impressions','on_demand_registrations','on_demand_impressions','completion_rate','on_demand_completion_rate'], []);
        return new self(
            property_exists($value, 'timestamp') ? Wire::string($value->timestamp) : null,
            property_exists($value, 'registrations') ? ($value->registrations === null ? null : Wire::integer($value->registrations)) : null,
            property_exists($value, 'impressions') ? ($value->impressions === null ? null : Wire::integer($value->impressions)) : null,
            property_exists($value, 'on_demand_registrations') ? ($value->on_demand_registrations === null ? null : Wire::integer($value->on_demand_registrations)) : null,
            property_exists($value, 'on_demand_impressions') ? ($value->on_demand_impressions === null ? null : Wire::integer($value->on_demand_impressions)) : null,
            property_exists($value, 'completion_rate') ? ($value->completion_rate === null ? null : Wire::number($value->completion_rate)) : null,
            property_exists($value, 'on_demand_completion_rate') ? ($value->on_demand_completion_rate === null ? null : Wire::number($value->on_demand_completion_rate)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

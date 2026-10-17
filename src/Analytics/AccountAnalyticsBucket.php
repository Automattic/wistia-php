<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class AccountAnalyticsBucket
{
    public function __construct(
        #[\SensitiveParameter] public string|null $timestamp,
        #[\SensitiveParameter] public int|null $unique_loads,
        #[\SensitiveParameter] public int|null $unique_plays,
        #[\SensitiveParameter] public int|null $plays,
        #[\SensitiveParameter] public float|null $play_rate,
        #[\SensitiveParameter] public int|null $played_time,
        #[\SensitiveParameter] public int|null $unique_visitors,
        #[\SensitiveParameter] public float|null $engagement_rate,
        #[\SensitiveParameter] public int|null $form_conversions,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['timestamp','unique_loads','unique_plays','plays','play_rate','played_time','unique_visitors','engagement_rate','form_conversions'], []);
        return new self(
            property_exists($value, 'timestamp') ? Wire::string($value->timestamp) : null,
            property_exists($value, 'unique_loads') && $value->unique_loads !== null ? Wire::integer($value->unique_loads) : null,
            property_exists($value, 'unique_plays') && $value->unique_plays !== null ? Wire::integer($value->unique_plays) : null,
            property_exists($value, 'plays') && $value->plays !== null ? Wire::integer($value->plays) : null,
            property_exists($value, 'play_rate') && $value->play_rate !== null ? Wire::number($value->play_rate) : null,
            property_exists($value, 'played_time') && $value->played_time !== null ? Wire::integer($value->played_time) : null,
            property_exists($value, 'unique_visitors') && $value->unique_visitors !== null ? Wire::integer($value->unique_visitors) : null,
            property_exists($value, 'engagement_rate') && $value->engagement_rate !== null ? Wire::number($value->engagement_rate) : null,
            property_exists($value, 'form_conversions') && $value->form_conversions !== null ? Wire::integer($value->form_conversions) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

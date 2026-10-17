<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class DailyStats
{
    public function __construct(
        #[\SensitiveParameter] public string|null $date,
        #[\SensitiveParameter] public int|null $load_count,
        #[\SensitiveParameter] public int|null $play_count,
        #[\SensitiveParameter] public float|null $hours_watched,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['date', 'load_count', 'play_count', 'hours_watched'], []);
        return new self(
            property_exists($value, 'date') ? (Wire::string($value->date)) : null,
            property_exists($value, 'load_count') ? (Wire::integer($value->load_count)) : null,
            property_exists($value, 'play_count') ? (Wire::integer($value->play_count)) : null,
            property_exists($value, 'hours_watched') ? (Wire::number($value->hours_watched)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

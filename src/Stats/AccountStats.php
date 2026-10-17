<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class AccountStats
{
    public function __construct(
        #[\SensitiveParameter] public int $load_count,
        #[\SensitiveParameter] public int $play_count,
        #[\SensitiveParameter] public float $hours_watched,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['load_count', 'play_count', 'hours_watched'], ['load_count', 'play_count', 'hours_watched']);
        return new self(
            Wire::integer($value->load_count),
            Wire::integer($value->play_count),
            Wire::number($value->hours_watched),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

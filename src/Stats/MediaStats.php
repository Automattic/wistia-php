<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class MediaStats
{
    public function __construct(
        #[\SensitiveParameter] public int|null $load_count,
        #[\SensitiveParameter] public int|null $play_count,
        #[\SensitiveParameter] public float|null $play_rate,
        #[\SensitiveParameter] public float|null $hours_watched,
        #[\SensitiveParameter] public float|null $engagement,
        #[\SensitiveParameter] public int|null $visitors,
        /** @var list<ActionStats>|null */
        #[\SensitiveParameter] public array|null $actions,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['load_count', 'play_count', 'play_rate', 'hours_watched', 'engagement', 'visitors', 'actions'], []);
        return new self(
            property_exists($value, 'load_count') ? (Wire::integer($value->load_count)) : null,
            property_exists($value, 'play_count') ? (Wire::integer($value->play_count)) : null,
            property_exists($value, 'play_rate') ? (Wire::number($value->play_rate)) : null,
            property_exists($value, 'hours_watched') ? (Wire::number($value->hours_watched)) : null,
            property_exists($value, 'engagement') ? (Wire::number($value->engagement)) : null,
            property_exists($value, 'visitors') ? (Wire::integer($value->visitors)) : null,
            property_exists($value, 'actions') ? (Wire::items($value->actions, static fn (mixed $item) => ActionStats::fromWire($item))) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

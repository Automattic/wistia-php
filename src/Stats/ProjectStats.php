<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class ProjectStats
{
    public function __construct(
        #[\SensitiveParameter] public int|null $load_count,
        #[\SensitiveParameter] public int|null $play_count,
        #[\SensitiveParameter] public float|null $hours_watched,
        #[\SensitiveParameter] public int|null $number_of_videos,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['load_count', 'play_count', 'hours_watched', 'number_of_videos'], []);
        return new self(
            property_exists($value, 'load_count') ? (Wire::integer($value->load_count)) : null,
            property_exists($value, 'play_count') ? (Wire::integer($value->play_count)) : null,
            property_exists($value, 'hours_watched') ? (Wire::number($value->hours_watched)) : null,
            property_exists($value, 'number_of_videos') ? (Wire::integer($value->number_of_videos)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

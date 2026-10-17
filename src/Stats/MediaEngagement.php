<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class MediaEngagement
{
    public function __construct(
        #[\SensitiveParameter] public float|null $engagement,
        /** @var list<int>|null */
        #[\SensitiveParameter] public array|null $engagement_data,
        /** @var list<int>|null */
        #[\SensitiveParameter] public array|null $rewatch_data,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['engagement', 'engagement_data', 'rewatch_data'], []);
        return new self(
            property_exists($value, 'engagement') ? (Wire::number($value->engagement)) : null,
            property_exists($value, 'engagement_data') ? (Wire::items($value->engagement_data, static fn (mixed $item) => Wire::integer($item))) : null,
            property_exists($value, 'rewatch_data') ? (Wire::items($value->rewatch_data, static fn (mixed $item) => Wire::integer($item))) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Analytics\{RankedMedia, RankedChannel, RankedProject};
use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class ShowAccountTopContentResult implements Result
{
    /** @param list<RankedMedia|RankedChannel|RankedProject> $data */
    public function __construct(#[\SensitiveParameter] public array $data, public int $status) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        return new self(Wire::items($value, static function (#[\SensitiveParameter] mixed $item): RankedMedia|RankedChannel|RankedProject {
            if (!$item instanceof \stdClass) { throw new \UnexpectedValueException('Expected ranked content.'); }
            return match (true) {
                property_exists($item, 'media_hashed_id') => RankedMedia::fromWire($item),
                property_exists($item, 'channel_hashed_id') => RankedChannel::fromWire($item),
                property_exists($item, 'project_hashed_id') => RankedProject::fromWire($item),
                default => throw new \UnexpectedValueException('Missing ranked content discriminator.'),
            };
        }), $status);
    }
    public function __debugInfo(): array { return []; }
}

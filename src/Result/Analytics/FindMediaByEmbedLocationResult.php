<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Analytics;

use Automattic\Wistia\Media\Wire;
use Automattic\Wistia\Result\Result;

final readonly class FindMediaByEmbedLocationResult implements Result
{
    /** @param list<string>|null $media_hashed_ids */
    public function __construct(#[\SensitiveParameter] public ?array $media_hashed_ids, public int $status, /** @var list<string> */ public array $presentFields) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value, int $status): self
    {
        if ($status !== 200) { throw new \UnexpectedValueException('Unexpected endpoint status.'); }
        Wire::object($value, ['media_hashed_ids'], []);
        return new self(property_exists($value, 'media_hashed_ids') ? Wire::items($value->media_hashed_ids, Wire::string(...)) : null, $status, array_keys(get_object_vars($value)));
    }
    public function __debugInfo(): array { return []; }
}

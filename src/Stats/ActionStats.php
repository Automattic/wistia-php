<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class ActionStats
{
    public function __construct(
        #[\SensitiveParameter] public string|null $type,
        #[\SensitiveParameter] public int|null $action_count,
        #[\SensitiveParameter] public int|null $impression_count,
        #[\SensitiveParameter] public float|null $rate,
        #[\SensitiveParameter] public string|null $url,
        #[\SensitiveParameter] public string|null $text,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['type', 'action_count', 'impression_count', 'rate', 'url', 'text'], []);
        return new self(
            property_exists($value, 'type') ? (Wire::string($value->type)) : null,
            property_exists($value, 'action_count') ? (Wire::integer($value->action_count)) : null,
            property_exists($value, 'impression_count') ? (Wire::integer($value->impression_count)) : null,
            property_exists($value, 'rate') ? (Wire::number($value->rate)) : null,
            property_exists($value, 'url') ? ($value->url === null ? null : (Wire::string($value->url))) : null,
            property_exists($value, 'text') ? ($value->text === null ? null : (Wire::string($value->text))) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

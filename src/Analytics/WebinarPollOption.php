<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class WebinarPollOption
{
    public function __construct(
        #[\SensitiveParameter] public ?int $id,
        #[\SensitiveParameter] public ?string $text,
        #[\SensitiveParameter] public ?int $count,
        #[\SensitiveParameter] public ?float $percent,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id','text','count','percent'], []);
        return new self(
            property_exists($value, 'id') ? Wire::integer($value->id) : null,
            property_exists($value, 'text') ? Wire::string($value->text) : null,
            property_exists($value, 'count') ? Wire::integer($value->count) : null,
            property_exists($value, 'percent') ? Wire::number($value->percent) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

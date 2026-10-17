<?php
declare(strict_types=1);

namespace Automattic\Wistia\Analytics;

use Automattic\Wistia\Media\Wire;

final readonly class MediaLanguage
{
    public function __construct(
        #[\SensitiveParameter] public string|null $language,
        #[\SensitiveParameter] public int|null $plays,
        #[\SensitiveParameter] public float|null $percent_of_total_plays,
        #[\SensitiveParameter] public bool|null $captions_support,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['language','plays','percent_of_total_plays','captions_support'], []);
        return new self(
            property_exists($value, 'language') ? Wire::string($value->language) : null,
            property_exists($value, 'plays') ? Wire::integer($value->plays) : null,
            property_exists($value, 'percent_of_total_plays') ? Wire::number($value->percent_of_total_plays) : null,
            property_exists($value, 'captions_support') ? Wire::boolean($value->captions_support) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

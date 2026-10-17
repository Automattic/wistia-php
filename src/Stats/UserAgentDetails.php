<?php
declare(strict_types=1);

namespace Automattic\Wistia\Stats;

use Automattic\Wistia\Media\Wire;

final readonly class UserAgentDetails
{
    public function __construct(
        #[\SensitiveParameter] public string|null $browser,
        #[\SensitiveParameter] public string|null $browser_version,
        #[\SensitiveParameter] public string|null $platform,
        #[\SensitiveParameter] public bool|null $mobile,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['browser', 'browser_version', 'platform', 'mobile'], []);
        return new self(
            property_exists($value, 'browser') ? (Wire::string($value->browser)) : null,
            property_exists($value, 'browser_version') ? (Wire::string($value->browser_version)) : null,
            property_exists($value, 'platform') ? (Wire::string($value->platform)) : null,
            property_exists($value, 'mobile') ? (Wire::boolean($value->mobile)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

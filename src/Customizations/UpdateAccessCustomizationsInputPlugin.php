<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateAccessCustomizationsInputPlugin implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateAccessCustomizationsInputPluginPasswordProtectedVideo|null|Omitted $passwordProtectedVideo = Omitted::Value,
    ) {
        if ($this->passwordProtectedVideo !== Omitted::Value && $this->passwordProtectedVideo !== null) { Shape::check(Shape::encode($this->passwordProtectedVideo), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'challenge' => ['type' => 'string'], 'src' => ['type' => 'string'], 'async' => ['type' => 'boolean']]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->passwordProtectedVideo !== Omitted::Value) { $out->{'passwordProtectedVideo'} = Shape::encode($this->passwordProtectedVideo); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}

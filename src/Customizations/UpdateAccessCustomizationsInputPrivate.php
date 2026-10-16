<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateAccessCustomizationsInputPrivate implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $password_protect_on = Omitted::Value,
    ) {
        if ($this->password_protect_on !== Omitted::Value && $this->password_protect_on !== null) { Shape::check(Shape::encode($this->password_protect_on), ['type' => 'boolean'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->password_protect_on !== Omitted::Value) { $out->{'password_protect_on'} = Shape::encode($this->password_protect_on); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}

<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateAccessCustomizationsInputEncrypted implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|null|Omitted $password_protect_password = Omitted::Value,
    ) {
        if ($this->password_protect_password !== Omitted::Value && $this->password_protect_password !== null) { Shape::check(Shape::encode($this->password_protect_password), ['type' => 'string'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->password_protect_password !== Omitted::Value) { $out->{'password_protect_password'} = Shape::encode($this->password_protect_password); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}

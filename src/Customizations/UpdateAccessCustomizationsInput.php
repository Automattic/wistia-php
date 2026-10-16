<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateAccessCustomizationsInput implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateAccessCustomizationsInputPrivate|null|Omitted $private = Omitted::Value,
        #[\SensitiveParameter] public UpdateAccessCustomizationsInputEncrypted|null|Omitted $encrypted = Omitted::Value,
        #[\SensitiveParameter] public UpdateAccessCustomizationsInputPlugin|null|Omitted $plugin = Omitted::Value,
    ) {
        if ($this->private !== Omitted::Value && $this->private !== null) { Shape::check(Shape::encode($this->private), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['password_protect_on' => ['type' => 'boolean']]], true); }
        if ($this->encrypted !== Omitted::Value && $this->encrypted !== null) { Shape::check(Shape::encode($this->encrypted), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['password_protect_password' => ['type' => 'string']]], true); }
        if ($this->plugin !== Omitted::Value && $this->plugin !== null) { Shape::check(Shape::encode($this->plugin), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['passwordProtectedVideo' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'challenge' => ['type' => 'string'], 'src' => ['type' => 'string'], 'async' => ['type' => 'boolean']]]]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->private !== Omitted::Value) { $out->{'private'} = Shape::encode($this->private); }
        if ($this->encrypted !== Omitted::Value) { $out->{'encrypted'} = Shape::encode($this->encrypted); }
        if ($this->plugin !== Omitted::Value) { $out->{'plugin'} = Shape::encode($this->plugin); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}

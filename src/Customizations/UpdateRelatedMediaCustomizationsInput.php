<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateRelatedMediaCustomizationsInput implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateRelatedMediaCustomizationsInputPlugin|null|Omitted $plugin = Omitted::Value,
    ) {
        if ($this->plugin !== Omitted::Value && $this->plugin !== null) { Shape::check(Shape::encode($this->plugin), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['relatedMedia' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'hashedIdList' => ['type' => 'array', 'items' => ['type' => 'string']], 'shouldShowOnPause' => ['type' => 'boolean'], 'shouldShowOnEnd' => ['type' => 'boolean'], 'mediaLabelText' => ['type' => 'string'], 'watchButtonText' => ['type' => 'string']]]]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->plugin !== Omitted::Value) { $out->{'plugin'} = Shape::encode($this->plugin); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}

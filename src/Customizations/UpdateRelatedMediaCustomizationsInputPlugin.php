<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateRelatedMediaCustomizationsInputPlugin implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public UpdateRelatedMediaCustomizationsInputPluginRelatedMedia|null|Omitted $relatedMedia = Omitted::Value,
    ) {
        if ($this->relatedMedia !== Omitted::Value && $this->relatedMedia !== null) { Shape::check(Shape::encode($this->relatedMedia), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'hashedIdList' => ['type' => 'array', 'items' => ['type' => 'string']], 'shouldShowOnPause' => ['type' => 'boolean'], 'shouldShowOnEnd' => ['type' => 'boolean'], 'mediaLabelText' => ['type' => 'string'], 'watchButtonText' => ['type' => 'string']]], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->relatedMedia !== Omitted::Value) { $out->{'relatedMedia'} = Shape::encode($this->relatedMedia); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}

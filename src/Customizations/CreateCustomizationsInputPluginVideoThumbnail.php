<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class CreateCustomizationsInputPluginVideoThumbnail implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $clickToPlayButton = Omitted::Value,
    ) {
        if ($this->clickToPlayButton !== Omitted::Value) { Shape::check(Shape::encode($this->clickToPlayButton), ['type' => 'boolean'], false); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->clickToPlayButton !== Omitted::Value) { $out->{'clickToPlayButton'} = Shape::encode($this->clickToPlayButton); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}

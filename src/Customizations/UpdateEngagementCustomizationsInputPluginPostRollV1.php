<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateEngagementCustomizationsInputPluginPostRollV1 implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $rewatch = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $text = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $link = Omitted::Value,
        #[\SensitiveParameter] public string|float|null|Omitted $time = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $autoSize = Omitted::Value,
        #[\SensitiveParameter] public UpdateEngagementCustomizationsInputPluginPostRollV1Style|null|Omitted $style = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $ctaType = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $on = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $conversionOpportunityKey = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $altText = Omitted::Value,
    ) {
        if ($this->rewatch !== Omitted::Value && $this->rewatch !== null) { Shape::check(Shape::encode($this->rewatch), ['type' => 'boolean'], true); }
        if ($this->text !== Omitted::Value && $this->text !== null) { Shape::check(Shape::encode($this->text), ['type' => 'string'], true); }
        if ($this->link !== Omitted::Value && $this->link !== null) { Shape::check(Shape::encode($this->link), ['type' => 'string'], true); }
        if ($this->time !== Omitted::Value && $this->time !== null) { Shape::check(Shape::encode($this->time), ['unevaluatedProperties' => false, 'oneOf' => [['type' => 'string'], ['type' => 'number']]], true); }
        if ($this->autoSize !== Omitted::Value && $this->autoSize !== null) { Shape::check(Shape::encode($this->autoSize), ['type' => 'boolean'], true); }
        if ($this->style !== Omitted::Value && $this->style !== null) { Shape::check(Shape::encode($this->style), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['backgroundColor' => ['type' => 'string']]], true); }
        if ($this->ctaType !== Omitted::Value && $this->ctaType !== null) { Shape::check(Shape::encode($this->ctaType), ['type' => 'string', 'enum' => ['text', 'image', 'html']], true); }
        if ($this->on !== Omitted::Value && $this->on !== null) { Shape::check(Shape::encode($this->on), ['type' => 'boolean'], true); }
        if ($this->conversionOpportunityKey !== Omitted::Value && $this->conversionOpportunityKey !== null) { Shape::check(Shape::encode($this->conversionOpportunityKey), ['type' => 'string'], true); }
        if ($this->altText !== Omitted::Value && $this->altText !== null) { Shape::check($this->altText, ['type' => 'string']); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->rewatch !== Omitted::Value) { $out->{'rewatch'} = Shape::encode($this->rewatch); }
        if ($this->text !== Omitted::Value) { $out->{'text'} = Shape::encode($this->text); }
        if ($this->link !== Omitted::Value) { $out->{'link'} = Shape::encode($this->link); }
        if ($this->time !== Omitted::Value) { $out->{'time'} = Shape::encode($this->time); }
        if ($this->autoSize !== Omitted::Value) { $out->{'autoSize'} = Shape::encode($this->autoSize); }
        if ($this->style !== Omitted::Value) { $out->{'style'} = Shape::encode($this->style); }
        if ($this->ctaType !== Omitted::Value) { $out->{'ctaType'} = Shape::encode($this->ctaType); }
        if ($this->on !== Omitted::Value) { $out->{'on'} = Shape::encode($this->on); }
        if ($this->conversionOpportunityKey !== Omitted::Value) { $out->{'conversionOpportunityKey'} = Shape::encode($this->conversionOpportunityKey); }
        if ($this->altText !== Omitted::Value) { $out->{'altText'} = $this->altText; }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}

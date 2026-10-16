<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateAppearanceCustomizationsInput implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|null|Omitted $playerColor = Omitted::Value,
        #[\SensitiveParameter] public UpdateAppearanceCustomizationsInputPlayerColorGradient|null|Omitted $playerColorGradient = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $roundedPlayer = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $opaqueControls = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $contrastIcons = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $branding = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $showCustomerLogo = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $customerLogoImageUrl = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $customerLogoTargetUrl = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $customerLogoPlacement = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $customerLogoSizePercent = Omitted::Value,
    ) {
        if ($this->playerColor !== Omitted::Value && $this->playerColor !== null) { Shape::check(Shape::encode($this->playerColor), ['type' => 'string'], true); }
        if ($this->playerColorGradient !== Omitted::Value && $this->playerColorGradient !== null) { Shape::check(Shape::encode($this->playerColorGradient), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'colors' => ['type' => 'array', 'items' => ['type' => 'array']]]], true); }
        if ($this->roundedPlayer !== Omitted::Value && $this->roundedPlayer !== null) { Shape::check(Shape::encode($this->roundedPlayer), ['type' => 'integer'], true); }
        if ($this->opaqueControls !== Omitted::Value && $this->opaqueControls !== null) { Shape::check(Shape::encode($this->opaqueControls), ['type' => 'boolean'], true); }
        if ($this->contrastIcons !== Omitted::Value && $this->contrastIcons !== null) { Shape::check(Shape::encode($this->contrastIcons), ['type' => 'boolean'], true); }
        if ($this->branding !== Omitted::Value && $this->branding !== null) { Shape::check(Shape::encode($this->branding), ['type' => 'boolean'], true); }
        if ($this->showCustomerLogo !== Omitted::Value && $this->showCustomerLogo !== null) { Shape::check(Shape::encode($this->showCustomerLogo), ['type' => 'boolean'], true); }
        if ($this->customerLogoImageUrl !== Omitted::Value && $this->customerLogoImageUrl !== null) { Shape::check(Shape::encode($this->customerLogoImageUrl), ['type' => 'string'], true); }
        if ($this->customerLogoTargetUrl !== Omitted::Value && $this->customerLogoTargetUrl !== null) { Shape::check(Shape::encode($this->customerLogoTargetUrl), ['type' => 'string'], true); }
        if ($this->customerLogoPlacement !== Omitted::Value && $this->customerLogoPlacement !== null) { Shape::check(Shape::encode($this->customerLogoPlacement), ['type' => 'string'], true); }
        if ($this->customerLogoSizePercent !== Omitted::Value && $this->customerLogoSizePercent !== null) { Shape::check(Shape::encode($this->customerLogoSizePercent), ['type' => 'integer'], true); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->playerColor !== Omitted::Value) { $out->{'playerColor'} = Shape::encode($this->playerColor); }
        if ($this->playerColorGradient !== Omitted::Value) { $out->{'playerColorGradient'} = Shape::encode($this->playerColorGradient); }
        if ($this->roundedPlayer !== Omitted::Value) { $out->{'roundedPlayer'} = Shape::encode($this->roundedPlayer); }
        if ($this->opaqueControls !== Omitted::Value) { $out->{'opaqueControls'} = Shape::encode($this->opaqueControls); }
        if ($this->contrastIcons !== Omitted::Value) { $out->{'contrastIcons'} = Shape::encode($this->contrastIcons); }
        if ($this->branding !== Omitted::Value) { $out->{'branding'} = Shape::encode($this->branding); }
        if ($this->showCustomerLogo !== Omitted::Value) { $out->{'showCustomerLogo'} = Shape::encode($this->showCustomerLogo); }
        if ($this->customerLogoImageUrl !== Omitted::Value) { $out->{'customerLogoImageUrl'} = Shape::encode($this->customerLogoImageUrl); }
        if ($this->customerLogoTargetUrl !== Omitted::Value) { $out->{'customerLogoTargetUrl'} = Shape::encode($this->customerLogoTargetUrl); }
        if ($this->customerLogoPlacement !== Omitted::Value) { $out->{'customerLogoPlacement'} = Shape::encode($this->customerLogoPlacement); }
        if ($this->customerLogoSizePercent !== Omitted::Value) { $out->{'customerLogoSizePercent'} = Shape::encode($this->customerLogoSizePercent); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}

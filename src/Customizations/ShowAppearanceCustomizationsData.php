<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Typed wire record; values remain strings where documented. */
final readonly class ShowAppearanceCustomizationsData
{
    public function __construct(
        #[\SensitiveParameter] public string|null $playerColor,
        #[\SensitiveParameter] public ShowAppearanceCustomizationsDataPlayerColorGradient|null $playerColorGradient,
        #[\SensitiveParameter] public string|null $roundedPlayer,
        #[\SensitiveParameter] public string|null $opaqueControls,
        #[\SensitiveParameter] public string|null $contrastIcons,
        #[\SensitiveParameter] public string|null $branding,
        #[\SensitiveParameter] public string|null $showCustomerLogo,
        #[\SensitiveParameter] public string|null $customerLogoImageUrl,
        #[\SensitiveParameter] public string|null $customerLogoTargetUrl,
        #[\SensitiveParameter] public string|null $customerLogoPlacement,
        #[\SensitiveParameter] public string|null $customerLogoSizePercent,
        public array $presentFields,
        /** Extensible source-defined properties only. */
        #[\SensitiveParameter] public \stdClass $additionalFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Shape::check($value, ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['playerColor' => ['type' => 'string'], 'playerColorGradient' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'string'], 'colors' => ['type' => 'array', 'items' => ['type' => 'array']]]], 'roundedPlayer' => ['type' => 'string'], 'opaqueControls' => ['type' => 'string'], 'contrastIcons' => ['type' => 'string'], 'branding' => ['type' => 'string'], 'showCustomerLogo' => ['type' => 'string'], 'customerLogoImageUrl' => ['type' => 'string'], 'customerLogoTargetUrl' => ['type' => 'string'], 'customerLogoPlacement' => ['type' => 'string'], 'customerLogoSizePercent' => ['type' => 'string']]]);
        return new self(
            property_exists($value, 'playerColor') ? $value->{'playerColor'} : null,
            property_exists($value, 'playerColorGradient') ? ShowAppearanceCustomizationsDataPlayerColorGradient::fromWire($value->{'playerColorGradient'}) : null,
            property_exists($value, 'roundedPlayer') ? $value->{'roundedPlayer'} : null,
            property_exists($value, 'opaqueControls') ? $value->{'opaqueControls'} : null,
            property_exists($value, 'contrastIcons') ? $value->{'contrastIcons'} : null,
            property_exists($value, 'branding') ? $value->{'branding'} : null,
            property_exists($value, 'showCustomerLogo') ? $value->{'showCustomerLogo'} : null,
            property_exists($value, 'customerLogoImageUrl') ? $value->{'customerLogoImageUrl'} : null,
            property_exists($value, 'customerLogoTargetUrl') ? $value->{'customerLogoTargetUrl'} : null,
            property_exists($value, 'customerLogoPlacement') ? $value->{'customerLogoPlacement'} : null,
            property_exists($value, 'customerLogoSizePercent') ? $value->{'customerLogoSizePercent'} : null,
            array_keys(get_object_vars($value)),
            (object) array_diff_key(get_object_vars($value), array_flip(['playerColor', 'playerColorGradient', 'roundedPlayer', 'opaqueControls', 'contrastIcons', 'branding', 'showCustomerLogo', 'customerLogoImageUrl', 'customerLogoTargetUrl', 'customerLogoPlacement', 'customerLogoSizePercent'])),
        );
    }
    public function __debugInfo(): array { return []; }
}

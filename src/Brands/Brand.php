<?php
declare(strict_types=1);

namespace Automattic\Wistia\Brands;

use Automattic\Wistia\Media\Wire;

/** Typed response; presentFields distinguishes omission from explicit null. */
final readonly class Brand
{
    public function __construct(
        #[\SensitiveParameter] public string|null $id,
        #[\SensitiveParameter] public string|null $name,
        #[\SensitiveParameter] public bool|null $is_default,
        #[\SensitiveParameter] public string|null $created,
        #[\SensitiveParameter] public string|null $updated,
        #[\SensitiveParameter] public string|Gradient|null $primary_color,
        #[\SensitiveParameter] public string|Gradient|null $page_background_color,
        #[\SensitiveParameter] public string|null $body_font_family,
        #[\SensitiveParameter] public string|null $headline_font_family,
        #[\SensitiveParameter] public string|null $button_font_family,
        #[\SensitiveParameter] public int|null $border_radius,
        #[\SensitiveParameter] public string|null $contrast_icons,
        #[\SensitiveParameter] public string|null $opaque_controls,
        #[\SensitiveParameter] public BrandPageLogo|null $page_logo,
        #[\SensitiveParameter] public BrandPlayerLogo|null $player_logo,
        #[\SensitiveParameter] public string|null $cursor,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['id', 'name', 'is_default', 'created', 'updated', 'primary_color', 'page_background_color', 'body_font_family', 'headline_font_family', 'button_font_family', 'border_radius', 'contrast_icons', 'opaque_controls', 'page_logo', 'player_logo', 'cursor'], []);
        return new self(
            property_exists($value, 'id') ? Wire::string($value->id) : null,
            property_exists($value, 'name') ? Wire::string($value->name) : null,
            property_exists($value, 'is_default') ? Wire::boolean($value->is_default) : null,
            property_exists($value, 'created') ? Wire::string($value->created) : null,
            property_exists($value, 'updated') ? Wire::string($value->updated) : null,
            property_exists($value, 'primary_color') ? ($value->primary_color === null ? null : Color::fromWire($value->primary_color)) : null,
            property_exists($value, 'page_background_color') ? ($value->page_background_color === null ? null : Color::fromWire($value->page_background_color)) : null,
            property_exists($value, 'body_font_family') ? ($value->body_font_family === null ? null : Wire::string($value->body_font_family)) : null,
            property_exists($value, 'headline_font_family') ? ($value->headline_font_family === null ? null : Wire::string($value->headline_font_family)) : null,
            property_exists($value, 'button_font_family') ? ($value->button_font_family === null ? null : Wire::string($value->button_font_family)) : null,
            property_exists($value, 'border_radius') ? ($value->border_radius === null ? null : Wire::integer($value->border_radius)) : null,
            property_exists($value, 'contrast_icons') ? ($value->contrast_icons === null ? null : Wire::oneOf(Wire::string($value->contrast_icons), ['enabled', 'disabled', 'unset'])) : null,
            property_exists($value, 'opaque_controls') ? ($value->opaque_controls === null ? null : Wire::oneOf(Wire::string($value->opaque_controls), ['enabled', 'disabled', 'unset'])) : null,
            property_exists($value, 'page_logo') ? ($value->page_logo === null ? null : BrandPageLogo::fromWire($value->page_logo)) : null,
            property_exists($value, 'player_logo') ? ($value->player_logo === null ? null : BrandPlayerLogo::fromWire($value->player_logo)) : null,
            property_exists($value, 'cursor') ? ($value->cursor === null ? null : Wire::string($value->cursor)) : null,
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}

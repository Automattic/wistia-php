<?php
declare(strict_types=1);
namespace Automattic\Wistia\Brands;

use Automattic\Wistia\Media\Input;

/** Flat exact-name fields. Omitted leaves unchanged; null resets a nullable field. */
final readonly class BrandAttributes implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|Omitted $name = Omitted::Value,
        #[\SensitiveParameter] public string|Gradient|null|Omitted $primary_color = Omitted::Value,
        #[\SensitiveParameter] public string|Gradient|null|Omitted $page_background_color = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $body_font_family = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $headline_font_family = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $button_font_family = Omitted::Value,
        public int|null|Omitted $border_radius = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $contrast_icons = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $opaque_controls = Omitted::Value,
        #[\SensitiveParameter] public LogoInput|null|Omitted $page_logo = Omitted::Value,
        #[\SensitiveParameter] public LogoInput|null|Omitted $player_logo = Omitted::Value,
    ) {
        foreach ([$primary_color, $page_background_color] as $color) {
            if (is_string($color)) { Color::validate($color); }
        }
        foreach ([$contrast_icons, $opaque_controls] as $control) {
            if (is_string($control)) { Input::oneOf($control, ['enabled', 'disabled', 'unset']); }
        }
    }
    public function jsonSerialize(): object { return (object) array_filter(get_object_vars($this), static fn($v) => $v !== Omitted::Value); }
    public function __debugInfo(): array { return []; }
}

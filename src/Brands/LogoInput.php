<?php
declare(strict_types=1);
namespace Automattic\Wistia\Brands;

/** Uses an existing account image, not an upload or arbitrary URL import. */
final readonly class LogoInput implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string|Omitted $url = Omitted::Value,
        public LogoDimensionsInput|null|Omitted $dimensions = Omitted::Value,
        public float|null|Omitted $size = Omitted::Value,
    ) {
        if ($url !== Omitted::Value && preg_match('~\Ahttps://embed-ssl[.]wistia[.]com/deliveries/[A-Za-z0-9_-]+[.][A-Za-z0-9]+\z~D', $url) !== 1) {
            throw new \InvalidArgumentException('Use a Wistia delivery URL for an image already in the account; upload new logos in the Wistia app.');
        }
        if (is_float($size) && !is_finite($size)) { throw new \InvalidArgumentException('Logo size must be finite.'); }
    }
    public function jsonSerialize(): object { return (object) array_filter(get_object_vars($this), static fn($v) => $v !== Omitted::Value); }
    public function __debugInfo(): array { return []; }
}

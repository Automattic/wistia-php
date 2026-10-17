<?php
declare(strict_types=1);

namespace Automattic\Wistia\Upload;

/** @internal Local syntax checks only; remote availability/permissions remain server decisions. */
final class Input
{
    public static function id(#[\SensitiveParameter] string $value): void
    {
        if (!preg_match('/^[a-zA-Z0-9]+$/D', $value)) { throw new \InvalidArgumentException('Use a nonempty media or project hashed ID.'); }
    }

    public static function url(#[\SensitiveParameter] string $url, bool $image = false): void
    {
        $parts = parse_url($url);
        if ($parts === false || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])
            || preg_match('/[\x00-\x20\x7f]/', $url)
            || !preg_match($image ? '/\.(?:jpg|jpeg|png|gif|webp|bmp|tiff?)$/iD' : '/\.[a-z0-9]+$/iD', $parts['path'] ?? '')) {
            throw new \InvalidArgumentException('Use a direct HTTP(S) file URL with a file extension, without user information or a fragment.');
        }
    }

    public static function language(#[\SensitiveParameter] string $value): void
    {
        // RFC 5646 §2.1/§2.2.9 well-formedness, not registry or account-availability validation.
        $lower = strtolower($value);
        $grandfathered = ['en-gb-oed', 'i-ami', 'i-bnn', 'i-default', 'i-enochian', 'i-hak',
            'i-klingon', 'i-lux', 'i-mingo', 'i-navajo', 'i-pwn', 'i-tao', 'i-tay', 'i-tsu',
            'sgn-be-fr', 'sgn-be-nl', 'sgn-ch-de', 'art-lojban', 'cel-gaulish', 'no-bok', 'no-nyn',
            'zh-guoyu', 'zh-hakka', 'zh-min', 'zh-min-nan', 'zh-xiang'];
        if (in_array($lower, $grandfathered, true) || preg_match('/^x(?:-[a-z0-9]{1,8})+$/D', $lower) === 1) { return; }
        // §2.2.2 permanently reserves a second/third extlang; grandfathered exceptions were handled above.
        $pattern = '~\A(?:[a-z]{2,3}(?:-[a-z]{3})?|[a-z]{4}|[a-z]{5,8})'
            . '(?:-[a-z]{4})?(?:-(?:[a-z]{2}|[0-9]{3}))?'
            . '(?<variants>(?:-(?:[a-z0-9]{5,8}|[0-9][a-z0-9]{3}))*)'
            . '(?<extensions>(?:-[0-9a-wy-z](?:-[a-z0-9]{2,8})+)*)'
            . '(?:-x(?:-[a-z0-9]{1,8})+)?\z~';
        if (preg_match($pattern, $lower, $matches) !== 1) {
            throw new \InvalidArgumentException('Use a well-formed IETF language tag.');
        }
        $variants = array_filter(explode('-', $matches['variants']));
        $singletons = array_filter(explode('-', $matches['extensions']), static fn (string $v): bool => strlen($v) === 1);
        if (count($variants) !== count(array_unique($variants)) || count($singletons) !== count(array_unique($singletons))) {
            throw new \InvalidArgumentException('Language tags cannot repeat variants or extension singletons.');
        }
    }
}

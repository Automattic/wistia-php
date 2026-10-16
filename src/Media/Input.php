<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

/** @internal Local checks; remote entitlement, ownership and processing remain remote validation. */
final class Input
{
    public static function nonempty(#[\SensitiveParameter] string $v): void
    {
        if (trim($v) === '') { throw new \InvalidArgumentException('A nonempty value is required.'); }
    }
    public static function oneOf(#[\SensitiveParameter] mixed $v, array $allowed): void
    {
        if (!in_array($v, $allowed, true)) { throw new \InvalidArgumentException('Value is not supported for this request.'); }
    }
    public static function strings(#[\SensitiveParameter] array $v): void
    {
        if (!array_is_list($v)) { throw new \InvalidArgumentException('Expected a list of strings.'); }
        foreach ($v as $item) {
            if (!is_string($item) || trim($item) === '') { throw new \InvalidArgumentException('Expected nonempty strings.'); }
        }
    }
    public static function count(#[\SensitiveParameter] array $v, int $min, ?int $max = null): void
    {
        if (count($v) < $min || ($max !== null && count($v) > $max)) {
            throw new \InvalidArgumentException('List size is outside this endpoint’s limits.');
        }
    }
    public static function url(#[\SensitiveParameter] string $url): void
    {
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['host']) || $parts['host'] === ''
            || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || isset($parts['user']) || isset($parts['pass']) || preg_match('/[\x00-\x20\x7f]/', $url)) {
            throw new \InvalidArgumentException('Supply an absolute HTTP(S) media URL without embedded credentials.');
        }
        // RFC 3986 URI syntax only: no fetching, DNS checks or rewriting signed bytes.
        $pct = '%[0-9A-Fa-f]{2}';
        $regName = "(?:[A-Za-z0-9._\\~!$&'()*+,;=-]|$pct)";
        $pchar = "(?:[A-Za-z0-9._\\~!$&'()*+,;=:@-]|$pct)";
        $host = $parts['host'];
        if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
            $literal = substr($host, 1, -1);
            $validHost = filter_var($literal, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false
                || preg_match("~\\Av[0-9a-f]+\\.[A-Za-z0-9._\\~!$&'()*+,;=:-]+\\z~iD", $literal) === 1;
        } else {
            $validHost = preg_match("~\\A$regName+\\z~D", $host) === 1;
        }
        if (!$validHost
            || preg_match("~\\A(?:$pchar|/)*\\z~D", $parts['path'] ?? '') !== 1
            || preg_match("~\\A(?:$pchar|[/?])*\\z~D", $parts['query'] ?? '') !== 1
            || preg_match("~\\A(?:$pchar|[/?])*\\z~D", $parts['fragment'] ?? '') !== 1) {
            throw new \InvalidArgumentException('Supply valid HTTP(S) URI syntax; percent-encode characters outside the URI character set.');
        }
    }
    public static function tag(#[\SensitiveParameter] string $name): void
    {
        // Wistia stores lowercased/squished names; do not change the caller’s bytes.
        $squished = preg_replace('/\s+/u', ' ', trim($name));
        if ($squished === null || preg_match_all('/./us', $squished) > 50 || $squished === '') {
            throw new \InvalidArgumentException('A tag name must contain 1–50 characters after whitespace squishing.');
        }
    }
    public static function trims(#[\SensitiveParameter] array $ranges): void
    {
        self::count($ranges, 1);
        foreach ($ranges as $range) {
            if (!preg_match('/^(\d{2}):([0-5]\d):([0-5]\d)\.(\d{3})-(\d{2}):([0-5]\d):([0-5]\d)\.(\d{3})$/D', $range, $m)) {
                throw new \InvalidArgumentException('Trim ranges must use HH:MM:SS.mmm-HH:MM:SS.mmm.');
            }
            $start = ((int) $m[1] * 3600 + (int) $m[2] * 60 + (int) $m[3]) * 1000 + (int) $m[4];
            $end = ((int) $m[5] * 3600 + (int) $m[6] * 60 + (int) $m[7]) * 1000 + (int) $m[8];
            if ($end <= $start) { throw new \InvalidArgumentException('A trim range must end after it starts.'); }
        }
    }
}

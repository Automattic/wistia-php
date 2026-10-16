<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

/** Only validated rate-limit values are retained, never arbitrary response headers. */
final readonly class RateLimitInfo
{
    public function __construct(
        public ?string $retryAfter = null,
        public ?int $limit = null,
        public ?int $remaining = null,
        public ?int $reset = null,
    ) {}

    public static function fromResponse(#[\SensitiveParameter] TransportResponse $response): self
    {
        $retry = $response->header('Retry-After');
        if ($retry !== null && !preg_match('/^\d{1,10}$/D', $retry)) {
            $date = preg_match('/^[A-Z][a-z]{2}, \d{2} [A-Z][a-z]{2} \d{4} \d{2}:\d{2}:\d{2} GMT$/D', $retry)
                ? \DateTimeImmutable::createFromFormat('D, d M Y H:i:s \G\M\T', $retry, new \DateTimeZone('GMT')) : false;
            $retry = $date !== false && $date->format('D, d M Y H:i:s \G\M\T') === $retry ? $retry : null;
        }
        return new self($retry, self::number($response->header('X-RateLimit-Limit')),
            self::number($response->header('X-RateLimit-Remaining')), self::number($response->header('X-RateLimit-Reset')));
    }

    private static function number(#[\SensitiveParameter] ?string $value): ?int
    {
        if ($value === null || !preg_match('/^\d{1,10}$/D', $value)) {
            return null;
        }
        $decimal = ltrim($value, '0');
        $maximum = (string) PHP_INT_MAX;
        if (strlen($decimal) > strlen($maximum)
            || (strlen($decimal) === strlen($maximum) && strcmp($decimal, $maximum) > 0)) {
            return null;
        }
        return (int) $value;
    }
}

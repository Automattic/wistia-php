<?php
declare(strict_types=1);

namespace Automattic\Wistia\Encoding;

use GuzzleHttp\Psr7\MultipartStream;
use Psr\Http\Message\StreamInterface;

final readonly class Body
{
    private function __construct(public string $contentType, #[\SensitiveParameter] private string|StreamInterface $contents) {}

    /** Exact keys and explicit null are preserved; an omitted key is absent. */
    public static function json(#[\SensitiveParameter] mixed $value): self
    {
        try {
            return new self('application/json', json_encode($value, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
        } catch (\Throwable) {
            throw new \InvalidArgumentException('The request body is not JSON encodable.');
        }
    }

    public static function form(#[\SensitiveParameter] Query $fields): self
    {
        return new self('application/x-www-form-urlencoded', $fields->encode());
    }

    /** @internal Parts must use safe names/filenames and non-owning streams from the upload boundary. */
    public static function multipart(#[\SensitiveParameter] array $parts): self
    {
        $stream = new MultipartStream($parts);
        return new self('multipart/form-data; boundary=' . $stream->getBoundary(), $stream);
    }

    public function contents(): string|StreamInterface { return $this->contents; }
    public function __serialize(): array { throw new \LogicException('Request bodies cannot be serialized.'); }
    public function __debugInfo(): array { return ['contentType' => $this->contentType]; }
}

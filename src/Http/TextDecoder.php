<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Result\TextResult;

final readonly class TextDecoder implements ResponseDecoder
{
    public function __construct(private string $contentType) {}

    public function decode(#[\SensitiveParameter] TransportResponse $response): TextResult
    {
        $type = strtolower(trim(explode(';', $response->header('Content-Type') ?? '')[0]));
        if ($type !== $this->contentType) { throw DecodeException::create($response->status); }
        return new TextResult($response->body);
    }
}

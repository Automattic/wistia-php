<?php
declare(strict_types=1);

namespace Automattic\Wistia\Upload;

use Automattic\Wistia\Http\{JsonDecoder, ResponseDecoder, TransportResponse};
use Automattic\Wistia\Result\Result;

/** @internal Client supplies the status check and sanitized decode-error boundary. */
final readonly class Decoder implements ResponseDecoder
{
    /** @param class-string<Result> $resultClass */
    public function __construct(private string $resultClass) {}
    public function decode(#[\SensitiveParameter] TransportResponse $response): Result
    {
        return new ($this->resultClass)(Media::fromWire((new JsonDecoder())->decode($response)->value), $response->status);
    }
}

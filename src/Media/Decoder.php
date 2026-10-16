<?php
declare(strict_types=1);

namespace Automattic\Wistia\Media;

use Automattic\Wistia\Http\{JsonDecoder, ResponseDecoder, TransportResponse};
use Automattic\Wistia\Result\Result;

/** @internal Media endpoint adapter; Client owns the sanitized failure boundary. */
final readonly class Decoder implements ResponseDecoder
{
    /** @param class-string<Result> $resultClass */
    public function __construct(private string $resultClass, private bool $json = true) {}
    public function decode(#[\SensitiveParameter] TransportResponse $response): Result
    {
        // Delete Tag documents status 200 without a response schema. Do not invent a JSON/empty-body contract.
        $value = $this->json ? (new JsonDecoder())->decode($response)->value : null;
        return ($this->resultClass)::fromWire($value, $response->status);
    }
}

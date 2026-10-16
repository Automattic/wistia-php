<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{JsonDecoder, TransportResponse};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class JsonDecoderTest extends TestCase
{
    public function testJsonSyntaxPreservesListObjectNullFalseZeroAndBigIntegers(): void
    {
        $decoder = new JsonDecoder();
        foreach (['[]' => [], 'null' => null, 'false' => false, '0' => 0, '9223372036854775808' => '9223372036854775808'] as $body => $expected) {
            $result = $decoder->decode(new TransportResponse(200, ['Content-Type' => ['application/json; charset=utf-8']], (string) $body));
            self::assertSame($expected, $result->value);
        }
        self::assertInstanceOf(\stdClass::class, $decoder->decode(new TransportResponse(200, ['content-type' => ['application/json']], '{}'))->value);
    }

    #[DataProvider('malformedResponses')]
    public function testRejectsMalformedJsonOrWrongContentType(string $body, array $headers): void
    {
        $this->expectException(DecodeException::class);
        (new JsonDecoder())->decode(new TransportResponse(200, $headers, $body));
    }

    public static function malformedResponses(): array
    {
        return [['{', ['Content-Type' => ['application/json']]], ['', ['Content-Type' => ['application/json']]],
            ['{}', ['Content-Type' => ['text/html']]], ['{}', []]];
    }
}

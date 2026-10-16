<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{TextDecoder, TransportResponse};
use PHPUnit\Framework\TestCase;

final class TextDecoderTest extends TestCase
{
    public function testPreservesTextRatherThanJsonDecodingIt(): void
    {
        $result = (new TextDecoder('text/plain'))->decode(new TransportResponse(200, ['Content-Type' => ['text/plain; charset=utf-8']], 'false'));
        self::assertSame('false', $result->value);
    }

    public function testRejectsUndeclaredContentType(): void
    {
        $this->expectException(DecodeException::class);
        (new TextDecoder('text/vtt'))->decode(new TransportResponse(200, ['Content-Type' => ['application/json']], '{}'));
    }
}

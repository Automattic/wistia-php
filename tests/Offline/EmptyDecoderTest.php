<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{EmptyDecoder, TransportResponse};
use PHPUnit\Framework\TestCase;

final class EmptyDecoderTest extends TestCase
{
    public function testDoesNotSilentlyDiscardUnexpectedBody(): void
    {
        $this->expectException(DecodeException::class);
        (new EmptyDecoder())->decode(new TransportResponse(204, [], 'fixture-secret'));
    }
}

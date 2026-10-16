<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testPathComponentsAreEncodedNotInterpolatedOrDoubleEncoded(): void
    {
        self::assertSame('a%2Fb%20%25%3F', Path::segment('a/b %?'));
    }

    #[DataProvider('unsafePaths')]
    public function testRejectsHostOverrideTraversalAndInlineQuery(string $path): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Request(ApiFamily::Data, HttpMethod::Get, $path, RequestSafety::ReadOnly, [200]);
    }

    public static function unsafePaths(): array
    {
        return [['https://example.invalid'], ['//example.invalid'], ['/../token'], ['/%2e%2e/token'],
            ['/a?token=secret'], ['/a#fragment'], ["/a\\b"], ["/a\r\nheader"]];
    }

    public function testSideEffectingGetIsNotInferredToBeReadOnly(): void
    {
        $request = new Request(ApiFamily::Data, HttpMethod::Get, '/share', RequestSafety::MayChangeRemoteState, [200]);
        self::assertTrue($request->mayChangeRemoteState());
        $write = new Request(ApiFamily::Data, HttpMethod::Post, '/medias', RequestSafety::ReadOnly, [201]);
        self::assertTrue($write->mayChangeRemoteState());
    }

    public function testCannotDeclareRedirectAsSuccess(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Request(ApiFamily::Data, HttpMethod::Get, '/medias', RequestSafety::ReadOnly, [302]);
    }
}

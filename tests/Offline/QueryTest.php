<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Encoding\Query;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    public function testExactWireNamesRepeatsDeepObjectsFalseZeroNullAndOmission(): void
    {
        $query = new Query([
            ['folder_id', 0], ['tags[]', 'a b'], ['tags[]', 'c+d'],
            ['cursor[enabled]', false], ['cursor[after]', 'x/y'], ['nil', null], ['embedCode', true],
        ]);
        self::assertSame('folder_id=0&tags%5B%5D=a%20b&tags%5B%5D=c%2Bd&cursor%5Benabled%5D=false&cursor%5Bafter%5D=x%2Fy&nil=&embedCode=true', $query->encode());
        self::assertSame('', (new Query())->encode());
        self::assertStringNotContainsString('omitted', $query->encode());
    }

    public function testSignedUrlIsEncodedExactlyOnceAsAValue(): void
    {
        $url = 'https://example.invalid/file%20name?signature=a+b%2Fc&expires=0';
        $encoded = (new Query([['url', $url]]))->encode();
        self::assertSame('url=' . rawurlencode($url), $encoded);
        self::assertSame($url, rawurldecode(substr($encoded, 4)));
    }

    public function testRejectsUnrepresentableNestedQueryInsteadOfGuessingAStyle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Query([['cursor', ['after' => 'abc']]]);
    }

    public function testMalformedPairsDoNotEmitWarnings(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Query([['name' => 'bad', 'value' => 'pair']]);
    }
}

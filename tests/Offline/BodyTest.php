<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Encoding\{Body, Query};
use PHPUnit\Framework\TestCase;

final class BodyTest extends TestCase
{
    public function testJsonPreservesExactKeysNestedListsObjectsNullFalseAndZero(): void
    {
        $body = Body::json(['embedCode' => false, 'zero' => 0, 'nil' => null,
            'nested' => ['fileSize' => 0.0], 'list' => [], 'object' => new \stdClass()]);
        self::assertSame('application/json', $body->contentType);
        self::assertSame('{"embedCode":false,"zero":0,"nil":null,"nested":{"fileSize":0.0},"list":[],"object":{}}', $body->contents());
        self::assertStringNotContainsString('omitted', $body->contents());
    }

    public function testFormPreservesUploadNamesAndUrlBytes(): void
    {
        $body = Body::form(new Query([['project_id', 0], ['url', 'https://example.invalid/a?x=1&sig=a+b']]));
        self::assertSame('application/x-www-form-urlencoded', $body->contentType);
        self::assertSame('project_id=0&url=https%3A%2F%2Fexample.invalid%2Fa%3Fx%3D1%26sig%3Da%2Bb', $body->contents());
    }

    public function testInvalidJsonInputHasNoRawCauseOrBody(): void
    {
        try { Body::json(['fixture-secret' => NAN]); self::fail('Expected local validation.'); }
        catch (\InvalidArgumentException $e) {
            self::assertNull($e->getPrevious());
            self::assertStringNotContainsString('fixture-secret', (string) $e);
        }
    }
}

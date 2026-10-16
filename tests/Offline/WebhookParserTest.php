<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\WebhookParser;
use Automattic\Wistia\WebhookVerifier;
use Automattic\Wistia\Webhook\Event;
use Automattic\Wistia\Webhook\ParsingException;
use Automattic\Wistia\Webhook\VerificationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WebhookParserTest extends TestCase
{
    private const string SECRET = 'synthetic-webhook-secret';
    private const string BODY = '{"hook":{"uuid":"message-1","future":true},"events":[{"uuid":"09e67f16e673a000","type":"media.updated","generated_at":"2024-03-30T15:53:15Z","payload":{"media":{"id":"synthetic-id"}},"metadata":{"account_id":"synthetic-account"}},{"uuid":"event-2","type":"future.unknown","generated_at":"2024-03-30T15:53:15.123+00:00","payload":[{},[],null,false,42,"unchanged"]}],"extra":{"preserved":true}}';

    private function parser(): WebhookParser { return new WebhookParser(new WebhookVerifier(self::SECRET)); }

    public function testTypedBatchPreservesUnknownTypesShapesAndAllMetadata(): void
    {
        $envelope = $this->parser()->parse(self::BODY, hash_hmac('sha256', self::BODY, self::SECRET));
        self::assertSame('message-1', $envelope->hookUuid);
        self::assertCount(2, $envelope->events);
        self::assertContainsOnlyInstancesOf(Event::class, $envelope->events);
        self::assertSame('09e67f16e673a000', $envelope->events[0]->uuid);
        self::assertSame('media.updated', $envelope->events[0]->type);
        self::assertSame('synthetic-id', $envelope->events[0]->payload()->media->id);
        self::assertSame('synthetic-account', $envelope->events[0]->data()->metadata->account_id);
        self::assertSame('future.unknown', $envelope->events[1]->type);
        self::assertSame('2024-03-30T15:53:15.123+00:00', $envelope->events[1]->generatedAt);
        self::assertInstanceOf(\stdClass::class, $envelope->events[1]->payload()[0]);
        self::assertSame([], $envelope->events[1]->payload()[1]);
        self::assertSame([null, false, 42, 'unchanged'], array_slice($envelope->events[1]->payload(), 2));
        self::assertTrue($envelope->data()->hook->future);
        self::assertTrue($envelope->data()->extra->preserved);
        self::assertSame(self::BODY, $envelope->rawBody());
        self::assertEquals(json_decode(self::BODY), $envelope->data());
    }

    #[DataProvider('payloads')]
    public function testUnknownPayloadsAreNotCoerced(string $payload): void
    {
        $body = '{"hook":{"uuid":"h"},"events":[{"uuid":"e","type":"new.type","generated_at":"2024-01-01T00:00:00Z","payload":' . $payload . '}]}';
        $envelope = $this->parser()->parse($body, hash_hmac('sha256', $body, self::SECRET));
        self::assertEquals(json_decode($payload), $envelope->events[0]->payload());
        self::assertSame(get_debug_type(json_decode($payload)), get_debug_type($envelope->events[0]->payload()));
    }

    public static function payloads(): array { return [['{}'], ['[]'], ['null'], ['false'], ['true'], ['42'], ['1.25'], ['"text"']]; }

    public function testDuplicatesAndReceivedOrderRemainConsumerOwned(): void
    {
        $event = ['uuid' => 'duplicate', 'type' => 'future.type', 'generated_at' => '2024-03-30T15:53:15Z', 'payload' => null];
        $older = $event; $older['uuid'] = 'older'; $older['generated_at'] = '2020-01-01T00:00:00Z';
        $body = json_encode(['hook' => ['uuid' => 'h'], 'events' => [$event, $older, $event]], JSON_THROW_ON_ERROR);
        $result = $this->parser()->parse($body, hash_hmac('sha256', $body, self::SECRET));
        self::assertSame(['duplicate', 'older', 'duplicate'], array_map(static fn (Event $e): string => $e->uuid, $result->events));
    }

    public function testChangedWhitespaceIsAcceptedOnlyWithItsOwnSignature(): void
    {
        $changed = self::BODY . "\n";
        self::assertSame($changed, $this->parser()->parse($changed, hash_hmac('sha256', $changed, self::SECRET))->rawBody());
        $this->expectException(VerificationException::class);
        $this->parser()->parse($changed, hash_hmac('sha256', self::BODY, self::SECRET));
    }

    public function testVerifiedInvalidUtf8AndOverDepthJsonFailSanitized(): void
    {
        foreach (["{\"private\":\"\xff\"}", str_repeat('[', 513) . str_repeat(']', 513)] as $body) {
            try {
                $this->parser()->parse($body, hash_hmac('sha256', $body, self::SECRET));
                self::fail('Invalid JSON accepted.');
            } catch (ParsingException $error) {
                self::assertNull($error->getPrevious());
                ExceptionAssertions::assertSanitized($error, self::SECRET);
            }
        }
    }

    public function testEmptyBatchIsAnArrayAndNoMinimumCountIsInvented(): void
    {
        $body = '{"hook":{"uuid":"h"},"events":[]}';
        self::assertSame([], $this->parser()->parse($body, hash_hmac('sha256', $body, self::SECRET))->events);
    }

    #[DataProvider('unverifiedBodies')]
    public function testVerificationAlwaysWinsOverParsing(string $body, ?string $signature): void
    {
        $this->expectException(VerificationException::class);
        $this->parser()->parse($body, $signature);
    }

    public static function unverifiedBodies(): array
    {
        return [['{"hook":{"uuid":"h"},"events":[]}', null], ['not json', null], ['not json', 'bad'],
            ['not json', str_repeat('0', 64)], ['{"hook":{"uuid":"h"},"events":[]}', str_repeat('0', 64)]];
    }

    #[DataProvider('invalidBodies')]
    public function testVerifiedMalformedOrWronglyTypedEnvelopeFails(string $body): void
    {
        try {
            $this->parser()->parse($body, hash_hmac('sha256', $body, self::SECRET));
            self::fail('Invalid envelope accepted.');
        } catch (ParsingException $error) {
            self::assertNull($error->getPrevious());
            self::assertNull($error->status);
            self::assertFalse($error->remoteOutcomeUncertain);
            ExceptionAssertions::assertSanitized($error, self::SECRET);
        }
    }

    public static function invalidBodies(): iterable
    {
        foreach (['not json', '{', 'null', '[]', '{}', '{"hook":{"uuid":"h"}}',
            '{"hook":{"uuid":"h"},"events":{}}', '{"hook":{"uuid":"h"},"events":null}',
            '{"hook":[],"events":[]}', '{"hook":{},"events":[]}',
            '{"hook":{"uuid":1},"events":[]}', '{"hook":{"uuid":""},"events":[]}',
            '{"hook":{"uuid":"h"},"events":[null]}'] as $i => $body) {
            yield 'root-' . $i => [$body];
        }
        $event = ['uuid' => 'e', 'type' => 'media.updated', 'generated_at' => '2024-03-30T15:53:15Z', 'payload' => new \stdClass()];
        foreach (array_keys($event) as $field) {
            $missing = $event; unset($missing[$field]);
            yield 'missing-' . $field => [json_encode(['hook' => ['uuid' => 'h'], 'events' => [$missing]], JSON_THROW_ON_ERROR)];
            if ($field === 'payload') { continue; }
            foreach ([null, 1, false, [], new \stdClass(), ''] as $i => $wrong) {
                $changed = $event; $changed[$field] = $wrong;
                yield $field . '-' . $i => [json_encode(['hook' => ['uuid' => 'h'], 'events' => [$changed]], JSON_THROW_ON_ERROR)];
            }
        }
    }

    public function testOversizedIntegersAndExactBytesRemainAvailableWithoutRounding(): void
    {
        $body = '{"hook":{"uuid":"h"},"events":[{"uuid":"e","type":"future","generated_at":"2024-01-01T00:00:00Z","payload":{"n":123456789012345678901234567890}}]}';
        $result = $this->parser()->parse($body, hash_hmac('sha256', $body, self::SECRET));
        self::assertSame('123456789012345678901234567890', $result->events[0]->payload()->n);
        self::assertSame($body, $result->rawBody());
    }

    public function testSuccessfulObjectsSuppressSensitiveDebugAndRequireDeliberateDataAccess(): void
    {
        $result = $this->parser()->parse(self::BODY, hash_hmac('sha256', self::BODY, self::SECRET));
        foreach ([$this->parser(), $result, ...$result->events] as $object) {
            ob_start(); var_dump($object); $debug = ob_get_clean();
            foreach (['synthetic-account', 'synthetic-id', 'message-1', self::SECRET] as $private) {
                self::assertStringNotContainsString($private, print_r($object, true));
                self::assertStringNotContainsString($private, $debug);
            }
            try { serialize($object); self::fail('Serialization allowed.'); }
            catch (\LogicException $error) { self::assertNull($error->getPrevious()); }
        }
    }
}

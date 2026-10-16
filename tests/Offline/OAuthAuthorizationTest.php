<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\{Client, Configuration, OAuthService};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Request\OAuth\{AuthorizationRequest, CodeExchangeRequest, RefreshTokenRequest};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OAuthAuthorizationTest extends TestCase
{
    public function testExactCompleteRedirectUriIsEncodedOnceWithoutTransportOrUnsupportedParameters(): void
    {
        $transport = new RecordingTransport(new TransportResponse(200, [], ''));
        $service = new OAuthService(new Client(new Configuration(), $transport));
        $redirect = 'https://consumer.invalid/callback?correlation=fixture-secret%2Bvalue&return=/a+b&empty=';
        $url = $service->authorizationUrl(new AuthorizationRequest('public id+&', $redirect));
        self::assertSame('https://app.wistia.com/oauth/authorize?client_id=public%20id%2B%26&redirect_uri=https%3A%2F%2Fconsumer.invalid%2Fcallback%3Fcorrelation%3Dfixture-secret%252Bvalue%26return%3D%2Fa%2Bb%26empty%3D&response_type=code', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        self::assertSame($redirect, $query['redirect_uri']);
        self::assertSame(['client_id', 'redirect_uri', 'response_type'], array_keys($query));
        self::assertCount(0, $transport->requests);
    }

    #[DataProvider('emptyFields')]
    public function testEmptyInputsAreLocallyRejected(string $type, array $fields): void
    {
        try { new $type(...$fields); self::fail('Expected local validation.'); }
        catch (\InvalidArgumentException $e) {
            self::assertSame('OAuth request fields must be nonempty strings.', $e->getMessage());
            self::assertNull($e->getPrevious());
            foreach ($e->getTrace() as $frame) {
                if (($frame['class'] ?? '') !== $type) { continue; }
                foreach ($frame['args'] as $arg) { self::assertInstanceOf(\SensitiveParameterValue::class, $arg); }
            }
        }
    }

    public static function emptyFields(): array
    {
        $cases = [];
        foreach ([AuthorizationRequest::class => 2, CodeExchangeRequest::class => 4, RefreshTokenRequest::class => 3] as $type => $count) {
            for ($i = 0; $i < $count; ++$i) {
                $fields = array_fill(0, $count, 'fixture-secret'); $fields[$i] = '';
                $cases[] = [$type, $fields];
            }
        }
        return $cases;
    }

    public function testCredentialRequestsAndServiceHaveRedactedDebugAndRejectSerialization(): void
    {
        $objects = [new AuthorizationRequest('fixture-secret', 'fixture-secret'),
            new CodeExchangeRequest('fixture-secret', 'fixture-secret', 'fixture-secret', 'fixture-secret'),
            new RefreshTokenRequest('fixture-secret', 'fixture-secret', 'fixture-secret'),
            new OAuthService(new Client(new Configuration('fixture-secret')))];
        foreach ($objects as $object) {
            self::assertStringNotContainsString('fixture-secret', print_r($object, true));
            self::assertStringNotContainsString('fixture-secret', json_encode($object, JSON_THROW_ON_ERROR));
            ob_start(); var_dump($object); $debug = ob_get_clean();
            self::assertStringNotContainsString('fixture-secret', $debug);
            try { serialize($object); self::fail('Expected serialization refusal.'); }
            catch (\LogicException $e) { self::assertStringNotContainsString('fixture-secret', $e->getMessage()); }
        }
    }
}

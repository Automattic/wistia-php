<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\{Client, Configuration, OAuthService};
use Automattic\Wistia\Exception\{ApiException, AuthenticationException, DecodeException, PermissionException, RateLimitException, TransportException, WistiaException};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Request\OAuth\{CodeExchangeRequest, RefreshTokenRequest};
use Automattic\Wistia\Result\OAuth\TokenResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OAuthServiceTest extends TestCase
{
    private static function token(): array
    {
        return ['access_token' => 'fixture-secret-access', 'token_type' => 'bearer', 'expires_in' => 21600,
            'refresh_token' => 'fixture-secret-refresh', 'scope' => ['all:read', 'media:upload']];
    }

    private function transport(int $status = 200, ?string $body = null, array $headers = []): RecordingTransport
    {
        return new RecordingTransport(new TransportResponse($status,
            $headers + ['Content-Type' => ['application/json']], $body ?? json_encode(self::token(), JSON_THROW_ON_ERROR)));
    }

    private function invoke(OAuthService $service, bool $refresh): TokenResult
    {
        return $refresh
            ? $service->refresh(new RefreshTokenRequest('id+&', 'fixture-secret+&', 'fixture-secret-refresh+&'))
            : $service->exchangeCode(new CodeExchangeRequest('id+&', 'fixture-secret+&', 'fixture-secret-code+&',
                'https://consumer.invalid/callback?correlation=fixture-secret%2B&return=/a+b'));
    }

    #[DataProvider('flows')]
    public function testCapturedTokenContractAndTypedResult(bool $refresh): void
    {
        $transport = $this->transport(headers: ['Retry-After' => ['7'], 'X-RateLimit-Remaining' => ['0']]);
        // A configured API token must not leak into the independent OAuth credential flow.
        $result = $this->invoke(new OAuthService(new Client(new Configuration('unrelated-api-token'), $transport)), $refresh);
        self::assertCount(1, $transport->requests);
        $request = $transport->requests[0];
        $expected = 'https://api.wistia.com/oauth/token?client_id=id%2B%26&client_secret=fixture-secret%2B%26&grant_type=';
        $expected .= $refresh ? 'refresh_token&refresh_token=fixture-secret-refresh%2B%26'
            : 'authorization_code&code=fixture-secret-code%2B%26&redirect_uri=https%3A%2F%2Fconsumer.invalid%2Fcallback%3Fcorrelation%3Dfixture-secret%252B%26return%3D%2Fa%2Bb';
        self::assertSame($expected, $request->url);
        self::assertSame('POST', $request->method);
        self::assertNull($request->body);
        self::assertSame([], $request->headers);
        self::assertSame(30.0, $request->timeout);
        self::assertSame(10.0, $request->connectTimeout);
        self::assertInstanceOf(TokenResult::class, $result);
        self::assertSame('fixture-secret-access', $result->accessToken);
        self::assertSame('bearer', $result->tokenType);
        self::assertSame(21600, $result->expiresIn);
        self::assertSame('fixture-secret-refresh', $result->refreshToken);
        self::assertSame(['all:read', 'media:upload'], $result->scope);
        self::assertSame(200, $result->status);
        self::assertSame(0, $result->rateLimit->remaining);
        self::assertSame('7', $result->rateLimit->retryAfter);
        self::assertStringNotContainsString('fixture-secret', print_r($result, true));
        self::assertSame('{"status":200}', json_encode($result, JSON_THROW_ON_ERROR));
        ob_start(); var_dump($result); $debug = ob_get_clean();
        self::assertStringNotContainsString('fixture-secret', $debug);
        try { serialize($result); self::fail('Expected credential serialization refusal.'); }
        catch (\LogicException $e) { self::assertStringNotContainsString('fixture-secret', $e->getMessage()); }
    }

    public static function flows(): array { return [[false], [true]]; }

    public function testZeroExpiryAndExactScopesAndTypeAreNotNormalized(): void
    {
        $value = self::token(); $value['expires_in'] = 0; $value['token_type'] = 'Bearer';
        $value['scope'] = ['future:scope', 'all:read', 'all:read'];
        $transport = $this->transport(body: json_encode($value, JSON_THROW_ON_ERROR));
        $result = $this->invoke(new OAuthService(new Client(new Configuration(), $transport)), true);
        self::assertSame(0, $result->expiresIn);
        self::assertSame('Bearer', $result->tokenType);
        self::assertSame($value['scope'], $result->scope);
        $value['scope'] = []; $value['expires_in'] = PHP_INT_MAX;
        $value['untrusted_extra'] = 'fixture-secret-must-not-retain';
        $transport->response = new TransportResponse(200, ['Content-Type' => ['application/json']], json_encode($value));
        $boundary = $this->invoke(new OAuthService(new Client(new Configuration(), $transport)), false);
        self::assertSame([], $boundary->scope);
        self::assertSame(PHP_INT_MAX, $boundary->expiresIn);
        self::assertArrayNotHasKey('untrusted_extra', get_object_vars($boundary));
    }

    #[DataProvider('remoteFailures')]
    public function testRejectedExpiredDeniedAndStatusFailuresAreSanitizedAndNotRetried(int $status, #[\SensitiveParameter] string $body, string $type, bool $uncertain): void
    {
        foreach ([false, true] as $refresh) {
            $transport = $this->transport($status, $body, ['Retry-After' => ['9'], 'X-RateLimit-Remaining' => ['0'], 'X-Error' => ['fixture-secret']]);
            try { $this->invoke(new OAuthService(new Client(new Configuration(), $transport)), $refresh); self::fail('Expected failure.'); }
            catch (WistiaException $e) {
                self::assertInstanceOf($type, $e);
                self::assertSame($status, $e->status);
                self::assertSame($uncertain, $e->remoteOutcomeUncertain);
                self::assertSame('9', $e->rateLimit->retryAfter);
                self::assertSame(0, $e->rateLimit->remaining);
                ExceptionAssertions::assertSanitized($e);
                self::assertCount(1, $transport->requests);
            }
        }
    }

    public static function remoteFailures(): array
    {
        return [
            'expired or rejected grant' => [400, '{"error":"invalid_grant","error_description":"expired fixture-secret"}', ApiException::class, false],
            'invalid client' => [401, '<html>fixture-secret</html>', AuthenticationException::class, false],
            'access denied' => [403, '{"error":"access_denied","error_description":"fixture-secret"}', PermissionException::class, false],
            'rate limit' => [429, '{"error":"fixture-secret"}', RateLimitException::class, false],
            'credential redirect' => [302, 'https://elsewhere.invalid/?secret=fixture-secret', ApiException::class, true],
            'ambiguous failure' => [503, 'fixture-secret', ApiException::class, true],
        ];
    }

    #[DataProvider('malformedTokens')]
    public function testMalformedSuccessCannotBecomeAnEmptyOrPartialToken(#[\SensitiveParameter] string $body, string $contentType = 'application/json', int $status = 200): void
    {
        foreach ([false, true] as $refresh) {
            $transport = $this->transport($status, $body, ['Content-Type' => [$contentType]]);
            try { $this->invoke(new OAuthService(new Client(new Configuration(), $transport)), $refresh); self::fail('Expected decoding failure.'); }
            catch (DecodeException $e) {
                self::assertSame($status, $e->status);
                self::assertTrue($e->remoteOutcomeUncertain);
                ExceptionAssertions::assertSanitized($e);
                self::assertCount(1, $transport->requests);
            }
        }
    }

    public static function malformedTokens(): array
    {
        $cases = [['{fixture-secret'], ['null'], ['[]'], ['{}'], ['{"error":"fixture-secret"}'],
            [json_encode(self::token()), 'text/html'], [json_encode(self::token()), 'application/json', 201]];
        foreach (array_keys(self::token()) as $key) {
            $value = self::token(); unset($value[$key]); $cases[] = [json_encode($value)];
            $value = self::token(); $value[$key] = null; $cases[] = [json_encode($value)];
        }
        foreach (['access_token' => ['', 17], 'token_type' => ['', false], 'refresh_token' => ['', []],
            'expires_in' => [-1, '21600', 21600.5, true, '999999999999999999999999'],
            'scope' => ['all:read', (object) ['0' => 'all:read'], [1], [null]]] as $key => $invalid) {
            foreach ($invalid as $bad) { $value = self::token(); $value[$key] = $bad; $cases[] = [json_encode($value)]; }
        }
        $cases[] = [str_replace('21600', '999999999999999999999999999', json_encode(self::token()))];
        return $cases;
    }

    public function testCompleteFailureTracesWithoutFrameworkCallerGraph(): void
    {
        $process = proc_open([PHP_BINARY, '-d', 'zend.exception_ignore_args=0', __DIR__ . '/fixtures/oauth-redaction.php'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        self::assertIsResource($process);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        self::assertSame(0, proc_close($process), $stderr);
        self::assertSame('', $stderr);
        self::assertSame("Sanitized isolated OAuth traces: 12 cases; arguments enabled.\n", $stdout);
    }

    #[DataProvider('flows')]
    public function testTransportFailureIsUncertainWithoutCauseOrReplay(bool $refresh): void
    {
        $transport = $this->transport();
        $transport->failure = new \RuntimeException('fixture-secret URL and body', 0, new \RuntimeException('nested fixture-secret'));
        try { $this->invoke(new OAuthService(new Client(new Configuration(), $transport)), $refresh); self::fail('Expected transport failure.'); }
        catch (TransportException $e) {
            self::assertTrue($e->remoteOutcomeUncertain);
            self::assertNull($e->status);
            ExceptionAssertions::assertSanitized($e);
            self::assertCount(1, $transport->requests);
        }
    }
}

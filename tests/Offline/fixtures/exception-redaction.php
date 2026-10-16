<?php
declare(strict_types=1);

use Automattic\Wistia\{Client, Configuration};
use Automattic\Wistia\Encoding\{Body, Query};
use Automattic\Wistia\Exception\WistiaException;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, JsonDecoder, Request, RequestSafety, ResponseDecoder, Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Result\Result;

require dirname(__DIR__, 3) . '/vendor/autoload.php';

if ((bool) ini_get('zend.exception_ignore_args')) {
    throw new RuntimeException('Exception arguments must be enabled.');
}

// No PHPUnit/provider/application argument graph: only synthetic library inputs.
$secret = 'adversarial-secret-' . bin2hex(random_bytes(16));
$cases = 0;
foreach ([400, 401, 403, 429, 302, 503, 'transport', 'json', 'decoder'] as $case) {
    $transport = new class($case, $secret) implements Transport {
        public int $attempts = 0;
        public function __construct(private int|string $case, private string $secret) {}
        public function send(#[SensitiveParameter] TransportRequest $request): TransportResponse
        {
            ++$this->attempts;
            if ($this->case === 'transport') {
                throw new RuntimeException($this->secret, 0, new RuntimeException('nested ' . $this->secret));
            }
            return new TransportResponse(is_int($this->case) ? $this->case : 200,
                ['Content-Type' => ['application/json'], 'Retry-After' => [$this->secret], 'X-Error' => [$this->secret]],
                $this->case === 'json' ? '{' . $this->secret : json_encode(['error' => $this->secret], JSON_THROW_ON_ERROR));
        }
    };
    $decoder = $case === 'decoder' ? new class($secret) implements ResponseDecoder {
        public function __construct(private string $secret) {}
        public function decode(#[SensitiveParameter] TransportResponse $response): Result
        {
            throw new RuntimeException($this->secret, 0, new RuntimeException('nested ' . $this->secret));
        }
    } : new JsonDecoder();
    $client = new Client(new Configuration($secret), $transport);
    $request = new Request(ApiFamily::Upload, HttpMethod::Post, '/fixture', RequestSafety::MayChangeRemoteState, [200],
        new Query([['client_secret', $secret], ['source', 'https://example.invalid/?signature=' . $secret]]),
        Body::json(['refresh_token' => $secret]));
    try {
        $client->execute($request, $decoder);
        throw new RuntimeException('Expected a sanitized exception.');
    } catch (WistiaException $error) {
        if (count($error->getTrace()) > 8) {
            throw new RuntimeException('Unexpected fixture trace depth.');
        }
        $rendered = (string) $error . print_r($error->getTrace(), true) . json_encode([
            $error->getMessage(), $error->getCode(), get_object_vars($error),
        ], JSON_THROW_ON_ERROR);
        if (str_contains($rendered, $secret) || $error->getPrevious() !== null) {
            throw new RuntimeException('Sensitive library error context escaped.');
        }
        if ($transport->attempts !== 1) {
            throw new RuntimeException('Unexpected replay.');
        }
        ++$cases;
    }
}
echo 'Sanitized isolated exception traces: ' . $cases . " cases; arguments enabled.\n";

<?php
declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';
ini_set('zend.exception_ignore_args', '0');

use Automattic\Wistia\{Client, Configuration, OAuthService};
use Automattic\Wistia\Exception\WistiaException;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Request\OAuth\{CodeExchangeRequest, RefreshTokenRequest};

function checkOAuthFailure(bool $refresh, int $status, bool $disconnect): void
{
    $transport = new class($status, $disconnect) implements Transport {
        public int $attempts = 0;
        public function __construct(private int $status, private bool $disconnect) {}
        public function send(#[\SensitiveParameter] TransportRequest $request): TransportResponse
        {
            ++$this->attempts;
            if ($this->disconnect) { throw new RuntimeException('fixture-secret URL/body', 0, new RuntimeException('fixture-secret cause')); }
            return new TransportResponse($this->status, ['Content-Type' => ['application/json']], '{"error":"fixture-secret"}');
        }
    };
    $service = new OAuthService(new Client(new Configuration(), $transport));
    try {
        if ($refresh) {
            $service->refresh(new RefreshTokenRequest('fixture-secret-id', 'fixture-secret-client', 'fixture-secret-refresh'));
        } else {
            $service->exchangeCode(new CodeExchangeRequest('fixture-secret-id', 'fixture-secret-client',
                'fixture-secret-code', 'https://consumer.invalid/?correlation=fixture-secret'));
        }
        throw new RuntimeException('Expected OAuth failure.');
    } catch (WistiaException $e) {
        $views = [(string) $e, print_r($e->getTrace(), true),
            json_encode(get_object_vars($e), JSON_THROW_ON_ERROR), $e->getMessage()];
        foreach ($views as $view) {
            if (str_contains($view, 'fixture-secret')) { throw new RuntimeException('OAuth failure leaked sensitive input.'); }
        }
        if ($e->getPrevious() !== null || $transport->attempts !== 1) {
            throw new RuntimeException('OAuth failure retained a cause or replayed.');
        }
    }
}

foreach ([false, true] as $refresh) {
    foreach ([[400, false], [401, false], [403, false], [200, false], [503, false], [200, true]] as [$status, $disconnect]) {
        checkOAuthFailure($refresh, $status, $disconnect);
    }
}
echo "Sanitized isolated OAuth traces: 12 cases; arguments enabled.\n";

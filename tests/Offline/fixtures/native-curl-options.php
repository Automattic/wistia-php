<?php
declare(strict_types=1);

namespace GuzzleHttp\Handler {
    // Isolated instrumentation of the real factory's native option application; delegates every setting to ext-curl.
    final class NativeOptions { public static array $values = []; }
    function curl_setopt(#[\SensitiveParameter] $handle, int $option, #[\SensitiveParameter] mixed $value): bool
    {
        NativeOptions::$values[$option] = $value;
        return \curl_setopt($handle, $option, $value);
    }
}

namespace {
    require dirname(__DIR__, 3) . '/vendor/autoload.php';
    $results = [];
    foreach ([32, 1000001] as $size) {
        \GuzzleHttp\Handler\NativeOptions::$values = [];
        $observed = [];
        $handler = new \Automattic\Wistia\Http\SingleAttemptCurlHandler(null, static function (#[\SensitiveParameter] \GuzzleHttp\Handler\EasyHandle $easy) use (&$observed, $size): void {
            $conf = \GuzzleHttp\Handler\NativeOptions::$values;
            $read = $conf[CURLOPT_READFUNCTION] ?? null;
            $observed = [
                'streaming' => is_callable($read) && !array_key_exists(CURLOPT_POSTFIELDS, $conf),
                'fresh' => $conf[CURLOPT_FRESH_CONNECT] === true,
                'noReuse' => $conf[CURLOPT_FORBID_REUSE] === true,
                'http11' => $conf[CURLOPT_HTTP_VERSION] === CURL_HTTP_VERSION_1_1,
                'noRedirects' => empty($conf[CURLOPT_FOLLOWLOCATION]),
                'noAuthNegotiation' => $conf[CURLOPT_HTTPAUTH] === CURLAUTH_NONE,
                'noProxy' => $conf[CURLOPT_PROXY] === '',
                'noExpect' => in_array('Expect:', $conf[CURLOPT_HTTPHEADER], true),
                'totalTimeout' => $conf[CURLOPT_TIMEOUT_MS] === 7500,
                'connectTimeout' => $conf[CURLOPT_CONNECTTIMEOUT_MS] === 2500,
                'tlsPeer' => $conf[CURLOPT_SSL_VERIFYPEER] === true,
                'tlsHost' => $conf[CURLOPT_SSL_VERIFYHOST] === 2,
            ];
            $observed['readExactPrefix'] = is_callable($read) && $read($easy->handle, null, 8) === str_repeat('x', 8);
            if (defined('CURLOPT_SEEKFUNCTION')) {
                $seek = $conf[(int) constant('CURLOPT_SEEKFUNCTION')] ?? null;
                $observed['nativeSeekRefused'] = is_callable($seek) && $seek($easy->handle, 0, SEEK_SET) !== 0;
            } else {
                // PHP without the seek option supplies no native rewind callback; libcurl cannot rewind this stream.
                $observed['nativeSeekUnavailable'] = true;
            }
            $observed['positionNotRewound'] = $easy->request->getBody()->tell() === 8;
            $observed['remainingBytesExact'] = is_callable($read) && $read($easy->handle, null, $size) === str_repeat('x', $size - 8);
            $observed['knownLengthPreserved'] = $easy->request->getHeaderLine('Content-Length') === (string) $size;
            $easy->errno = 65;
        });
        try {
            $handler(new \GuzzleHttp\Psr7\Request('POST', 'https://offline.invalid/', [], str_repeat('x', $size)), [
                'timeout' => 7.5, 'connect_timeout' => 2.5, 'verify' => true, 'allow_redirects' => false,
            ])->wait();
            $observed['failedRewindRejected'] = false;
        } catch (\GuzzleHttp\Exception\RequestException) {
            $observed['failedRewindRejected'] = true;
        }
        $results[(string) $size] = $observed;
    }
    echo json_encode($results, JSON_THROW_ON_ERROR), "\n";
}

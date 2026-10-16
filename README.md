# Wistia PHP

[![Latest Version](https://img.shields.io/github/release/Automattic/wistia-php.svg?style=flat-square)](https://github.com/Automattic/wistia-php/releases)
[![Software License](https://img.shields.io/badge/license-GPLv2-brightgreen.svg?style=flat-square)](LICENSE)

A framework-independent PHP client for Wistia, undergoing a breaking rewrite.

## Current development scope

P02 provides the shared client/configuration, HTTP execution, encoding, decoding and failure foundation. P03 adds [bounded pagination and optional job polling](docs/pagination.md) and the typed [background-job-status Data operation](docs/reference/data.md#get_background-job-status-backgroundjobstatusid). Construct `DataBackgroundJobStatusService` directly around `Client`. P04 adds [OAuth helpers](docs/oauth.md) for authorization URLs, explicit code exchange and refresh; applications own callbacks, correlation, persistence and renewal policy. Other resource endpoints, Upload workflows and webhook helpers remain planned in this checkout. Do not treat the [endpoint inventory](docs/rewrite/README.md) as complete installed coverage; this development branch is not a complete replacement for the published 1.x package.

Requirements: PHP `^8.4` with `ext-curl`, Guzzle `^8.2`; development tests use PHPUnit `^12.5`. Package identity remains `automattic/wistia-php`, namespace `Automattic\Wistia`. Composer distribution is retained; no 2.0.0 release has been published by this change.

## Configuration

After installing the package in an appropriately provisioned application:

```php
use Automattic\Wistia\Client;
use Automattic\Wistia\Configuration;

$token = getenv('WISTIA_API_TOKEN'); // Application-owned secret management.
if ($token === false || $token === '') {
    throw new RuntimeException('Configure WISTIA_API_TOKEN in your application.');
}
$client = new Client(new Configuration(token: $token, timeout: 30.0, connectTimeout: 10.0));
```

Both timeout fields accept finite seconds in the inclusive range `0.001`–`2147483.647` (portable signed 32-bit milliseconds); invalid values are rejected at construction, before transport. Defaults remain 30 total and 10 connect seconds. Construction performs no HTTP request. Credentials are attached only as Bearer headers for Data, Stats, Analytics and Upload. Modern families use `https://api.wistia.com/modern` and pinned `X-Wistia-API-Version: 2026-09`; Upload and OAuth have separate hosts/contracts and no version header. Tokenless configuration supports the OAuth helpers, not unauthenticated modern API calls.

See [foundation contracts](docs/foundation.md) for transport injection, exact-name encoding, result/decoder boundaries, and safe error handling. Applications own OAuth callbacks, correlation and secure token persistence. Webhook helpers and routing remain future work in this checkout.

## Breaking migration status

The legacy array constructor, magic endpoint methods, `get_client()`, `get_token()`, public transport/format/last-response fields, raw decoded-object/error-code returns and XML mode are removed. There is no compatibility layer. Configure `Configuration`, construct explicit services around `Client`, and pass their named request DTOs to receive typed results. The job-status service is implemented; other old resource calls remain planned replacements. Old calls such as `list_projects()` are **not yet replaced** on this branch: their replacements remain planned work, not usable examples.

Failures now use typed exceptions under `Automattic\Wistia\Exception`. Check `remoteOutcomeUncertain` before deciding how to reconcile a state-changing operation; do not blindly replay an upload or share-link request. Requests are sent once, with redirects disabled. The default uses dedicated HTTP/1.1 cURL connections, nonrewindable streamed bodies and no environment proxies or authentication negotiation to prevent native replay. Injected transports must preserve these constraints. No live API or paid-plan behavior is verified by this foundation change.

## Tests and safety

In an environment with the declared development dependencies already available:

```sh
composer test
# Equivalent default offline discovery:
vendor/bin/phpunit
```

Both discover `tests/Offline` and offline `tests/Contract`, load no legacy credential configuration and use controlled transports, Guzzle MockHandler and synthetic native cURL completion, not real HTTP. Foundation, OAuth, pagination, polling and job-status contract cases are included; no live verification is claimed. The combined P02–P04 default Composer and strict suites passed on PHP 8.4.26 and 8.5.10 with Guzzle 8.2.0 / PHPUnit 12.5.39 under OS-enforced network denial (236 tests, 3871 assertions per run). Strict runs also started with caller exception arguments ignored; the test-owned bootstrap enables capture for redaction assertions without an application/global PHP configuration change. PHP 8.4 plus a newer supported runtime and externally network-denied execution remain verification gates; syntax checks alone do not satisfy them.

The historical live/destructive tests remain in `tests/ClientTest.php` and `tests/ApiMethodsTraitsTest.php` for provenance, excluded from default discovery. The separately named `phpunit.legacy.xml.dist` bootstrap refuses execution unless **both** `WISTIA_ENABLE_LEGACY_LIVE_TESTS=1` and `WISTIA_APPROVE_DESTRUCTIVE_TESTS=1` are set. These flags are technical gates, not permission. The legacy tests also require the original 1.x client/PHPUnit environment and are **not compatible with this rewrite**. Do not run them on this branch or supply credentials as a local check; do not exercise charge-incurring operations.

## Credits

- [Automattic](https://github.com/Automattic)
- [Nicola Mustone](https://nicolamustone.com)

## License

Wistia PHP - PHP wrapper to consume Wistia API

Copyright (C) 2015 Automattic

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

Please see [License File](LICENSE) for more information.

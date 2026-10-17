# Wistia PHP

[![Latest Version](https://img.shields.io/github/release/Automattic/wistia-php.svg?style=flat-square)](https://github.com/Automattic/wistia-php/releases)
[![Software License](https://img.shields.io/badge/license-GPLv2-brightgreen.svg?style=flat-square)](LICENSE)

A framework-independent PHP client for Wistia, undergoing a breaking rewrite.

## Current development scope

P02 provides the shared client/configuration, HTTP execution, encoding, decoding and failure foundation. P03 adds [bounded pagination and optional job polling](docs/pagination.md) and the typed [background-job-status Data operation](docs/reference/data.md#get_background-job-status-backgroundjobstatusid). Construct `DataBackgroundJobStatusService` directly around `Client`. P04 adds [OAuth helpers](docs/oauth.md) for authorization URLs, explicit code exchange and refresh; applications own callbacks, correlation, persistence and renewal policy. P05 adds inbound [webhook helpers](docs/webhooks.md) that verify original bytes before typed batch parsing; applications own routing, processing, deduplication, ordering and replay protection. P06 adds 19 [media lifecycle and organization operations](docs/media.md), with typed nested results, local validation, lazy pagination and job handoff. P07 adds 24 [media customization operations](docs/customizations.md), preserving replacement, partial-update and explicit-null semantics with typed nested inputs/results. P08 adds 16 [caption and accessibility operations](docs/accessibility.md), including text formats, atomic versioned edits, speaker-profile reads and existing localization/audio-description management. P09 adds 17 [folder, sharing and subfolder operations](docs/folders.md), including lazy pagination, typed nested sharing/media records and asynchronous copy/bulk-delete job handoff. P10 adds 16 [channel, collaborator and episode operations](docs/channels.md), including typed podcast settings, publishing/scheduling and lazy pagination. P11 adds ten [webinar, collaborator and registration operations](docs/webinars.md), including UTC scheduling, nested updates, typed engagement records and filtered pagination. P12 adds nine [brand and account-branding operations](docs/brands.md), including explicit-null updates, application/inheritance options, lazy pagination, preload selections and color-picker results. P13 adds 11 [account and access-management operations](docs/account.md), including typed usage/credits/token metadata, allowed-domain pagination, invitations and experimental expiring tokens. P14 adds nine [search and review-sharing operations](docs/search-sharing.md), with typed grouped search/transcript results, metadata filters, resource URLs, bundle pagination and write-safe share links. P15 adds [non-purchasing bulk actions and jobs](docs/bulk.md), with typed resource payloads, local limits and partial-result job handoff. P17 adds all ten [Stats operations](docs/reference/stats.md), with typed account/media/event/visitor records, inclusive by-date queries and lazy offset pagination. P18 adds five [account Analytics operations](docs/reference/analytics.md), with exclusive-end date ranges, typed ranking/timeseries results and embed-location lookup. P19 adds seven [media Analytics operations](docs/reference/analytics.md#media-analytics-usage), with typed aggregate/traffic/language metrics, embed-location timeseries and cursor-paginated form conversions. P20 adds five [webinar Analytics operations](docs/reference/analytics.md#webinar-analytics-usage), with typed polls/chats/histograms and bounded audience traversal. P16 adds five [upload/import workflows](docs/reference/upload.md), with streamed files and operation-specific results; S3 copy remains unimplemented pending human confirmation of its response contract. Do not treat the [endpoint inventory](docs/rewrite/README.md) as complete installed coverage; this development branch is not a complete replacement for the published 1.x package.

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

See [foundation contracts](docs/foundation.md) for transport injection, exact-name encoding, result/decoder boundaries, and safe error handling. Applications own OAuth callbacks, correlation and secure token persistence. Webhook verification/parsing is implemented; routing, processing, deduplication, ordering and replay protection remain application-owned.

## Breaking migration status

The legacy array constructor, magic endpoint methods, `get_client()`, `get_token()`, public transport/format/last-response fields, raw decoded-object/error-code returns and XML mode are removed. There is no compatibility layer. Configure `Configuration`, construct explicit services around `Client`, and pass their named request DTOs to receive typed results. The job-status, P06 media/tag/trim/deleted-media, P07 customization, P08 caption/accessibility P09 folder/sharing/subfolder P10 channel/collaborator/episode P11 webinar/collaborator/registration, P12 brand/account-branding, P13 account/access-management, P14 search/review-sharing, P15 non-purchasing bulk, P17 Stats, P18 account Analytics P19 media Analytics, P20 webinar Analytics and the five non-S3 P16 upload/import services are implemented. Replace `list_projects()` with `DataFoldersService::listFolders(new Request\Data\ListFoldersRequest(...))` and typed results; see the [folder migration notes](docs/folders.md). Other old resource calls remain planned replacements.

Failures now use typed exceptions under `Automattic\Wistia\Exception`. Check `remoteOutcomeUncertain` before deciding how to reconcile a state-changing operation; do not blindly replay an upload or share-link request. Requests are sent once, with redirects disabled. The default uses dedicated HTTP/1.1 cURL connections, nonrewindable streamed bodies and no environment proxies or authentication negotiation to prevent native replay. Injected transports must preserve these constraints. No live API or paid-plan behavior is verified by this foundation change.

## Tests and safety

In an environment with the declared development dependencies already available:

```sh
composer test
# Equivalent default offline discovery:
vendor/bin/phpunit
```

Both discover `tests/Offline` and offline `tests/Contract`, load no legacy credential configuration and use controlled transports, Guzzle MockHandler and synthetic native cURL completion, not real HTTP. Foundation, OAuth, webhook, pagination, polling, job-status, P06 media, P07 customization, P08 caption/accessibility P09 folder/sharing/subfolder P10 channel/collaborator/episode P11 webinar/collaborator/registration P12 brand/account-branding P13 account/access-management P14 search/review-sharing P15 non-purchasing bulk, P17 Stats, P18 account Analytics P19 media Analytics, P20 webinar Analytics and the five non-S3 P16 upload/import contract cases are included. The PHP upload service has offline coverage; separately authorized standalone live probes established the limited [upload observations](docs/rewrite/upload-evidence.md), not completed processing or live PHP verification. The P06 PHP 8.4.26 network-denied default run passed 669 tests / 6721 assertions; full runtime/review evidence is retained in the delivery handoff. The combined P02–P05 default Composer and strict suites passed on PHP 8.4.26 and 8.5.10 with Guzzle 8.2.0 / PHPUnit 12.5.39 under OS-enforced network denial (310 tests, 4599 assertions per run). Strict runs also started with caller exception arguments ignored; the test-owned bootstrap enables capture for redaction assertions without an application/global PHP configuration change. PHP 8.4 plus a newer supported runtime and externally network-denied execution remain verification gates; syntax checks alone do not satisfy them.

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

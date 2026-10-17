# Wistia PHP

[![Latest Release](https://img.shields.io/github/release/Automattic/wistia-php.svg?style=flat-square)](https://github.com/Automattic/wistia-php/releases)
[![PHP](https://img.shields.io/badge/php-%5E8.4-777BB4?style=flat-square)](composer.json)

A framework-independent PHP client for Wistia, with typed requests and results for media management, uploads, reporting, and more.

> [!IMPORTANT]
> This branch contains the breaking rewrite targeting **2.0.0**. The examples below describe the development API, not the published 1.x package. The rewrite is still in progress: S3 copy is not implemented, and complete release acceptance is pending.

## Installation

Requires PHP **8.4+**, the **cURL extension**, and Composer. Guzzle `^8.2` is installed as a dependency.

To try the development API before 2.0.0 is released:

```sh
composer require automattic/wistia-php:dev-main
```

This selects the moving development branch. Pin a reviewed commit when evaluating it in an application.

## Quick start

Set `WISTIA_API_TOKEN` through your application's secret management, then fetch a page of media:

```php
<?php
require 'vendor/autoload.php';

use Automattic\Wistia\Client;
use Automattic\Wistia\Configuration;
use Automattic\Wistia\DataMediaService;
use Automattic\Wistia\Request\Data\ListMediaRequest;

$token = getenv('WISTIA_API_TOKEN');
if ($token === false || $token === '') {
    throw new RuntimeException('Configure WISTIA_API_TOKEN in your application.');
}

$client = new Client(new Configuration(token: $token));
$media = new DataMediaService($client);
$result = $media->listMedia(new ListMediaRequest(per_page: 25));

foreach ($result->data as $item) {
    echo $item->hashed_id, ': ', $item->name, PHP_EOL;
}
```

Construct a service around the shared `Client`, pass its named request object, and read the typed result. Request and result properties preserve Wistia's field names. The library does not load `.env` files.

For multiple pages, use bounded, lazy pagination:

```php
$pages = $media->paginateMedia(new ListMediaRequest(per_page: 100), maxPages: 10);

foreach ($pages->items() as $item) {
    echo $item->name, PHP_EOL;
}
```

See [pagination and background jobs](docs/pagination.md) for cursor strategies, stopping conditions, and optional finite job polling. A queued job or accepted upload does not guarantee completed processing.

## API guides

| Area | Guide |
| --- | --- |
| Media, tags, trims, archived and deleted media | [Media management](docs/media.md) |
| Player settings, thumbnails, captions, and engagement tools | [Customizations](docs/customizations.md) |
| Caption edits, speaker profiles, existing localizations, and audio descriptions | [Captions and accessibility](docs/accessibility.md) |
| Folders, subfolders, and sharing permissions | [Folders](docs/folders.md) |
| Channels, podcasts, collaborators, and episodes | [Channels](docs/channels.md) |
| Webinars, collaborators, and registrations | [Webinars](docs/webinars.md) |
| Brands and account branding | [Brands](docs/brands.md) |
| Account usage, credits, allowed domains, invitations, and access tokens | [Account and access management](docs/account.md) |
| Search, review bundles, resource URLs, and share links | [Search and review sharing](docs/search-sharing.md) |
| Non-purchasing bulk actions and job results | [Bulk operations](docs/bulk.md) |
| File uploads, URL imports, thumbnails, and supplied-audio imports | [Upload and import](docs/reference/upload.md) |
| Account, media, event, visitor, and project metrics | [Stats](docs/reference/stats.md) |
| Account, media, and webinar analytics | [Analytics](docs/reference/analytics.md) |
| Authorization URLs, code exchange, and explicit token refresh | [OAuth](docs/oauth.md) |
| Signature verification and typed inbound event parsing | [Webhooks](docs/webhooks.md) |

The [Data reference](docs/reference/data.md) details individual operations. The [rewrite inventory](docs/rewrite/README.md) tracks implemented coverage, exclusions, and unresolved contracts; planned entries are not installed functionality. Account permissions and plan restrictions still apply.

## Configuration and failures

Configure total and connection timeouts in seconds (defaults: 30 and 10):

```php
$client = new Client(new Configuration(
    token: $token,
    timeout: 60.0,
    connectTimeout: 10.0,
));
```

Client construction sends no request. API credentials use Bearer headers; Data, Stats, and Analytics use the pinned API version `2026-09`. Upload and OAuth follow their own contracts.

Failures use typed exceptions under `Automattic\Wistia\Exception`, distinguishing authentication, permission, rate-limit, API, transport, and decoding errors. Remote failures expose HTTP status, available rate-limit metadata, and `remoteOutcomeUncertain`.

> [!WARNING]
> Requests are sent once, without automatic retries or redirects. If `remoteOutcomeUncertain` is true after a state-changing request, reconcile the remote state before submitting it again. A timeout does not mean the operation failed to reach Wistia.

See the [foundation guide](docs/foundation.md) for transport injection, timeout limits, encoding, and safe error handling. The default transport does not discover environment proxies; injected transports must preserve the no-replay and credential-safety contracts. Applications own secret storage, OAuth callbacks and token renewal, webhook processing and replay protection, and safe logging.

## Migrating from 1.x

The 2.0 API is a breaking replacement with **no compatibility layer**:

- Replace the array constructor with `new Client(new Configuration(...))`.
- Replace magic endpoint calls with explicit services and named request objects. For example, `list_projects()` becomes `DataFoldersService::listFolders(new ListFoldersRequest(...))`; see the [folder migration notes](docs/folders.md).
- Read typed results instead of raw decoded objects or error codes, and handle typed exceptions.
- Remove uses of `get_client()`, `get_token()`, public transport/format/last-response fields, and XML mode.

Use each API guide to check field names, null/update semantics, pagination, and asynchronous results before migrating a workflow.

## Tests and safety

With the declared development dependencies available (PHPUnit `^12.5`):

```sh
composer test
# Or run the same default offline suites directly:
vendor/bin/phpunit
```

Default discovery runs only `tests/Offline` and `tests/Contract`, using synthetic responses and controlled transports. It loads no live credentials and makes no real HTTP requests. These checks do not establish live API behavior, paid-plan access, or processing completion. Limited, separately authorized [upload observations](docs/rewrite/upload-evidence.md) are documented separately.

> [!CAUTION]
> Historical tests in `tests/ClientTest.php` and `tests/ApiMethodsTraitsTest.php` can upload data and create or delete Wistia resources. They are excluded from default discovery, separately gated by `phpunit.legacy.xml.dist`, and incompatible with the rewrite. Do not run them on this branch or supply credentials as a local check. Their opt-in flags do not grant permission for live, destructive, or chargeable operations.

---

By [Automattic](https://github.com/Automattic) and [Nicola Mustone](https://nicolamustone.com). Copyright (C) 2015 Automattic. Distributed under [GPL-2.0-or-later](LICENSE).

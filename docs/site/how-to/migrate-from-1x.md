# Migrate from the 1.x client

The 2.0 development client is a breaking rewrite. There is no compatibility layer for magic methods, generic endpoint calls or raw-object/error-code results. Migrate in a branch of your application and keep the existing integration available until your checks pass.

## Prepare the application

Use PHP 8.4+, ext-curl and Composer. The package remains `automattic/wistia-php`; [install the development version](../tutorials/first-api-call.md#install-the-development-package) and pin the reviewed revision you test. This guide does not announce a 2.0 release.

Find calls to `new Client(['token' => …])`, magic methods such as `list_medias()` and `create_project()`, and generic `get()`/`post()`/`put()`/`delete()` dispatch. Also find consumers of `last_response_code`, numeric return codes and raw response objects. Replace these call sites deliberately rather than renaming methods globally.

## Replace a media-list call

A legacy call looked like this:

```text
$legacy = new Client(['token' => $token]);
$records = $legacy->list_medias(['per_page' => 25]);
```

The equivalent one-page read uses a service, a named request and a typed result. Save the following as `list-media.php` beside your application's `vendor/` directory. Configure `WISTIA_API_TOKEN` through your environment or secret manager before running it:

```php
<?php
require 'vendor/autoload.php';

use Automattic\Wistia\Client;
use Automattic\Wistia\Configuration;
use Automattic\Wistia\DataMediaService;
use Automattic\Wistia\Exception\WistiaException;
use Automattic\Wistia\Request\Data\ListMediaRequest;

$token = getenv('WISTIA_API_TOKEN');
if ($token === false || $token === '') {
    throw new RuntimeException('Configure WISTIA_API_TOKEN before running this script.');
}
$client = new Client(new Configuration(token: $token));
$media = new DataMediaService($client);
try {
    $result = $media->listMedia(new ListMediaRequest(per_page: 25));
    echo 'Media records on this page: ', count($result->data), PHP_EOL;
} catch (WistiaException $error) {
    fwrite(STDERR, 'Wistia request failed (HTTP ' . ($error->status ?? 'unavailable') . ').' . PHP_EOL);
    exit(1);
}
```

Run `php list-media.php`. This makes one read-only request and prints `Media records on this page: N`, not a total account count. A successful result has typed records in `data` and HTTP `status`; it is not the old bare array. A rejected request throws an exception rather than returning an integer error code. The library does not load `.env` files or log credentials.

For runnable repository examples with bounded traversal, uploads, imports, polling and webhook handling, follow [Plain PHP examples](../integrations/plain-php.md).

## Map the remaining operations

| Old integration pattern | Development API replacement |
| --- | --- |
| `list_medias`, `show_media`, `update_media`, `delete_media`, `copy_media` | `DataMediaService` methods and their `Request\Data` types; use [media](media.md) and the generated service reference for exact inputs and results |
| `list_projects`, `show_project`, project writes and sharing | `DataFoldersService` and `DataFolderSharingsService`; follow [folders and sharing](folders.md) |
| `create_media($path, $options)` | `UploadService::uploadFile()` with `UploadFileRequest`, `LocalFile` and `UploadOptions`; follow [uploads](uploads.md) |
| Account, customization and caption magic calls | `DataAccountService`, `DataCustomizationsService`, `DataCaptionsService`; select the named request in the [service directory](../reference/services.md) |
| Generic endpoint strings, `.json` suffixes, Basic auth or `api_password` | Explicit service methods, Bearer-header configuration and family-specific request contracts |
| Raw objects and `last_response_code` | Operation-specific typed result, its status, or a [typed exception](errors.md) |
| Application loops that guess pagination or wait forever | Resource-specific [paginators](pagination.md) and [finite job polling](jobs.md) |

Modern Data uses folders and often `folder_id`; Upload still uses `project_id`, and Stats retains project terminology on its wire paths. Do not blanket-rename fields. Data, Stats and Analytics use the pinned `2026-09` version at the modern host. Upload and OAuth have separate unversioned hosts/contracts; the client chooses them for the operation.

## Adapt result and failure handling

Upload media is not modern Data media. Thumbnail and supplied-audio import results are status-only; normal upload, URL import and S3 copy return Upload media. Asynchronous Data operations can return job information instead. An accepted response is not proof of processing completion or playability.

Preserve omission versus explicit null for updates. Optional result fields may be absent; consult `presentFields` where available. Keep wire names such as `hashed_id` and `embedCode` rather than converting every field to one style. See [update semantics](../concepts/updates.md).

Catch the appropriate authentication, permission, rate-limit, API, transport or decoding exception. Read safe metadata such as status and rate hints; do not print raw bodies, token-bearing URLs or arbitrary traces. A write with `remoteOutcomeUncertain` may already have succeeded: reconcile it before any manual retry. The default transport does not retry or follow redirects, and middleware you inject must preserve those protections.

## Keep application responsibilities explicit

Move token/callback handling through the [OAuth guide](oauth.md), with application-owned correlation and persistence. Route original webhook bytes and the received signature through [verification and parsing](webhooks.md), then add durable processing, deduplication and ordering in your application. These helpers do not host routes or store state.

[Coverage and limitations](../reference/coverage.md) distinguishes offline checks, limited live observations, unverified permissions/features and excluded purchasing/generation/trial operations. Do not run the historical live/destructive test suite as a migration check. Test application behavior with controlled transports before choosing any separately authorized live validation.

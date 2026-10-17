# Make your first API call

In this tutorial, you will install the 2.0 development client and print the number of media records on one page. The call is read-only: it does not upload, change or delete anything.

You need PHP 8.4+, the cURL extension, Composer and a Wistia API token allowed to read folder and media data. This tutorial assumes you can run PHP scripts from a terminal.

## Install the development package

Run this in your application's directory:

```sh
composer require automattic/wistia-php:dev-main
```

The 2.0 API is not released yet. `dev-main` follows the moving development branch; pin a reviewed commit when evaluating it in an application. Composer installs Guzzle as a dependency.

## Configure your token

Provide `WISTIA_API_TOKEN` through your application's environment or secret manager before running the script. Do not put the token in the PHP file, a CLI argument or a committed configuration file.

The library does not load `.env` files. If your framework loads one, it is your application's configuration mechanism, not client behavior.

## Create the client

Create `list-media.php` beside your application's `vendor/` directory:

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
    // Log only selected safe metadata, not the request, response or entire trace.
    fwrite(STDERR, 'Wistia request failed (HTTP ' . ($error->status ?? 'unavailable') . ').' . PHP_EOL);
    exit(1);
}
```

Constructing the client, service and request sends no HTTP. Calling `listMedia()` sends the request.

## Run and check the result

```sh
php list-media.php
```

On success, the output is `Media records on this page: N`, where `N` is the count returned, up to 25. Zero is a valid empty page. This is not a total count of everything in the account.

On failure, the script exits with code 1 and a sanitized status. Check token configuration for 401, access/feature permissions for 403, and [failure handling](../how-to/errors.md) for other cases. Do not dump credentials or raw responses to diagnose an error.

## What you used

- `Configuration` holds credentials and finite timeouts.
- `Client` handles request execution.
- `DataMediaService` exposes explicit media methods.
- `ListMediaRequest` names the inputs.
- `$result->data` is a list of typed media records, not an unstructured JSON response.

## Next steps

[Iterate more pages](../how-to/pagination.md), [read media fields](../how-to/media.md), or learn [how services, requests and results fit together](../concepts/client-model.md). The how-to examples assume the configured `$client` from this tutorial; keep the autoloader and token setup in your application.

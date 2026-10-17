# Run the plain-PHP examples

Use these programs when you want a complete CLI entry point rather than assembling documentation snippets.

You need a reviewed repository checkout, PHP 8.4+, cURL and the library's Composer dependencies. Run the commands below from that checkout's root. They use the 2.0 development API.

## Configure inputs privately

API examples read `WISTIA_API_TOKEN` from the environment; they do not load `.env`. Provide it through your secret manager or private environment. Webhook verification uses separate `WISTIA_WEBHOOK_SECRET` and `WISTIA_WEBHOOK_SIGNATURE` values.

Do not place credentials or signed source URLs in CLI arguments or enable shell tracing around them. Reading from an environment does not make the value suitable for logging.

## Choose a program

| Task | Command | Effect |
| --- | --- | --- |
| [List media](../../../examples/plain-php/list-media.php) | `php examples/plain-php/list-media.php` | Reads one page, then separately traverses from page one with a three-page bound |
| [Upload a file](../../../examples/plain-php/upload-file.php) | `php examples/plain-php/upload-file.php --execute /absolute/path/video.mp4` | Creates media; streams the local file |
| [Import a URL](../../../examples/plain-php/import-url.php) | `php examples/plain-php/import-url.php --execute` | Creates media from `WISTIA_SOURCE_URL` |
| [Follow an existing job](../../../examples/plain-php/poll-job.php) | `php examples/plain-php/poll-job.php YOUR_JOB_ID 5` | At most five read-only status checks |
| [Verify a saved webhook](../../../examples/plain-php/webhook.php) | `php examples/plain-php/webhook.php /secure/path/original-body.json` | Local signature verification/parsing only |

Review write effects before using `--execute`. Upload/import can consume storage/quota and perform no automatic cleanup. The webhook CLI does not host a route, acknowledge a delivery or process events.

## Check expected output

Listing prints counts, not media names or URLs. Upload/import print status and whether an ID was supplied, not playback completion. Job polling reports `Completed`, `ReportedFailure` or `Exhausted`; partial action failures count as reported failure.

Exit codes are 0 for success, 1 for typed library failure, 2 for local input/configuration error, 3 for reported job failure and 4 for traversal/poll exhaustion or a non-advancing cursor.

No program automatically retries requests. A traversal that reaches its bound is incomplete, not a complete export.

## Separate examples from tests

Real API examples contact Wistia. Do not run them as a credential-free test suite. With development dependencies already available, the example suite injects synthetic transports:

```sh
vendor/bin/phpunit tests/Offline/ExamplesTest.php
```

For a local-only webhook fixture and the example implementation layout, use the [repository example instructions](../../../examples/README.md). For production HTTP handling, follow the [webhook guide](../how-to/webhooks.md), not the saved-file CLI entry point.

# Runnable examples

These examples use the typed **2.0 development API**, not the published 1.x client. The plain-PHP CLI programs below are framework-independent; the [Laravel integration](laravel/README.md) adds consumer-owned container bindings, an Artisan command and a webhook route. Symfony integration is not included yet.

## Framework integration

- [Laravel 13 / PHP 8.4+](laravel/README.md): isolated consumer setup, configuration caching, read-only Artisan media listing, verified webhook acknowledgement and offline framework integration tests.

## Plain-PHP setup and safety

Use a reviewed checkout of this repository with PHP 8.4+, ext-curl and the declared Composer dependencies already provisioned (see [installation](../README.md#installation)). Run commands below from the repository root. Each entry loads `vendor/autoload.php` relative to its own file; no working-directory edits or snippet assembly are needed.

Provide `WISTIA_API_TOKEN` through your application's environment/secret manager for API examples. No script loads `.env`. Never commit credentials or put tokens or signed URLs in CLI arguments. Use an account/token with permission for the selected operation. Account permissions and plan limits still apply.

**Upload and import create remote media and may consume storage or account quota.** Review the source and remote effects first; both require the explicit `--execute` flag. Examples perform no cleanup, deletion, purchases or automatic retry. Do not run live examples as tests. Listing and polling make read-only API calls; webhook verification is local only.

Output deliberately omits media metadata, IDs, URLs, signatures, raw bodies and exception traces. Applications may read typed results in [Application.php](plain-php/Application.php) to persist IDs securely; accepted uploads are not automatically playable.

## Workflows

| Workflow / entry | Additional input | Run command | Expected outcome and effects |
| --- | --- | --- | --- |
| [List media](plain-php/list-media.php) | API token | `php examples/plain-php/list-media.php` | Read-only: count one page (25 records maximum), then separately traverse from page one lazily, bounded to three pages. Prints counts, not metadata. |
| [Upload local video](plain-php/upload-file.php) | API token; readable local MP4 file | `php examples/plain-php/upload-file.php --execute /absolute/path/video.mp4` | **Creates media** via `UploadService::uploadFile`. Reports `UploadFileResult` HTTP status and whether `data->hashed_id` was supplied. The file is streamed and its library-owned handle closed. Bytes/codec validity remain server-owned. |
| [Import URL](plain-php/import-url.php) | API token; `WISTIA_SOURCE_URL` containing a direct HTTP(S) file URL | `php examples/plain-php/import-url.php --execute` | **Creates media** via `UploadService::importUrl`. Reports `ImportUrlResult`, not a Data import job. Signed query strings remain intact and are never printed. Keep source grants valid for hours for deferred fetching. |
| [Poll existing job](plain-php/poll-job.php) | API token; an existing background-job hashed ID; attempt limit 1–99 | `php examples/plain-php/poll-job.php YOUR_JOB_ID 5` | Read-only: status checks at one-second intervals, at most five attempts. Reports `Completed`, `ReportedFailure` (including partial bulk failures), or `Exhausted`, plus aggregate bulk counts when present. Never submits the operation that created the job. |
| [Verify/parse webhook](plain-php/webhook.php) | Original body file (up to 1 MiB); received header value in `WISTIA_WEBHOOK_SIGNATURE`; separate `WISTIA_WEBHOOK_SECRET` | `php examples/plain-php/webhook.php /secure/path/original-body.json` | Local only: verifies original bytes with the received signature, then parses and counts events. Does not process events or send an acknowledgement. No API token needed. |

The upload/import results are operation-specific Upload media objects, not modern Data Media objects. Optional fields can be absent. No job ID is invented, and no polling or playback guarantee follows from acceptance. Use an appropriate existing media read in your application to reconcile processing; stopping a local program does not cancel remote work. See [upload/import contracts](../docs/reference/upload.md).

A traversal hitting its page limit stops with incomplete results and exit 4; increase bounds deliberately in application code, not by unbounded looping. Poll exhaustion is a local stop, not remote failure or cancellation. Each API request also has the client's finite default total/connection timeouts (30/10 seconds). A polling read failure stops immediately without retry.

## Webhook integration and fixture

The CLI file represents bytes saved by your HTTP application. Production routing must read the original request body and the received `X-Wistia-Signature` header, reject ambiguous duplicate headers, enforce body limits before buffering, and pass the separate webhook secret (not an API token). Do not JSON-decode/re-encode before verification. Your application owns HTTP responses, secret rotation, durable UUID-based deduplication, replay/freshness controls, concurrency, and out-of-order/batched event handling. HMAC alone does not prove freshness. See [webhook responsibilities](../docs/webhooks.md#failures-and-consumer-responsibilities).

For a **fixture-only**, credential-free demonstration, create a body and synthetic signature locally:

```sh
fixture_dir=$(mktemp -d)
printf '%s' '{"hook":{"uuid":"fixture"},"events":[]}' > "$fixture_dir/body.json"
export WISTIA_WEBHOOK_SECRET='fixture-only-not-a-real-secret'
export WISTIA_WEBHOOK_SIGNATURE="$(php -r 'echo hash_hmac("sha256", file_get_contents($argv[1]), getenv("WISTIA_WEBHOOK_SECRET"));' "$fixture_dir/body.json")"
php examples/plain-php/webhook.php "$fixture_dir/body.json"
rm "$fixture_dir/body.json"
rmdir "$fixture_dir"
unset WISTIA_WEBHOOK_SECRET WISTIA_WEBHOOK_SIGNATURE
```

Expected: `Verified events: 0. No events processed.` Generating the signature yourself is valid only for this synthetic demonstration. For incoming deliveries use the received signature; signing an untrusted incoming body yourself does not verify its sender.

## Failures

Exit codes: **0** successful example outcome; **1** typed library failure; **2** local invalid input/configuration; **3** job-reported failure (including partial failure); **4** exhausted polling/traversal or non-advancing cursor. Output identifies typed library exception classes and HTTP status without dumping sensitive error documents. Local validation errors are distinct from typed library failures. No automatic resubmission occurs.

If a state-changing call has `remoteOutcomeUncertain`, reconcile remote state before submitting again: a timeout or decoding failure can follow a successful write. Even a definite failure is not permission for blind retry. Authentication, permission and rate-limit failures require application-specific handling. Run commands with a private terminal/session and avoid shell tracing or logging environment values.

## Reproducible offline checks

With development dependencies already provisioned:

```sh
vendor/bin/phpunit tests/Offline/ExamplesTest.php
composer test
find examples -name '*.php' -exec php -l {} \;
```

The focused suite loads the actual entry files and invokes their callable entry points with a controlled transport, synthetic secrets/bodies, temporary local upload files, and fake polling time. It exercises successful workflows, input rejection, typed read/write failures, uncertain write outcomes, traversal bounds, partial job failure, exhaustion, changed original bytes and verified malformed JSON. It requires no Wistia credentials or HTTP. The CLI branch supplies real environment/output and the default transport; tests inject these dependencies rather than allowing HTTP.

These are offline checks, not proof of OS-enforced network denial, live Wistia acceptance, media processing completion, deployed HTTP routing or application replay protection. Historical live/destructive suites remain excluded; never use them to check these examples.

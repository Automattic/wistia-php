# Integrated rewrite acceptance

This is the maintained criterion map for [#29](https://github.com/Automattic/wistia-php/issues/29) against [specification #8](https://github.com/Automattic/wistia-php/issues/8). It describes the integrated implementation and its verification boundaries, not release approval. Current command results and independent-review reports accompany delivery; a source/test pointer alone is not an executed pass.

## Integration boundary

The implementation baseline is `6a4a2980f1a43eea93e06911b4769aea72082043` on `main`. Native parent #8 and all 17 native blockers (#12–#28) were read with pagination. Their delivery PRs #33–#48, #50 and #61 are ancestors of this baseline; their recorded verification/review evidence was inspected rather than inferring integration from closed issue states. The shared foundation/pagination PRs #31/#32 are also integrated. Documentation and consumer examples from #56–#60 are integrated.

The catalog remains pinned to the first-party source snapshot in [operations.json](operations.json): 167 catalog entries, 161 included and six excluded. The root Upload alias covers two of six workflow definitions, yielding **160 non-Upload catalog methods plus six Upload workflow methods**. OAuth, webhook, pagination and polling helpers are accounted for separately.

## Criterion map

| Criterion | Implementation and observable check | Boundary |
| --- | --- | --- |
| WIS-01 — Composer/configuration | [composer.json](../../composer.json) declares PHP ^8.4, ext-curl and Guzzle ^8.2. [Installation tutorial](../site/tutorials/first-api-call.md) configures a client. [ConsumerAutoloadTest](../../tests/Offline/ConsumerAutoloadTest.php) runs a separate consumer process from outside the repository cwd with an empty environment, existing Composer autoloading and a synthetic typed read. Composer platform/manifest checks inspect provisioned dependencies. | Fresh empty-consumer installation of the current local source package passed on PHP 8.4/8.5 under separate maintainer approval; see the evidence boundary below. Ordinary checks do not install dependencies. Composer's license identifier is normalized to `GPL-2.0-or-later`, matching the existing grant; strict manifest validation passes without the previous SPDX warning and `LICENSE` is unchanged. |
| WIS-02 — Complete inventory | [Inventory checker](../../tools/check-inventory.php) pins membership/counts, six exclusions, sources, wire schemas, experimental expiring tokens and Upload aliases. [Delivery checker](../../tools/check-delivery.php) connects all 166 distinct mappings to real signatures, request/result classes, documentation files and PHPUnit classes with test methods. [InventoryCoverageTest](../../tests/Offline/InventoryCoverageTest.php) rejects incomplete mappings and hidden implementation-resolution evidence. | Mapping existence is not semantic endpoint verification; endpoint contract suites provide the latter against synthetic sources. Catalog freshness beyond the pinned snapshot is not asserted. |
| WIS-03 — Explicit typed operations | Reflected method signatures match inventory request/result types; results implement `Result`. [Service directory](../site/reference/services.md) and generated reference expose named operations. | No magic-dispatch or legacy compatibility layer. |
| WIS-04 — Endpoint contracts | [Contract suites](../../tests/Contract/) cover Data, Upload, Stats and Analytics authentication, paths, request/response formats and failure paths. [Client](../../src/Client.php), `ApiFamily` and shared encoders retain endpoint-specific hosts and `2026-09` only where applicable. | Offline contract checks, not live upstream conformance. Original schema/source discrepancies remain explicit. |
| WIS-05 — Authentication/OAuth | [OAuth tests](../../tests/Offline/OAuthServiceTest.php), authorization tests and [OAuth guide](../site/how-to/oauth.md) cover URLs, code exchange, explicit refresh and sensitive data handling. | Account enablement, browser/session correlation, callbacks and token storage belong to the application; live OAuth is unverified. |
| WIS-06 — Upload/import | All six [Upload contracts](../../tests/Contract/Upload/) have operation-specific requests/results. [Streaming](../../tests/Offline/UploadStreamingTest.php) and [validation](../../tests/Offline/UploadValidationTest.php) checks cover stream ownership, invalid local inputs, signed URLs and uncertainty. | [Upload evidence](upload-evidence.md) separates limited standalone live observations, S3 human-support confirmation and offline PHP-service checks. |
| WIS-07 — Pagination | [Pagination tests](../../tests/Offline/Pagination/) plus resource contract cases cover individual pages, lazy bounded traversal, filters/order and non-advancing cursors. [Pagination guide](../site/how-to/pagination.md) explains resource-specific continuation. | No universal pagination model or snapshot-consistency guarantee. |
| WIS-08 — Jobs/polling | [Job tests](../../tests/Offline/Jobs/) and [runnable example checks](../../tests/Offline/ExamplesTest.php) cover identifiers/status, finite attempts, failure, partial bulk results and exhaustion. [Job guide](../site/how-to/jobs.md) explains outcomes. | Stopping local polling does not cancel remote work. Upload media/status-only responses do not acquire invented job IDs. |
| WIS-09 — Useful sanitized errors | Shared client, OAuth, webhook and resource cases distinguish API/authentication/permission/rate/transport/decode failures. [Redaction assertions](../../tests/Offline/ExceptionAssertions.php) inspect captured arguments; [rate-limit tests](../../tests/Offline/RateLimitInfoTest.php) protect accessible hints. | Applications and injected transports must avoid logging raw bodies, credentials, signed URLs and caller-owned traces. |
| WIS-10 — No unsafe replay | [Single-attempt handler tests](../../tests/Offline/SingleAttemptCurlHandlerTest.php), transport tests and Upload/resource failures cover no retries/redirects and ambiguous-write uncertainty. | Default transport guarantees do not authorize arbitrary replaying middleware; reconcile before manual resubmission. |
| WIS-11 — Webhooks | [Verifier](../../tests/Offline/WebhookVerifierTest.php), parser/redaction and actual example checks cover original-byte HMAC verification before parsing, invalid signatures and unknown payloads. [Webhook guide](../site/how-to/webhooks.md) explains batch/deduplication/ordering responsibilities. | No hosted route, durable queue, replay store or freshness guarantee. Framework integration is separate consumer code. |
| WIS-12 — Offline defaults | [phpunit.xml.dist](../../phpunit.xml.dist) discovers only Offline/Contract suites. [Bootstrap](../../tests/bootstrap.php) loads no credentials and enables argument capture. Default and strict checks run on PHP 8.4 and a newer runtime under OS-level network denial. | Historical PHPUnit 4.8 live/destructive tests remain excluded and gated; they were not run. |
| WIS-13 — Usage/migration/limits | Public tutorial/how-to/reference/concept pages cover installation, authentication, pagination, jobs, webhooks and uploads. [Migration guide](../site/how-to/migrate-from-1x.md) replaces legacy calls with typed reads and explains failures/wire names. [Coverage page](../site/reference/coverage.md) records exclusions and live limitations. [Documentation example checker](../../tools/docs/check-examples.php) executes the tutorial and migration read with synthetic transports; [ExamplesTest](../../tests/Offline/ExamplesTest.php) executes actual plain-PHP entry points. | Other public fragments are statically checked, not all executed. Framework tests require separately provisioned consumers and are not part of default discovery. |
| WIS-14 — Identity/credit | [composer.json](../../composer.json) retains `automattic/wistia-php` and maintainer URL `https://nicolamustone.com`. Published history/license and Automattic attribution remain intact. | No version bump, tag or release follows from acceptance work. |

## Upload clarification disposition

P01's three named source-gap records cover four carried workflows. Their `unresolved` text is retained **upstream documentation provenance**. Each now has explicit `implementation_resolution` authority and an evidence pointer: approved standalone thumbnail/localization observations or relayed human S3 confirmation. `implementation_contract` on the workflow gives the adopted behavior. Source hashes and original `result_contract` strings are unchanged.

`tools/check-inventory.php --require-complete` requires recorded resolutions while still printing source gaps; it does not claim upstream docs were corrected. `tools/check-delivery.php` additionally requires the resolved workflows' implementation contracts and real mapped artifacts. Neither tool substitutes for contract test execution or verifies live plan access.

## Fresh consumer installation evidence

Under separate maintainer approval, two empty temporary Composer consumer projects installed a filtered copy of the current package through a local path repository labelled `dev-main`. Only `src/`, `composer.json`, `LICENSE` and `README.md` were copied; no `.env`, credentials, repository vendor tree or Git data was included. All 769 copied source/manifest/license/readme files were byte-compared with the checkout at that verification point. These installation checks predate the later release-copy and SPDX metadata cleanup; those follow-ups change neither runtime implementation nor dependency requirements.

Each consumer started without a lock file or vendor directory and used an isolated Composer home/cache, with plugins and scripts disabled (`composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist`). Composer fetched public dependencies and resolved Guzzle **8.3.0** within the declared `^8.2` constraint. Installation and non-dev platform checks passed on **PHP 8.4.26 and 8.5.10**. Both installed packages configured the default client without dispatching HTTP and performed one injected synthetic typed media read under OS-enforced network denial.

This verifies clean dependency resolution, installation/autoloading and client configuration for the **local source package at that verification point**. It does not verify a newly published 2.0 artifact, remote `dev-main` checkout selection, all future dependency combinations or live Wistia behavior. The repository's provisioned Guzzle 8.2 test suite remains distinct from these Guzzle 8.3 consumer smoke checks. Temporary consumer dependencies/lock files were not copied back into the repository.

## Reproducible checks and evidence classes

With declared development dependencies already provisioned, use PHP 8.4 and a newer supported runtime:

```sh
composer validate --no-check-publish --no-interaction
composer check-platform-reqs --no-interaction
composer test
php -d allow_url_fopen=0 vendor/bin/phpunit --fail-on-warning --fail-on-risky --fail-on-deprecation --fail-on-notice
php -d allow_url_fopen=0 tools/check-inventory.php --self-test --require-complete
php -d allow_url_fopen=0 tools/check-delivery.php
php tools/docs/check-examples.php
php tools/docs/check-repository-links.php
php tools/docs/build.php
php tools/docs/check.php
```

Run runtime checks inside an available OS network-denial mechanism. On the current macOS verification host, the wrapper is `sandbox-exec -p '(version 1)(allow default)(deny network*)' COMMAND`; `allow_url_fopen=0` alone is not network isolation. Provisioning dependencies, additional live calls, IAM/account setup, purchases and destructive tests require separate approval. Do not run the documentation installer merely to complete a check when the tool is absent.

Executed results, environment versions, exact checked-state fingerprints and independent Standards/Spec findings are recorded with delivery. Historical prerequisite results are supporting history, not fresh aggregate passes. The separately approved fresh-consumer checks above resolve the WIS-01 source-installation verification gap. The later license-identifier cleanup passed network-denied strict Composer validation on PHP 8.4/8.5; it does not assert another fresh installation. Live/plan restrictions and the published-artifact boundary remain explicit; aggregate acceptance does not authorize release. No other planned or unavailable check is silently converted into a pass.

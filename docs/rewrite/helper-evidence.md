# Historical helper verification evidence

This record preserves verification statements from the retired usage guides at [commit f29d015](https://github.com/Automattic/wistia-php/tree/f29d015f135947da01d8ca8a5455455e806b50d4/docs). These are historical observations, not fresh check results or claims of current live behavior, complete endpoint coverage or release acceptance. Use the [inventory](operations.json), [upload evidence](upload-evidence.md) and current delivery checks for later scope and unresolved contracts.

User-facing guidance is maintained on the [documentation website](https://wistia-php.space.fast/); its reproducible source remains in `docs/site/`.

## Client foundation

Original: [docs/foundation.md](https://github.com/Automattic/wistia-php/blob/f29d015f135947da01d8ca8a5455455e806b50d4/docs/foundation.md).

The original P02 offline PHPUnit suite covers shared configuration, encoding, status/content primitives, sanitized failures, rate information, family policy, safe Guzzle options, redirects and single-attempt ambiguous writes using synthetic fixtures. The [accepted P02 baseline](https://github.com/Automattic/wistia-php/pull/31) passed on PHP 8.4.26 and 8.5.10 with Guzzle 8.2.0 / PHPUnit 12.5.39 under OS-enforced network denial: 107 tests, 800 assertions per run, with caller `zend.exception_ignore_args` set to both 0 and 1. Current default discovery also includes P03's offline pagination/polling and job-status contracts and P04's OAuth and P05's webhook contracts; see the [contributing guide](../../CONTRIBUTING.md#make-and-test-your-change) for default test commands and safety boundaries. The offline bootstrap explicitly enables argument capture before exceptions; the isolated redaction child also explicitly enables it. Default memory remains 128M. Handler-level cases exercise the actual Guzzle factory finish path after a partially consumed POST and errno65/no response, errno0 recovery and empty writes. Isolated native-option instrumentation delegates to ext-curl without dispatch, checking streamed read/rewind behavior, small/large payload bytes, connection/protocol/negotiation controls and effective native timeout/TLS settings. Redaction checks inspect bounded library-owned trace arguments, normal exception strings, metadata and causes with exception arguments enabled. An isolated PHP subprocess additionally checks complete traces for nine adversarial synthetic failure cases without PHPUnit's caller/provider graph. These checks do not implement or accept any catalog endpoint. Focused diagnostics and syntax checks are not substitutes for PHPUnit. No live account, uploads, OAuth exchange or webhook processing is claimed; independent review and accepted integration are separate gates.

## Pagination and job helpers

Original: [docs/pagination.md](https://github.com/Automattic/wistia-php/blob/f29d015f135947da01d8ca8a5455455e806b50d4/docs/pagination.md).

Offline synthetic contract tests cover wire encoding, shape validation, filters/order, laziness, empty/final pages, cycles, bounds, all job states, partial failures and fake time/waits. Real default Composer/PHPUnit runs on PHP 8.4 and 8.5 under OS-enforced network denial are recorded in delivery evidence. These checks are not live Wistia verification, fresh-install proof, paid-plan entitlement proof, aggregate endpoint coverage or independent acceptance. P06's media operations have separate offline coverage; other resource endpoints, live credentials and the four P16 Upload contract gaps remain outside these helpers.

## OAuth helpers

Original: [docs/oauth.md](https://github.com/Automattic/wistia-php/blob/f29d015f135947da01d8ca8a5455455e806b50d4/docs/oauth.md).

Offline synthetic tests capture the exact authorization/code/refresh contract, typed token fields, scope/expiry/type boundaries, denial/rejected/expired-grant status handling, malformed successes, sanitized failures, rate data and one-attempt uncertainty. The suite is executed on PHP 8.4 and 8.5 under OS-enforced network denial. No real account authorization, token exchange, refresh, account-plan access or callback security integration has been live-verified. This helper guide does not claim delivery of the remaining endpoint catalog or whole-library acceptance.

## Webhook helpers

Original: [docs/webhooks.md](https://github.com/Automattic/wistia-php/blob/f29d015f135947da01d8ca8a5455455e806b50d4/docs/webhooks.md).

The offline tests use synthetic bodies and secrets, including an independent HMAC vector, valid/tampered/missing/malformed signatures, changed raw bytes, verification-before-JSON failures, required envelope types, single/empty/batched events, unknown payload shapes, metadata retention and sanitized error/debug paths. The actual default Composer and strict PHPUnit suites were executed on PHP 8.4.26 and 8.5.10 with OS-enforced network denial. The synthetic example was also executed. These are local contract checks, not proof of live Wistia delivery, framework-specific extraction, application replay protection or deployed routing. No live account calls were made. Independent review, common-document reconciliation and accepted integration are separate lead-owned gates.

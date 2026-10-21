# Coverage and verification limits

Wistia PHP 2.0 covers the included server-side Data, Upload, Stats and Analytics surface recorded in the repository's contract inventory. [Services and operations](services.md) lists the implemented service families; generated PHP reference describes their methods and types.

## Included surface

The pinned catalog contains 167 entries: 139 Data, ten Stats, 17 Analytics and one root Upload entry. Six charge-capable or commercially state-changing entries are excluded. Root Upload covers two workflows; six separately documented Upload workflows include local files, direct URLs, thumbnail files/URLs, S3 copy and supplied localization audio. These workflow definitions are not six extra catalog entries.

All included inventory entries have implementation markers and mapped typed methods, request/result classes, documentation and offline contract tests. OAuth authorization/code-exchange/refresh, webhook verification/parsing, resource-specific pagination and finite job polling are additional helpers, not extra catalog endpoints. The inventory is pinned source evidence, not continuous discovery of future Wistia changes.

## What offline checks establish

The default PHPUnit suites use controlled HTTP responses and synthetic metadata. They exercise request paths, authentication/version headers, encoding, typed decoding, local validation, pagination/polling bounds, file/stream ownership, redaction, rate hints and single-attempt/uncertain-write behavior. Example checks use injected transports rather than a Wistia account.

Offline verification targets PHP 8.4 and a newer supported runtime with operating-system network denial. These checks do not prove that a remote endpoint currently accepts a request, that a token has access, or that media processing completed. Current command results and criterion-level acceptance evidence belong to internal delivery records, not to a guarantee attached to every public method.

## Live observations are limited

| Workflow | Available evidence | What remains unverified |
| --- | --- | --- |
| Local video upload | A separately approved standalone probe returned 200 with Upload media JSON | Live PHP-service behavior, processing completion and general account/plan eligibility |
| Thumbnail file and URL | Standalone probes returned 201 with empty bodies; URL used POST and lowercase `url` | Applied thumbnail state and live PHP-service behavior |
| Supplied localization audio URL | Standalone probe at `upload-v2.wistia.com` returned 201 with an empty body | Other upload host, playback/processing completion and live PHP-service behavior |
| S3 copy | Maintainer-relayed Wistia human support confirmed normal 200 standard Upload media | S3 execution, IAM setup, permissions and live PHP-service behavior |
| Other endpoints and helpers | Offline contract/behavior checks | Live API behavior, OAuth account enablement, plan entitlement and deployed application integrations |

The thumbnail/localization guides have omissions or conflicting examples. Approved implementation contracts record the observed request/response behavior separately; this does not claim Wistia corrected its public documentation. S3's undocumented `create_media=false` mode is not exposed.

## Exclusions

The client does not expose text translation, caption purchases, localization generation/dubbing, audio-description ordering, purchasing bulk actions or starting account trials. An included free allowance does not make a charge-capable operation safe. Existing-localization/audio-description management and existing order-status reads are different from placing a new order.

Browser/player APIs, UI widgets and undocumented internal endpoints are outside this server-side client. There is no compatibility layer for the old magic interface; use the [migration guide](../how-to/migrate-from-1x.md).

## Check access in your application

Non-purchasing paid-plan features remain included even where no live entitlement is available. Token scope, resource ownership, delegated-contact permissions, plan features and remote validation can still reject requests. Expiring-token creation is explicitly experimental; see [account and access](../how-to/account.md#use-expiring-tokens-cautiously).

Your application owns credentials, secret storage, callback/webhook routes, persistence, deduplication and retry decisions. The library does not provision an account, IAM permissions or plan access. Historical live/destructive tests are separately gated and incompatible with the rewrite; they are not part of default verification. No release, live-call or purchase authorization follows from these docs.

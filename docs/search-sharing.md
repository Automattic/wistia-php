# Search and review sharing

Nine Data operations are implemented with named request/result types, synthetic offline contracts and no live verification. Construct each service around the shared `Client`; requests use Bearer authentication at `https://api.wistia.com/modern` with `X-Wistia-API-Version: 2026-09`.

## Services

| Service | Method | Request → result |
| --- | --- | --- |
| `DataSearchService` | `search` | `SearchRequest` → `SearchResult` |
| `DataResourceURLsService` | `resolveResourceURLs` | `ResolveResourceURLsRequest` → `ResolveResourceURLsResult` |
| `DataReviewBundlesService` | `listReviewBundles` | `ListReviewBundlesRequest` → `ListReviewBundlesResult` |
| `DataReviewBundlesService` | `createReviewBundle` | `CreateReviewBundleRequest` → `CreateReviewBundleResult` |
| `DataReviewBundlesService` | `deleteReviewBundle` | `DeleteReviewBundleRequest` → `DeleteReviewBundleResult` |
| `DataShareLinksService` | `resolveShareLink` | `ResolveShareLinkRequest` → `ResolveShareLinkResult` |
| `DataShareLinksService` | `showShareLink` | `ShowShareLinkRequest` → `ShowShareLinkResult` |
| `DataShareLinksService` | `updateShareLink` | `UpdateShareLinkRequest` → `UpdateShareLinkResult` |
| `DataShareLinksService` | `deleteShareLink` | `DeleteShareLinkRequest` → `DeleteShareLinkResult` |

Requests live under `Automattic\Wistia\Request\Data`, results under `Result\Data`, and nested response records under `Sharing`. Every result carries `status` and `data`. Missing optional properties become null; records expose `presentFields` to distinguish omission from explicit wire null. Closed schemas, required properties, primitive types and enums are checked during decoding. Responses and inputs have redacted debug views; deliberately reading a property can expose sensitive media/transcript data to application code.

## Search and resource URLs

`SearchRequest` requires `q`, which may be empty. Optional `tags` and `resource_type` are lists encoded as repeated `tags[]` / `resource_type[]` keys. Resource types are `media`, `folder`, `subfolder`, `channel`, `channel_episode`, and `webinar`. Tags match with OR semantics. `created_after` and `created_before` accept valid ISO8601 UTC timestamps ending in `Z`.

`SearchResult::data->data` contains typed lists named `folders`, `subfolders`, `medias`, `channels`, `channel_episodes`, and `webinars`. Media records include `folder_hashed_id`, typed `transcript_matches` (text, start time in seconds, formatted time and nullable thumbnail URL), and optional typed `custom_metadata_field_values`. Metadata values retain strings, numbers, booleans, lists, contact references or null; write provenance is nested and typed.

For example, given an application-configured `$client`:

```php
use Automattic\Wistia\DataSearchService;
use Automattic\Wistia\Request\Data\SearchRequest;
use Automattic\Wistia\Sharing\MetadataFilter;

$request = new SearchRequest(
    q: '',
    resource_type: ['media'],
    custom_metadata: [
        'region' => MetadataFilter::values(['emea', 'amer']),
        'budget' => MetadataFilter::numericRange(min: '0', max: '500'),
        'approved' => MetadataFilter::exact('false'),
        'shoot_date' => MetadataFilter::dateRange(after: '2026-01-01'),
        'owner' => MetadataFilter::exists(false),
    ],
    include: 'custom_metadata',
);
// Executes one request when called; the application owns credentials and approval.
$result = (new DataSearchService($client))->search($request);
```

`custom_metadata` maps field keys to `MetadataFilter` values. It requires `resource_type` to include `media`. Text/select values use `exact` or `values` (option keys, OR matching); booleans use the literal strings `'true'`/`'false'`; numbers/time use exact strings or numeric ranges; dates/datetimes use date strings or ranges. Presence uses `exists`. Either range bound may be omitted. The library does not know account field definitions: unknown/archived keys and unknown option keys remain remote 400s. Filtering or `include='custom_metadata'` requires custom-metadata account access; other accounts receive 403.

Search has **no pagination**. Custom metadata's primary match set contains at most 100 media; nonblank `q` can add up to 100 transcript-only matches, while an empty-query audit returns at most 100. Narrow large audits with filters such as creation dates; a result is not proof of exhaustive account coverage.

`ResolveResourceURLsRequest(type, hashed_id)` accepts `media`, `folder`, `channel`, `channel_episode`, `webinar`, or `remix`. Its result holds typed `type`, `hashed_id`, and `urls->show`. These are canonical Wistia UI deep links, returned only when the authenticated user may view the resource—not a public-access grant.

## Review bundles

`CreateReviewBundleRequest(media_hashed_ids, name, allow_downloads: null)` validates 1–25 nonempty media IDs and a nonblank name. Media must already belong to the account and may come from different folders; membership/authorization remain remote checks. Omitted downloads leave the server default; explicit false is sent as JSON false. Creation returns 201 and a typed `Sharing\ReviewBundle`; listing and deletion return 200 and the same record shape (a list for listing). The schema does not require bundle response fields, so missing fields remain identifiable through `presentFields`.

`ListReviewBundlesRequest` supports `hashed_ids`, `name` (case-insensitive substring), `media_hashed_id`, `folder_hashed_id`, `sort_by` (`id`, `name`, `created`, `updated`), `sort_direction` (0 descending / 1 ascending), `page`, `per_page` and `Cursor`. Do not mix page with cursor, or reuse a cursor after changing the ordering.

`DataReviewBundlesService::paginateReviewBundles($request, maxPages: 1000)` returns a lazy paginator: `page()` fetches one page, `pages()` iterates pages, and `items()` iterates records. Offset and Data-cursor traversal preserve all filters and ordering. Exhaustion and non-advancing cursors throw instead of silently truncating or looping. See [pagination](pagination.md).

`DeleteReviewBundleRequest(reviewBundleHashedId)` permanently removes the bundle and its review link, **not its media**. It cannot be undone. Implementation and offline fixtures do not authorize destructive live calls.

## Share links

`ResolveShareLinkRequest(identifier)` accepts a share link's hashed ID or custom slug—the segment after `/s/`, not a whole URL. Historical slugs continue to resolve. Resolve/show/update return `Sharing\ShareLink` with required `id`, `media_id`, `url`, nullable `slug`, `visibility`, `created_at`, and `updated_at`.

`ShowShareLinkRequest(mediaId)` is a **state-changing GET**: first access creates a link with visibility derived from the parent folder. Update also creates a missing link. Both are sent once; transport failures, ambiguous server/redirect responses, or undecodable successes expose `remoteOutcomeUncertain=true`. Reconcile before deciding whether to try again. The client never automatically retries or follows redirects.

`UpdateShareLinkRequest(mediaId, visibility)` requires one of:

- `unlocked`: anyone with the link;
- `account`: signed-in members of the media's account;
- `locked`: contacts with access to the media's folder;
- `domain_verified`: signed-in viewers with an email at an account-verified domain. The account must be enrolled in the domain validation gate; otherwise setting it returns 400.

No slug-update field is documented. `DeleteShareLinkRequest(mediaId)` revokes the URL; later show/update can create a fresh URL. Delete documents status 200 without a response schema; `DeleteShareLinkResult::data` is null and the library does not invent a JSON-body requirement.

## Authorization and evidence boundaries

Search/resource URL resolution require **Read all data**. Bundle listing and share-link resolve/show require **Read all folder and media data**. Bundle creation/deletion and share-link update/deletion require **Read, update & delete anything**. Delegated-contact tokens remain subject to the assigned contact's permissions.

Search rejects expiring tokens with authorizations (403 regardless of grants). For expiring `all:delegate_to_contact_permissions` tokens: bundle listing needs authorizations naming bundles; creation needs `share` on every media and does not authorize follow-up access to the new bundle; deletion needs `destroy` on that bundle. Share-link resolution needs any permission naming its media (other media are not found), show needs `share` on media, update/delete need `update` on media. Resource URL resolution has no additional expiring-token authorization promise in the retained source.

Failures use [shared sanitized exceptions](foundation.md#results-and-failures), including authentication, permission, rate-limit metadata, transport and decoding errors. Local checks do not establish entitlement or ownership. Plan conditions not documented by Wistia remain unspecified, not proof of free-plan availability. No operation is marked experimental by the retained sources; no live or paid-plan verification is claimed.

The [operation reference](reference/data.md#get_search) and [inventory](rewrite/operations.json) retain exact source URLs, wire contracts and restrictions. Contract tests use synthetic fixtures on controlled transports; live tests, credentials, purchases, account trials, releases and issue closure remain outside this delivery.

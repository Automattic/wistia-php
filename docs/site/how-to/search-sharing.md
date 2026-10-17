# Search content and share reviews

Search returns grouped account resources; review bundles and share links manage access through different interfaces. Use the right operation for the task rather than treating every URL as an access grant.

Start with a [configured client](../tutorials/first-api-call.md#create-the-client) and an appropriately scoped token.

## Find media

```php
use Automattic\Wistia\DataSearchService;
use Automattic\Wistia\Request\Data\SearchRequest;

$search = new DataSearchService($client);
$result = $search->search(new SearchRequest(
    q: 'walkthrough',
    resource_type: ['media'],
    tags: ['product'],
));
$matches = $result->data->data->medias;
```

The nested `data->data` is intentional. Results group folders, subfolders, media, channels, episodes and webinars separately. Transcript matches can contain sensitive text and timestamps.

Search has **no pagination**. Custom-metadata searches are capped, so a returned set is not proof of an exhaustive export. Narrow large searches with appropriate filters, such as creation dates.

## Filter custom metadata

Use typed `Sharing\MetadataFilter` values for exact/list/range/presence queries, and include `media` in `resource_type`. Metadata filtering or inclusion requires the account's custom-metadata feature. The client cannot validate keys against your account's field definitions.

Tag matching uses OR semantics. Metadata boolean filters use literal `'true'`/`'false'` strings, not PHP booleans. See [SearchRequest](../../../src/Request/Data/SearchRequest.php) for all supported filters.

## Build a review bundle

This **creates a review link** for the selected existing media:

```php
use Automattic\Wistia\DataReviewBundlesService;
use Automattic\Wistia\Request\Data\CreateReviewBundleRequest;

$reviews = new DataReviewBundlesService($client);
$bundle = $reviews->createReviewBundle(new CreateReviewBundleRequest(
    media_hashed_ids: ['YOUR_MEDIA_HASHED_ID'],
    name: 'Product review',
    allow_downloads: false,
))->data;
```

A bundle accepts 1–25 media IDs from the account. Listing bundles supports bounded pagination. Deleting a bundle permanently removes its review link, **not the underlying media**, and cannot be undone.

## Choose share-link visibility

`DataShareLinksService` manages a media share link. `resolveShareLink()` accepts a hashed ID or custom slug—the segment after `/s/`, not an entire URL.

**`showShareLink()` is a state-changing GET:** it creates a link when one does not exist. Update can create one too. Do not treat either as a harmless existence check.

Visibility values are:

| Value | Who may view |
| --- | --- |
| `unlocked` | Anyone with the link |
| `account` | Signed-in members of the media's account |
| `locked` | Contacts with folder access |
| `domain_verified` | Signed-in viewers at an account-verified domain; requires enrollment in that feature |

Deleting a share link revokes its URL; a later show/update can create a new one. `DataResourceURLsService` instead resolves Wistia UI deep links for resources the caller may view. It does not make them public.

Review the [bundle](../../../src/DataReviewBundlesService.php), [share-link](../../../src/DataShareLinksService.php) and [resource-URL](../../../src/DataResourceURLsService.php) types before changing access. Even state-changing GET failures can have [uncertain outcomes](errors.md).

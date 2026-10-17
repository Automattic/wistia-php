# Manage media

List and inspect media with `DataMediaService`, then use explicit update, copy, tagging or trim operations for the changes you intend.

Before you start, create a [configured client](../tutorials/first-api-call.md#create-the-client). Reads require folder/media read access; writes require the relevant update permission. Feature restrictions can still be enforced remotely.

## Read a page

```php
use Automattic\Wistia\DataMediaService;
use Automattic\Wistia\Request\Data\ListMediaRequest;

$media = new DataMediaService($client);
$result = $media->listMedia(new ListMediaRequest(per_page: 25));
foreach ($result->data as $record) {
    $id = $record->hashed_id;
    $name = $record->name;
    // Use fields in your application; avoid indiscriminate metadata logging.
}
```

This makes one read-only call. Use [bounded pagination](pagination.md) for larger lists.

Results retain field names such as `hashed_id`, `embedCode` and `file_size`. Optional fields can be absent; [presentFields](../concepts/client-model.md#missing-is-different-from-null) distinguishes absence from an explicit nullable value.

## Change a name or replace tags

The next call **changes the selected media**. Replace the ID with media you intend to edit:

```php
use Automattic\Wistia\Request\Data\UpdateMediaRequest;

$updated = $media->updateMedia(new UpdateMediaRequest(
    mediaHashedId: 'YOUR_MEDIA_HASHED_ID',
    name: 'Product walkthrough',
    tags: ['product', 'walkthrough'],
));
```

`tags` replaces the entire list. Send `[]` to remove all tags, or omit the argument to leave them unchanged. Use `DataTaggingsService::bulkTagMedia()` to add tags without replacing existing ones.

Optional null arguments on this request mean omission. An empty description is an explicit empty string. Custom metadata uses an object; a null value inside that object clears that particular metadata key, and requires the account's custom-metadata feature.

## Choose the right operation

| Task | Service / method | Important distinction |
| --- | --- | --- |
| Copy one media item | `DataMediaService::copyMedia` | Its optional `folder_id` is an integer, unlike bulk-copy's hashed string ID |
| Replace underlying media | `DataMediaService::swapMedia` | Preserves the original hashed ID/URLs and queues replacement; media types must match |
| Create a trimmed video | `DataTrimsService::createMediaFromTrims` | Video only; returns a job, not completed media |
| List or create tags | `DataTagsService` | Wistia normalizes stored tag names |
| List/restore recently deleted media | `DataDeletedMediaService` | Separate from archived-media restoration |

Use the [PHP media service reference](../../../src/DataMediaService.php) and related [service directory](../reference/services.md) for request and result types.

## Handle jobs and partial outcomes

Archive, move, swap, trims and several bulk operations return job information. Follow it with [finite status reads](jobs.md); a queued result is not completed processing.

Move can return HTTP 207 with optional `partial_errors`. Inspect that result variant even when a job was queued; the HTTP 200 variant does not have that property. Archive and move calls cap the media list at 100; move also has a separate endpoint throttle.

Deleting media moves it to Recently Deleted, subject to the account's restore window and eventual permanent purge. Archived-media restore is different and requires Archiving access. Check eligibility and the intended destination before running either restoration.

If a write fails with an [uncertain outcome](errors.md#treat-an-uncertain-write-differently), reconcile before submitting it again.

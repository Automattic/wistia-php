# Read more than one page

Use a service's `paginate…` companion when you need more records than one response contains. Start with a [configured client](../tutorials/first-api-call.md#create-the-client) and a token allowed to read the resource.

## Iterate media records

```php
use Automattic\Wistia\DataMediaService;
use Automattic\Wistia\Request\Data\ListMediaRequest;

$media = new DataMediaService($client);
$pages = $media->paginateMedia(
    new ListMediaRequest(per_page: 100),
    maxPages: 10,
);
foreach ($pages->items() as $record) {
    // Process or store the record under your application's data policy.
    $id = $record->hashed_id;
}
```

The example reads pages until the endpoint ends or the ten-page bound is reached. Reaching the bound while more pages are needed throws `OverflowException`; it is not a successful complete export. Records already processed remain processed.

Use `pages()` when you need page-level handling, or `page()` to fetch an individual page. Creating a paginator or generator sends no HTTP; iteration does. Breaking the loop stops further reads.

## Choose offset or cursor mode

Media supports both. Use `page` for offset mode, or a `Cursor` for cursor mode, never both:

```php
use Automattic\Wistia\Request\Data\Cursor;

$cursorPages = $media->paginateMedia(new ListMediaRequest(
    per_page: 100,
    cursor: new Cursor(enabled: 1),
    sort_by: 'id',
), maxPages: 10);
```

Keep filters, sorting and direction unchanged when reusing a cursor. Pass the raw cursor, not a pre-encoded value. Media cursor sorting permits `id` or `created`; offset mode has different choices. Other resources have their own constraints.

## Use the resource's continuation rules

Offset and record-cursor lists normally stop on a short or empty page. A full last page may require a subsequent empty read.

Analytics conversions, webinar audience and Data webinar registrations instead use response pagination metadata. An empty page can still have a next page. Use their service companions rather than guessing continuation from the item count.

Generic traversal page sizes are bounded to 1–100; an endpoint can impose a narrower contract. Not every list has pagination: search and several aggregate Analytics calls do not provide a traversal helper.

## Recover deliberately

A request or decoding failure stops traversal without retry. Invalid, missing advancing or repeated cursors fail rather than loop. Persist application checkpoints if processing must survive restarts, and design for records changing remotely during a traversal.

See [services and operations](../reference/services.md) to find the relevant list/pagination methods, and [failure handling](errors.md) before retrying reads.

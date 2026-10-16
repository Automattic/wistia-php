# Webinars, collaborators and registrations

P11 implements ten modern Data operations through `DataWebinarsService`, `DataWebinarCollaboratorsService` and `DataWebinarRegistrationsService`. Construct services around a configured [Client](foundation.md). Requests live in `Request\Data`, results in `Result\Data`, and nested records and update inputs in `Webinars`. The [Data reference](reference/data.md#webinars-collaborators-and-registrations) maps every operation to its source and offline contract test.

## Read and traverse webinars

```php
use Automattic\Wistia\DataWebinarsService;
use Automattic\Wistia\Request\Data\{ListWebinarsRequest, ShowWebinarRequest, Cursor};

$webinars = new DataWebinarsService($client);
$webinar = $webinars->showWebinar(new ShowWebinarRequest('webinar-id'))->data;
$pages = $webinars->paginateWebinars(new ListWebinarsRequest(
    per_page: 20, cursor: new Cursor(enabled: 1), sort_by: 'scheduled_for',
    sort_direction: 0, started: 'false',
), maxPages: 10);
foreach ($pages->items() as $event) {
    // Typed fields include title, scheduled_for, time_zone and lifecycle_status.
}
```

Webinar and collaborator lists support individual pages and lazy `paginate…` companions. Use offset `page` or nested `Cursor`, never both. Webinar filters include repeated `hashed_ids[]` and `started: 'true'`/`'false'`. Cursor sorting permits `id` or `scheduled_for` for webinars, only `id` for collaborators. Offset webinar sorting also supports `title`, `created` and `updated`; collaborator offset sorting supports `created` and `updated`. Preserve the original sorting when reusing a cursor.

Traversal exposes `page()`, `pages()` and `items()`, preserves filters and ordering, and enforces a finite `maxPages`. Offset/record-cursor lists finish on an empty or short page. Repeating or missing continuation cursors fail rather than loop. Shared traversal supports page sizes 1–100; individual webinar/collaborator requests require positive sizes and leave undocumented upper limits to Wistia. See [pagination](pagination.md).

## Create and update

```php
use Automattic\Wistia\Request\Data\{CreateWebinarRequest, UpdateWebinarRequest};
use Automattic\Wistia\Webinars\WebinarUpdate;

$create = new CreateWebinarRequest(
    title: 'Workshop', scheduled_for: '2026-11-01T15:00:00Z',
    event_duration: 60, time_zone: 'America/New_York', folder_id: 'folder-id',
);
$update = new UpdateWebinarRequest('webinar-id', new WebinarUpdate(
    description: '', scheduled_for: '2026-11-02T15:00:00+00:00',
));
// Construction sends no HTTP. Submit only when the intended write is authorized.
```

Creation sends top-level JSON fields and requires `title`, `scheduled_for`, `event_duration` and `time_zone`. Updates use a typed `WebinarUpdate` nested under the wire key `webinar`. Optional PHP null means omission, not a JSON-null reset; the source documents no nullable write fields. Empty descriptions remain empty strings. An empty update sends `{}`, or `{"webinar":{}}` when an empty update object is supplied, leaving remote no-op/validation behavior to Wistia.

Schedules must be valid ISO 8601 UTC timestamps ending in `Z` or `+00:00`; fractional seconds are supported. `time_zone` is a separate IANA zone identifier, and `event_duration` must be at least 15 minutes. Validation checks syntax and documented limits without inspecting remote state or requiring a future date. `folder_id` defaults to the account's webinar folder on create; it can only be changed before a webinar starts. Wistia enforces that state-dependent restriction. `lifecycle_status` is read-only and has no write input.

Webinar results include links for audiences, hosts and panelists, nullable schedule/description/thumbnail fields, a typed folder reference and optional record cursor. Lifecycle values are `pending`, `ready`, `starting`, `started`, `ended`, `vod_ready` and `failed`. `presentFields` distinguishes absent response fields from explicit null. Creation returns HTTP 201; show, update and delete return a webinar record with HTTP 200, not an empty delete response.

## Collaborators and registrations

Collaborators are producers: `createWebinarCollaborator(new CreateWebinarCollaboratorRequest('webinar-id', 'producer@example.test'))` invites by email and can create a contact. Viewers cannot be webinar collaborators. No role input is sent; the returned role is `admin`, with a typed `share` contact/contact-group record. Listing returns only collaborators visible to the token: owners, managers and producers see all, everyone else sees an empty list. Delete takes the webinar hashed ID and a positive numeric collaborator ID, and returns the removed collaborator record.

```php
use Automattic\Wistia\DataWebinarRegistrationsService;
use Automattic\Wistia\Request\Data\{ListWebinarRegistrationsRequest, CreateWebinarRegistrationRequest};

$registrations = new DataWebinarRegistrationsService($client);
$people = $registrations->paginateWebinarRegistrations(new ListWebinarRegistrationsRequest(
    webinarId: 'webinar-id', per_page: 100, attendance: 'attendees',
    restriction: 'allowed', emails: ['person@example.test'],
), maxPages: 10);
foreach ($people->items() as $person) {
    // Typed contact, attribution, engagement, chats, Q&A and poll fields.
}
$registration = new CreateWebinarRegistrationRequest(
    'webinar-id', 'person@example.test', 'First', 'Last',
);
// createWebinarRegistration($registration) returns data->visitor_key and
// data->personalized_event_url. Protect these values as registrant data.
```

Registration lists use a scalar `cursor`, not `Cursor` or `page`. `sort_direction: 1` advances via `has_next_page`/`end_cursor`; `0` traverses backwards via `has_previous_page`/`start_cursor`. All filters and direction survive continuation. `per_page` accepts 1–100. Attendance values are `all`, `attendees`, `non_attendees`; restriction values are `all`, `restricted`, `allowed`; emails use repeated `emails[]` query keys.

The individual-page result exposes `data->registrations` and `data->page_info`, not a bare array. Registration records include typed form fields, chat groups, Q&A groups/questions, poll responses and nullable integer histograms. Optional absent fields remain distinguishable through `presentFields`. Traversal requires complete pagination metadata and follows its flags, not the number of registrations returned. Creating a registration sends only the required `email`, `first_name` and `last_name` fields and returns HTTP 201 with a visitor key and personalized event URL. Email syntax is validated locally for creation, invitations and list filters.

## Permissions, safety and verification

All ten operations use Bearer authentication, `/modern` and API version `2026-09`. Reads require read-all-data access; writes require read/update/delete-anything access. Delegated-contact tokens act with their assigned contact's permissions. Resource-scoped expiring tokens with `all:delegate_to_contact_permissions` can list/show webinars and list registrations with any permission on the named webinar. Creating a webinar requires account `create-webinars`; the creating token does not automatically cover the new webinar for follow-up requests. Updating, collaborator management and attendee registration require webinar `update`; deleting requires `destroy`. A token with only `show` lists no collaborators. Exact source restrictions remain in the [reference](reference/data.md#webinars-collaborators-and-registrations).

Invitations grant access; registration creates registrant data; deletion removes webinars or producer access. **Destructive calls require separate approval.** Requests are sent once without automatic retries. On an ambiguous write failure, inspect `remoteOutcomeUncertain` and reconcile remote state before considering another attempt. Shared authentication, permission, rate-limit, API, transport and decode exceptions hide raw responses and credentials. Normal debug views hide result/input payloads; deliberately accessing or serializing fields still exposes application data.

Offline synthetic tests cover all ten endpoint contracts, nested records, schedule validation, partial-update encoding, both pagination formats, filtered forward/backward traversal, sanitized failures and single-attempt writes. Live API behavior, plan entitlement and paid-plan availability remain unverified; HTTP 403 feature/permission failures are exposed rather than bypassed. No purchases, trials, webinar Analytics or Upload workflows are added here.

For migration, use these explicit services and named requests instead of legacy magic calls, then read typed `data` and catch shared exceptions. The old PHP interface has no compatibility layer.

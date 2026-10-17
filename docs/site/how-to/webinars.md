# Manage webinars and registrations

Use Data webinar services to read or schedule events and manage producer access and registrations. Use [reporting](reporting.md) for Analytics, not these registration-write methods.

You need a [configured client](../tutorials/first-api-call.md#create-the-client), suitable token permissions and webinar feature access.

## Read an event

```php
use Automattic\Wistia\DataWebinarsService;
use Automattic\Wistia\Request\Data\ShowWebinarRequest;

$webinars = new DataWebinarsService($client);
$event = $webinars->showWebinar(
    new ShowWebinarRequest('YOUR_WEBINAR_ID'),
)->data;
$title = $event->title;
```

Webinar and collaborator lists have bounded pagination companions. Webinar cursor sorts accept `id` or `scheduled_for`; collaborator cursors accept only `id`. Keep the original order while traversing.

## Create a scheduled webinar

This **creates a webinar**. Choose the title, destination and intended time before submitting:

```php
use Automattic\Wistia\Request\Data\CreateWebinarRequest;

$created = $webinars->createWebinar(new CreateWebinarRequest(
    title: 'Product workshop',
    scheduled_for: '2027-01-01T15:00:00Z',
    event_duration: 60,
    time_zone: 'America/New_York',
));
```

`scheduled_for` is a UTC ISO 8601 timestamp ending in `Z` or `+00:00`. `time_zone` is a separate IANA zone. Duration must be at least 15 minutes. Local validation does not require a future date or predict remote availability.

Updates use a typed `Webinars\WebinarUpdate`. Null arguments mean omission, not a reset. Changing the destination folder is allowed remotely only before the event starts. `lifecycle_status` is read-only.

## Read registrations

Registrations use a scalar cursor and response metadata, not offset pages or the nested Data `Cursor` object:

```php
use Automattic\Wistia\DataWebinarRegistrationsService;
use Automattic\Wistia\Request\Data\ListWebinarRegistrationsRequest;

$registrations = new DataWebinarRegistrationsService($client);
foreach ($registrations->paginateWebinarRegistrations(
    new ListWebinarRegistrationsRequest(
        webinarId: 'YOUR_WEBINAR_ID',
        per_page: 100,
        attendance: 'attendees',
    ),
    maxPages: 10,
)->items() as $person) {
    // Handle registrant data privately; do not dump attendee records.
}
```

The single-page result contains `data->registrations` and `data->page_info`, not a bare list. The paginator handles continuation flags, including empty continuing pages. See [pagination](pagination.md).

## Invite producers or create registrations

Collaborators are producers; there is no viewer role input. Invitations can create a contact. Creating a registration returns a visitor key and personalized event URL: protect them as registrant data.

These operations write remote state. Deleting an event or collaborator removes the corresponding event/access. Token permissions differ by operation; a newly created webinar is not automatically included in a restricted token's resource grants.

Consult [DataWebinarsService](../../../src/DataWebinarsService.php), [registration methods](../../../src/DataWebinarRegistrationsService.php) and [producer methods](../../../src/DataWebinarCollaboratorsService.php). For failures, use the [shared recovery rules](errors.md) rather than automatically repeating invitations or registrations.

# Channels, collaborators and episodes

P10 implements 16 modern Data operations through `DataChannelsService`, `DataChannelCollaboratorsService` and `DataChannelEpisodesService`. Construct them around a configured [Client](foundation.md). Named requests live in `Request\Data`, results in `Result\Data`, and nested records and podcast inputs in `Channels`. The [Data reference](reference/data.md#channels-collaborators-and-episodes) maps each operation to its source and offline test.

## Read channels and episodes

```php
use Automattic\Wistia\{DataChannelsService, DataChannelEpisodesService};
use Automattic\Wistia\Request\Data\{ListChannelsRequest, ListChannelEpisodesByChannelRequest, Cursor};

$channels = new DataChannelsService($client);
$page = $channels->listChannels(new ListChannelsRequest(per_page: 20));
foreach ($page->data as $channel) {
    // Typed fields: hashed_id, media_count, podcast_settings, and others.
}

$episodes = new DataChannelEpisodesService($client);
$traversal = $episodes->paginateChannelEpisodesByChannel(new ListChannelEpisodesByChannelRequest(
    channelHashedId: 'channel-id', per_page: 20, cursor: new Cursor(enabled: 1),
    sort_by: 'created', sort_direction: 0, published: false, title: 'Introduction',
), maxPages: 10);
foreach ($traversal->items() as $episode) {
    // Application-owned processing; stopping iteration stops further reads.
}
```

Both episode-list endpoints are supported: `listChannelEpisodesByChannel` puts the channel identifier in `/channels/{channelHashedId}/channel_episodes`; `listChannelEpisodes` uses `/channel_episodes` and an optional `channel_id` query filter. Both preserve `media_id[]` (numeric IDs represented as strings, or hashed IDs), `hashed_ids[]`, `published`, `title`, sorting and pagination. Show-episode takes both channel and episode IDs; update/delete/publish/unpublish take only the episode hashed ID.

All four lists—channels, collaborators, nested episodes and account episodes—offer individual-page methods and lazy `paginate…` companions with bounded `page()`, `pages()` and `items()` access. Offset traversal starts at the supplied page; cursor traversal preserves before/after direction. Empty/short pages terminate traversal; repeating cursors fail rather than loop. Generic traversal supports page sizes 1–100; single-page requests require positive sizes and leave undocumented upper limits to Wistia. Do not combine `page` and `cursor`.

Cursor sorting supports `id`/`created` for channels and episodes, and only `id` for collaborators. Offset sorting preserves each endpoint's documented choices. Keep sorting unchanged when reusing a cursor. See [pagination](pagination.md) for page bounds and continuation behavior.

## Create and update

Channel inputs preserve `name`, `description`, `auto_publish_enabled`, `podcast_enabled`, `custom_url` and nested `podcast_settings`. Enabling auto-publishing and podcasting together is rejected locally when both flags are supplied as true. A partial update cannot inspect the current channel; Wistia checks combinations with existing remote settings.

`Channels\ChannelPodcastSettings` exposes the documented nullable fields: `copyright`, `author_name`, `explicit`, `owner_name`, `owner_email`, `episode_format`, `category1`, `category2`, `category3` and `language`. `Channels\EpisodePodcastSettings` exposes `episode_type`, `episode_number`, `season_number`, `explicit_content` and `hide_from_feed`. Documented enum values are validated, including podcast category/language values; optional nonnullable fields use PHP null for omission. Nullable inputs default to `Channels\Omitted::Value` so explicit null resets remain distinct from omission. False, zero and empty strings retain their wire values.

```php
use Automattic\Wistia\Channels\{ChannelPodcastSettings, EpisodePodcastSettings};
use Automattic\Wistia\Request\Data\{UpdateChannelRequest, CreateChannelEpisodeRequest};

$reset = new UpdateChannelRequest('channel-id', description: null,
    podcast_settings: new ChannelPodcastSettings(owner_email: null, explicit: false));
$scheduled = new CreateChannelEpisodeRequest('channel-id', media_id: 'media-id',
    publish_status: 'scheduled', publish_at: '2027-01-01T00:00:00Z',
    podcast_settings: new EpisodePodcastSettings(episode_type: 'full', episode_number: 0));
// Construction performs no HTTP. Submit these only when the intended write is authorized.
```

Episode creation uses `media_id`; updates instead expose `media_hashed_id` and `live_stream_event_hashed_id`, plus `episode_notes`. These are exact endpoint-specific names, not aliases. The source leaves create-body fields optional; omitting them preserves remote defaults/validation rather than inventing required fields. Creation returns HTTP **200**, while channel/collaborator creation returns **201**.

Results expose `data` and `status`; list `data` is a bare list of typed records. Channel results include podcast settings; collaborators include a typed `share` contact/contact-group record; episodes retain `published`, optional `publish_at`, required `media_hashed_id`, nullable `live_stream_event_hashed_id` and `title`, and podcast settings. `presentFields` distinguishes absent response properties from explicit null. Normal debug views hide payloads; deliberately reading or serializing their fields still discloses application data.

## Publishing and scheduling

Creating with `publish_status: 'scheduled'` requires `publish_at`; creation with any other or omitted status must omit `publish_at`. The timestamp must be a valid ISO8601 UTC value ending in `Z` (fractional seconds are supported). Other status values are `draft` and `published`.

`publishChannelEpisode(new PublishChannelEpisodeRequest('episode-id'))` sends an empty JSON object for immediate publication. Supplying `publish_at` schedules publication. `unPublishChannelEpisode(new UnPublishChannelEpisodeRequest('episode-id'))` sends no body. Update can set `publish_status` and `publish_at`; unlike creation, the update source does not document a required pairing, so partial updates retain remote validation. All supplied schedule timestamps receive the same UTC syntax check. The library does not decide whether a time is in the future or wait for scheduled publication.

Publishing and unpublishing change viewer visibility. Inviting a collaborator grants access and may create a contact if the email is new; roles are `admin` or `viewer`. Deleting a collaborator revokes access. Deleting channels/episodes changes remote state. **Do not execute destructive calls without separate approval.** Each call is sent once; an ambiguous write failure exposes `remoteOutcomeUncertain`. Reconcile remote state before considering another attempt.

## Permissions and verification limits

All methods use Bearer authentication, `/modern` and pinned API version `2026-09`. Channel/episode reads require read-all-folder/media access; collaborator lists require read-all-data access. Collaborator and episode writes require read/update/delete-anything access. Delegated-contact tokens act with their assigned contact's permissions.

Resource-scoped expiring tokens with `all:delegate_to_contact_permissions` can list/show channels or episodes with any permission on the named channel. Creating channels requires account `create-channels`; updating requires channel `update`; deleting requires `destroy`. Channel `update` also permits managing its collaborators/episodes. Collaborator lists are visibility-scoped: owners/managers see all, channel admins see all on their channels, and others see only roles granting themselves access. An expiring token with only channel `show` lists no collaborators; `update` permits listing them. Exact restrictions remain in the [Data reference](reference/data.md#channels-collaborators-and-episodes) and [inventory](rewrite/operations.json).

Synthetic offline tests cover all 16 contracts, nested types, omission/null/false/zero, roles, scheduling, visibility transitions, both list endpoints, filters/order, cursor/offset traversal, sanitized failures and single-attempt writes. Live API behavior, account entitlement and paid-plan availability remain unverified; absent plan conditions do not establish free-plan availability. P10 adds no purchases, trials, browser/player APIs or Upload workflows.

For migration, replace legacy channel magic calls with these explicit services and named requests, then read typed `data` or catch shared exceptions. There is no compatibility layer. Channel episode operations remain distinct from media operations even when they refer to the same underlying media.

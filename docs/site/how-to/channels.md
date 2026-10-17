# Manage channels and episodes

Use channel services to list channels, manage collaborators and create or publish episodes. Episode operations are distinct from media operations even when they refer to the same media.

Start with a [configured client](../tutorials/first-api-call.md#create-the-client) and the channel/media IDs your token may access.

## Read channels and their episodes

```php
use Automattic\Wistia\DataChannelsService;
use Automattic\Wistia\DataChannelEpisodesService;
use Automattic\Wistia\Request\Data\ListChannelsRequest;
use Automattic\Wistia\Request\Data\ListChannelEpisodesByChannelRequest;

$channels = new DataChannelsService($client);
$page = $channels->listChannels(new ListChannelsRequest(per_page: 25));

$episodes = new DataChannelEpisodesService($client);
$pages = $episodes->paginateChannelEpisodesByChannel(
    new ListChannelEpisodesByChannelRequest(
        channelHashedId: 'YOUR_CHANNEL_ID',
        per_page: 25,
        published: false,
    ),
    maxPages: 10,
);
foreach ($pages->items() as $episode) {
    $id = $episode->hashed_id;
}
```

These are read calls. Nested episode lists take the channel in the path; the account-level list uses an optional `channel_id` query filter. Show-episode requires channel and episode IDs, while update/delete/publish/unpublish use the episode ID.

Channels, collaborators and both episode lists support [bounded pagination](pagination.md). Cursor sorting accepts `id`/`created` for channels and episodes, and only `id` for collaborators.

## Schedule an episode

The following **creates a remotely scheduled episode**, changing future viewer visibility. Choose a suitable future timestamp in your application:

```php
use Automattic\Wistia\Request\Data\CreateChannelEpisodeRequest;

$created = $episodes->createChannelEpisode(new CreateChannelEpisodeRequest(
    'YOUR_CHANNEL_ID',
    media_id: 'YOUR_MEDIA_HASHED_ID',
    publish_status: 'scheduled',
    publish_at: '2027-01-01T12:00:00Z',
));
```

Creation with `scheduled` requires `publish_at`; other creation statuses must omit it. Timestamps must be valid UTC values ending in `Z`. The client checks syntax, not whether the chosen time is still in the future.

`publishChannelEpisode()` publishes immediately when no timestamp is supplied, or schedules when one is supplied. `unPublishChannelEpisode()` changes visibility again. The client does not wait for a scheduled publication.

## Update podcast settings

Use `Channels\ChannelPodcastSettings` or `EpisodePodcastSettings` for nested options. Nullable fields distinguish an omitted value from an explicit null reset.

Channel auto-publishing and podcasting cannot both be supplied as enabled. A partial update cannot inspect existing remote settings, so Wistia validates combinations with current state.

Episode creation uses `media_id`; updates expose `media_hashed_id` and `live_stream_event_hashed_id`. They are not interchangeable aliases.

## Manage collaborators and failures

Inviting a channel collaborator grants access and may create a contact. Roles are `admin` and `viewer`; deletion revokes access. Collaborator-list visibility depends on the caller's role, so an empty list need not mean no collaborators exist.

Use the [channel](../../../src/DataChannelsService.php), [episode](../../../src/DataChannelEpisodesService.php) and [collaborator](../../../src/DataChannelCollaboratorsService.php) references for exact inputs. Writes need appropriate channel permissions and can fail with an [uncertain outcome](errors.md); reconcile before replaying publication or invitations.

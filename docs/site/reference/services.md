# Services and supported operations

Choose a service by task, then follow its link for the generated method, request and result reference. Pass a configured `Client` to services; webhook verification/parsing are local helpers and do not use it.

The directory describes implemented services in the 2.0 development API, not a promise of complete Wistia API coverage. Permissions, account features and plan restrictions still apply. Use the **PHP API reference** sidebar entry to browse every generated class.

## Content management

| Service | Operations | Usage |
| --- | --- | --- |
| [DataMediaService](../../../src/DataMediaService.php) | List/show/update/delete/copy/swap, aggregated stats, URL import, archive/move/restore and bulk copy | [Media](../how-to/media.md) |
| [DataTagsService](../../../src/DataTagsService.php) | List/create/delete tags | [Media](../how-to/media.md) |
| [DataTaggingsService](../../../src/DataTaggingsService.php) | Add tags across media | [Media](../how-to/media.md) |
| [DataTrimsService](../../../src/DataTrimsService.php) | Create media from video trims | [Media](../how-to/media.md) |
| [DataDeletedMediaService](../../../src/DataDeletedMediaService.php) | List and restore recently deleted media | [Media](../how-to/media.md) |
| [DataCustomizationsService](../../../src/DataCustomizationsService.php) | Broad and concern-specific player settings | [Customizations](../how-to/customizations.md) |
| [DataCaptionsService](../../../src/DataCaptionsService.php) | Read/import/update/delete captions, find matches and version-guarded text edits | [Accessibility](../how-to/accessibility.md) |
| [DataSpeakersService](../../../src/DataSpeakersService.php) | Read speaker profiles | [Accessibility](../how-to/accessibility.md) |
| [DataLocalizationsService](../../../src/DataLocalizationsService.php) | Read/delete existing localizations | [Accessibility](../how-to/accessibility.md) |
| [DataExtendedAudioDescriptionsService](../../../src/DataExtendedAudioDescriptionsService.php) | Read/delete existing descriptions and read existing order status | [Accessibility](../how-to/accessibility.md) |
| [UploadService](../../../src/UploadService.php) | Upload/import files, thumbnail file/URL updates and supplied localization audio imports | [Uploads](../how-to/uploads.md) |

## Organization and publishing

| Service | Operations | Usage |
| --- | --- | --- |
| [DataFoldersService](../../../src/DataFoldersService.php) | List/show/create/update/delete/copy folders | [Folders](../how-to/folders.md) |
| [DataSubfoldersService](../../../src/DataSubfoldersService.php) | List/show/create/update/delete and bulk-delete subfolders | [Folders](../how-to/folders.md) |
| [DataFolderSharingsService](../../../src/DataFolderSharingsService.php) | List/show/create/update/delete folder sharings | [Folders](../how-to/folders.md) |
| [DataChannelsService](../../../src/DataChannelsService.php) | List/show/create/update/delete channels | [Channels](../how-to/channels.md) |
| [DataChannelCollaboratorsService](../../../src/DataChannelCollaboratorsService.php) | List/invite/remove collaborators | [Channels](../how-to/channels.md) |
| [DataChannelEpisodesService](../../../src/DataChannelEpisodesService.php) | List/show/create/update/delete/publish/unpublish episodes | [Channels](../how-to/channels.md) |
| [DataWebinarsService](../../../src/DataWebinarsService.php) | List/show/create/update/delete webinars | [Webinars](../how-to/webinars.md) |
| [DataWebinarCollaboratorsService](../../../src/DataWebinarCollaboratorsService.php) | List/invite/remove producers | [Webinars](../how-to/webinars.md) |
| [DataWebinarRegistrationsService](../../../src/DataWebinarRegistrationsService.php) | List/create registrations | [Webinars](../how-to/webinars.md) |
| [DataBrandsService](../../../src/DataBrandsService.php) | List/show/create/update/delete/apply brands | [Branding](../how-to/brands.md) |

## Account, access and workflows

| Service | Operations | Usage |
| --- | --- | --- |
| [DataAccountService](../../../src/DataAccountService.php) | Account/usage/credit/token reads, brand preload/color picker and desktop-prompt dismissal | [Account](../how-to/account.md), [branding](../how-to/brands.md) |
| [DataAllowedDomainsService](../../../src/DataAllowedDomainsService.php) | List/show/create/delete allowed domains | [Account](../how-to/account.md) |
| [DataContactsService](../../../src/DataContactsService.php) | Invite contacts | [Account](../how-to/account.md) |
| [DataExpiringAccessTokensService](../../../src/DataExpiringAccessTokensService.php) | Create experimental expiring tokens | [Account](../how-to/account.md) |
| [OAuthService](../../../src/OAuthService.php) | Authorization URL, explicit code exchange and refresh | [OAuth](../how-to/oauth.md) |
| [DataSearchService](../../../src/DataSearchService.php) | Grouped search and metadata filters | [Search and sharing](../how-to/search-sharing.md) |
| [DataResourceURLsService](../../../src/DataResourceURLsService.php) | Resolve Wistia UI deep links | [Search and sharing](../how-to/search-sharing.md) |
| [DataReviewBundlesService](../../../src/DataReviewBundlesService.php) | List/create/delete review bundles | [Search and sharing](../how-to/search-sharing.md) |
| [DataShareLinksService](../../../src/DataShareLinksService.php) | Resolve/show/update/delete share links | [Search and sharing](../how-to/search-sharing.md) |
| [DataBulkActionsService](../../../src/DataBulkActionsService.php) | Submit supported non-purchasing bulk actions/jobs | [Bulk actions](../how-to/bulk.md) |
| [DataBackgroundJobStatusService](../../../src/DataBackgroundJobStatusService.php) | Read background-job status | [Jobs](../how-to/jobs.md) |

[WebhookVerifier](../../../src/WebhookVerifier.php) and [WebhookParser](../../../src/WebhookParser.php) verify and parse inbound deliveries. They do not host HTTP routes or process events; follow the [webhook guide](../how-to/webhooks.md).

## Stats and Analytics

| Service | Operations | Usage |
| --- | --- | --- |
| [StatsAccountService](../../../src/StatsAccountService.php) | Current account and by-date Stats | [Reporting](../how-to/reporting.md) |
| [StatsMediaService](../../../src/StatsMediaService.php) | Media summary, by-date Stats and engagement | [Reporting](../how-to/reporting.md) |
| [StatsEventsService](../../../src/StatsEventsService.php) | List/show events | [Reporting](../how-to/reporting.md) |
| [StatsVisitorsService](../../../src/StatsVisitorsService.php) | List/show visitors | [Reporting](../how-to/reporting.md) |
| [StatsProjectsService](../../../src/StatsProjectsService.php) | Folder/project Stats | [Reporting](../how-to/reporting.md) |
| [AnalyticsAccountService](../../../src/AnalyticsAccountService.php) | Account aggregates/timeseries, ranking, embed domains and media lookup | [Reporting](../how-to/reporting.md) |
| [AnalyticsMediaService](../../../src/AnalyticsMediaService.php) | Media aggregates/timeseries, locations, traffic, conversions and languages | [Reporting](../how-to/reporting.md) |
| [AnalyticsWebinarService](../../../src/AnalyticsWebinarService.php) | Webinar aggregates, registration timeseries, traffic, audience and histograms | [Reporting](../how-to/reporting.md) |

## Coverage boundaries

S3 copy is not implemented. The client does not expose purchases of captions/localizations/audio descriptions, generated translation/dubbing, or account trials. Reading credits or an existing order is not a purchase operation.

Generated class documentation reflects the code; it does not establish live feature access. Wistia enforces resource permissions, plan availability and remote processing rules when a request runs.

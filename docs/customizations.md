# Media customizations

Use `DataCustomizationsService` with the shared `Client` for 24 operations: broad read/create/update/delete and read/update pairs for appearance, playback, thumbnail, accessibility, chapters, engagement, related media, sharing, lead capture and access. The [Data reference](reference/data.md) lists each method, source, permission and success status. These are server-side configuration operations, not browser player APIs or Upload-host thumbnail imports.

## Update selected settings

Construct services directly. Request/result names match each method; payload types live under `Automattic\Wistia\Customizations`. This example uses a synthetic transport and makes no network calls:

```php
<?php
require 'vendor/autoload.php';

use Automattic\Wistia\{Client, Configuration, DataCustomizationsService};
use Automattic\Wistia\Customizations\UpdatePlaybackCustomizationsInput;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Request\Data\UpdatePlaybackCustomizationsRequest;

$transport = new class implements Transport {
    public function send(TransportRequest $request): TransportResponse {
        if ($request->body !== '{"autoPlay":false,"volume":0.0,"time":null}') {
            throw new RuntimeException('Unexpected request body.');
        }
        return new TransportResponse(200, ['Content-Type' => ['application/json']],
            '{"autoPlay":"false","volume":"0"}');
    }
};
$service = new DataCustomizationsService(new Client(new Configuration('synthetic-token'), $transport));
$result = $service->updatePlaybackCustomizations(new UpdatePlaybackCustomizationsRequest(
    'synthetic-media', new UpdatePlaybackCustomizationsInput(autoPlay: false, volume: 0, time: null),
));
if ($result->data->autoPlay !== 'false' || $result->data->volume !== '0') {
    throw new RuntimeException('Unexpected customization result.');
}
```

For actual requests, configure a suitably scoped token through application-owned secret handling and use the default transport. Updating or deleting customizations changes remote settings; no live mutations were exercised for this delivery.

## Replacement, partial updates and resets

- `createCustomizations` (POST) **replaces** the video's explicitly set customizations; it returns 201. Build its `CreateCustomizationsInput` deliberately rather than using it as a patch. Null values are rejected for this replacement payload.
- `updateCustomizations` (PUT) and the concern-specific PUT methods **partially update** settings. An omitted constructor argument defaults to `Omitted::Value` and is absent from JSON. Explicit `null` clears that field; nested fields can also be cleared. False, zero, empty strings and empty lists are retained. An empty input serializes as `{}`, not `[]`. The server decides resulting defaults/inheritance.
- Lead capture is a provider-specific exception: its PUT requires `provider` (`wistia_form`, `hubspot`, `marketo`, or `pardot`). Other fields are optional but not nullable in its documented schema. Supply its `enabled` boolean and typed `settings` when needed; omitted settings stay absent.
- `deleteCustomizations` (DELETE) deletes the explicitly set customizations. The documented 200 has no response schema, so `DeleteCustomizationsResult` exposes only `status`; no empty/JSON response contract is invented.

Each request takes a nonempty raw `mediaId` encoded once as a path segment. Methods have no query parameters or pagination. JSON field names retain Wistia spelling: `autoPlay`, `playerColor`, `showTweetCount`, `password_protect_password`, and provider-specific keys are not globally renamed. PHP replaces hyphens in property names with underscores: `socialbar_v1` sends `socialbar-v1`, for example.

## Nested input and results

Inputs use explicit nested records, such as `UpdateSharingCustomizationsInputPlugin` and `UpdateSharingCustomizationsInputPluginShare`. Set `channels` to a list drawn from `embed`, `email`, `facebook`, `twitter`, `linkedIn`, `download`; `downloadType` is `original`, `sd_mp4`, `hd_mp4`, or `all_qualities`. `channels` replaces the entire channel list: send every channel you want to keep. To enable downloads, include `download` in that list **and** set `downloadType`; for example, `channels: ['email', 'download'], downloadType: 'original'` retains email sharing while enabling downloads. Related-media IDs are string lists. Chapter lists contain typed `...ChapterListItem` records; chapter times are strings. Engagement's post-roll `time` accepts a number or string (for example, `end`). Playback's `videoFoam` accepts a boolean or typed sizing record. The lead-capture `settings` record preserves provider keys such as `formId`, `portalId`, `allowSkip`, and `hashedId` without flattening them into unrelated plugins.

Results have `data` and `status`. Customization responses document many booleans and numbers as **strings**: `'false'` is not PHP `false`, and its PHP truthiness is not the intended setting. The decoder preserves these strings rather than coercing them. Nested records and lists remain typed. `presentFields` identifies fields actually returned; missing optional fields become null, while undocumented wire nulls are rejected. Source-extensible objects retain extra properties in `additionalFields`; closed objects reject unknown fields. Open-ended objects, such as arbitrary plugin configuration, remain `stdClass` values.

Image CTAs support optional `altText` in the broad create/update and engagement post-roll input records. Omit it to leave it absent; send null to clear it in partial updates (replacement rejects null). The first-party narrative mentions this field but omits it from its closed schema. The maintainer approved this string-field interpretation for #15; the [inventory's contract resolution](rewrite/operations.json) preserves the source discrepancy and approval rather than claiming a corrected upstream schema. Returned `altText` is available in the post-roll record's `additionalFields`; the broad closed decoder allows this approved string field specifically, while continuing to reject unrelated unknown fields.

Playback/end, preload, resume, autoplay, fit-strategy and CTA-type options are checked against the documented choices before sending. Gradient entries must be `[hex color, finite numeric stop]` pairs; empty outer lists and explicit-null clearing remain distinct, and no undocumented stop range is imposed.

Use the broad read to fetch explicitly defined customizations, not an assertion that it includes every effective default. Passwords, lead forms and plugin data can be sensitive. Debug views are empty and exceptions sanitized; explicitly reading or serializing payloads/results remains the application's responsibility. Do not log them indiscriminately.

## Restrictions, failures and evidence

Read methods require **Read all folder and media data**; writes require **Read, update & delete anything**. Delegated-contact authorization and media-scoped expiring tokens are supported as described in each [operation's reference](reference/data.md); reads accept any media permission while writes require `edit`. Provider configuration, feature/plan availability and account entitlement remain remote validation. No new free-plan or paid-plan access claim is made by these methods.

All operations use Bearer authentication, `/modern`, and the pinned `2026-09` Data version. Shared authentication, permission, rate-limit, API, transport and decoding exceptions apply. Rate-limit information remains accessible. Requests are attempted once; ambiguous write transport failures, server errors or invalid successful write responses mark the remote outcome uncertain. Reconcile before submitting another mutation.

Contract tests use synthetic fixtures on controlled transports; they do not verify live defaults, plan access, provider integration, passwords or server-side reset behavior. Live Wistia behavior remains unverified. These explicit services and typed inputs/results replace legacy magic calls and raw results; there is no compatibility layer. Complete cross-library migration documentation remains a separate rewrite task.

# Customize a player

Use `DataCustomizationsService` to read and change server-side player settings. This is not a browser-player API or a thumbnail file upload.

Start with a [configured client](../tutorials/first-api-call.md#create-the-client), a media ID and permission to read or update that media.

## Read before changing

```php
use Automattic\Wistia\DataCustomizationsService;
use Automattic\Wistia\Request\Data\ShowCustomizationsRequest;

$customizations = new DataCustomizationsService($client);
$current = $customizations->showCustomizations(
    new ShowCustomizationsRequest('YOUR_MEDIA_HASHED_ID'),
);
```

The broad read returns explicitly defined customizations, not a promise that every effective default is present.

## Update only the settings you intend

The next call **changes playback settings**:

```php
use Automattic\Wistia\Customizations\UpdatePlaybackCustomizationsInput;
use Automattic\Wistia\Request\Data\UpdatePlaybackCustomizationsRequest;

$result = $customizations->updatePlaybackCustomizations(
    new UpdatePlaybackCustomizationsRequest(
        'YOUR_MEDIA_HASHED_ID',
        new UpdatePlaybackCustomizationsInput(autoPlay: false, volume: 0),
    ),
);
```

Arguments left out of a partial-update input stay absent. Explicit null clears a setting where supported; false and zero are sent rather than omitted. Wistia decides the resulting defaults and inheritance. See [update semantics](../concepts/updates.md).

## Choose patch or replacement

- `updateCustomizations()` and concern-specific updates patch selected settings.
- `createCustomizations()` **replaces** explicitly set customizations. Build the complete intended replacement; it rejects null values.
- `deleteCustomizations()` removes explicit customizations. Its result is status-only.

Appearance, playback, thumbnails, accessibility, chapters, engagement, related media, sharing, lead capture and access have separate read/update methods. [DataCustomizationsService](../../../src/DataCustomizationsService.php) links every typed input and result.

Lead capture differs from most partial updates: its provider is required and its documented optional fields are not nullable. Use the matching provider settings type.

## Retain the whole intended list

List settings can be replacements even inside a patch. For sharing channels, send every channel you want to keep. Enabling downloads requires both `download` in the channel list and a `downloadType`; adding it without retaining `email`, for example, removes email sharing from that list.

Nested inputs remain typed; do not flatten provider or plugin settings into a generic map.

## Read values without accidental coercion

Customization responses preserve documented boolean/number **strings**. Compare `'false'` explicitly: PHP treats that string as truthy. Use `presentFields` to distinguish optional absence from returned values.

Passwords, forms and plugin data can be sensitive. Protect deliberately read properties, not just object dumps. Feature/plan access is enforced remotely; a token alone does not establish it. [Reconcile uncertain writes](errors.md) before resubmission.

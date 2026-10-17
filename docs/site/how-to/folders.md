# Work with folders and sharing

Use folders and subfolders to organize media, and folder sharing to grant or change access. These are separate services with different result shapes and deletion effects.

Start with a [configured client](../tutorials/first-api-call.md#create-the-client). Listing requires suitable read permission; creation, changes and sharing require the corresponding write permission.

## List folders

```php
use Automattic\Wistia\DataFoldersService;
use Automattic\Wistia\Request\Data\ListFoldersRequest;

$folders = new DataFoldersService($client);
foreach ($folders->paginateFolders(
    new ListFoldersRequest(per_page: 25),
    maxPages: 10,
)->items() as $folder) {
    $id = $folder->hashed_id;
    $name = $folder->name;
}
```

This traverses lazily with a finite bound. Folder, sharing and subfolder lists each have their own `paginate…` companion. Do not mix offset `page` with a `Cursor`; keep sorting unchanged. See [pagination](pagination.md).

## Update a subfolder description

This **changes an existing subfolder**:

```php
use Automattic\Wistia\DataSubfoldersService;
use Automattic\Wistia\Request\Data\UpdateSubfolderRequest;

$subfolders = new DataSubfoldersService($client);
$updated = $subfolders->updateSubfolder(new UpdateSubfolderRequest(
    'YOUR_FOLDER_ID',
    'YOUR_SUBFOLDER_ID',
    description: 'Approved product walkthroughs',
));
```

Omit `description` to leave it unchanged, send null for an explicit nullable update, or an empty string to set an empty description. Names and descriptions have 255/1000-character limits respectively.

Folder request fields can use names such as `adminEmail` and `anonymousCanDownload`, while results use fields such as `anonymous_can_download`. Use the specific request/result types instead of normalizing names yourself.

## Grant sharing deliberately

`DataFolderSharingsService` uses nested `Folders\CreateSharing` and `UpdateSharing` inputs. Flags preserve explicit false. Creating a sharing grants access and sends an email notification.

The deprecated `sendEmailNotification` string flag does **not** suppress notifications, even when set to `"0"`. Do not use it as a silent-invitation mechanism. Revoking a sharing removes access.

Show-sharing takes an integer sharing ID, while update/delete use strings. The [sharing service reference](../../../src/DataFolderSharingsService.php) lists the exact request types.

## Know the deletion consequences

| Operation | Effect |
| --- | --- |
| Delete a folder | Removes its contents and loses associated stats |
| Delete one subfolder | Moves its media to the parent folder root, then soft-deletes the subfolder |
| Bulk-delete subfolders | Soft-deletes their media too |
| Delete a sharing | Revokes access |

Do not treat these as interchangeable cleanup methods. Restoration depends on the account's trash window and purge state.

## Follow asynchronous results

Folder copy returns a bare job record in `$result->data`. Bulk subfolder deletion returns a nested `$result->data->background_job_status`. Use the actual returned identifier with [bounded job polling](jobs.md).

Granted Access folder-level tokens cannot copy folders or bulk-delete subfolders. Other resource-scoped tokens have operation-specific permissions; embedded media can be visibility-filtered. A token's presence does not prove complete folder visibility.

Use [DataFoldersService](../../../src/DataFoldersService.php) and [DataSubfoldersService](../../../src/DataSubfoldersService.php) for method signatures. Reconcile [uncertain writes](errors.md) before repeating a change.

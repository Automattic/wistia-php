# Apply account branding

Use brands to share player/page appearance across media, folders and channels. A brand takes effect only when applied.

Start with a [configured client](../tutorials/first-api-call.md#create-the-client). Reading brands requires account read access; writes need the corresponding account/resource permissions. Additional brands and player logos can be plan-limited.

## List existing brands

```php
use Automattic\Wistia\DataBrandsService;
use Automattic\Wistia\Request\Data\ListBrandsRequest;

$brands = new DataBrandsService($client);
$page = $brands->listBrands(new ListBrandsRequest(per_page: 25));
foreach ($page->data as $brand) {
    $name = $brand->name;
}
```

Brand responses can be sparse. Do not assume that every optional field, including an ID, is present. Check a value before using it in a follow-up request.

For more pages, use `paginateBrands()` with a finite `maxPages`. Keep sorting consistent across cursor reads.

## Apply a brand without discarding overrides

This **changes a folder's effective branding**. Use known IDs your token may update:

```php
use Automattic\Wistia\Request\Data\ApplyBrandRequest;

$effective = $brands->applyBrand(new ApplyBrandRequest(
    'YOUR_BRAND_ID',
    resource_type: 'folder',
    resource_id: 'YOUR_FOLDER_ID',
    clear_overrides: false,
))->data;
```

Supported target types are `media`, `folder` and `channel`, not webinars. Applying normally clears brand-mapped appearance overrides; the explicit false above preserves them.

## Understand inheritance and resets

Media inherit from their folder, and folders inherit from the account default. Applying the default brand detaches the resource so it inherits again. The response is the **effective** brand, which may differ from the ID requested.

Use typed `Brands\BrandAttributes` to create/update attributes. Create requires a name; nullable updates can explicitly clear fields, while omitted arguments leave them unchanged. Changes to a brand propagate to its users.

The default brand cannot be deleted, and attempts to rename it are ignored remotely. Deleting another brand normally returns users to the account default; `sync_to_customizations: true` instead preserves its values as individual customizations. Review that propagation before deleting.

## Logos and account suggestions

A brand logo refers to an image already uploaded through the Wistia app; these methods do not upload arbitrary images. Wistia may ignore a player logo when the plan lacks custom-branding access, so a successful response alone does not prove it took effect.

`DataAccountService` can read brand preload suggestions and color-picker values. Updating preload changes account settings: an empty logo ID clears it, but an empty color string leaves the color unchanged. It is not a general color-reset operation.

Use [DataBrandsService](../../../src/DataBrandsService.php) and [DataAccountService](../../../src/DataAccountService.php) for the types. Reconcile [uncertain writes](errors.md) before repeating an application, update or deletion.

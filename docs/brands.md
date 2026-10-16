# Brands and account branding

P12 implements nine modern Data operations in `DataBrandsService` and `DataAccountService`. Construct either service around `Client`. All requests use Bearer authentication and pinned API version `2026-09`. The [operation reference](reference/data.md#get_brands) and [inventory](rewrite/operations.json) map methods, schemas, permissions and offline contracts. None of these operations is marked experimental in the checked sources; that is not a stability guarantee.

## Create, update and apply

```php
use Automattic\Wistia\DataBrandsService;
use Automattic\Wistia\Brands\{BrandAttributes, Gradient, GradientStop};
use Automattic\Wistia\Request\Data as R;

$brands = new DataBrandsService($client); // Configuration is shown in the README.
// These calls change remote state. Execute only with application authorization.
$brand = $brands->createBrand(new R\CreateBrandRequest(new BrandAttributes(
    name: 'Studio',
    primary_color: '#123',
    page_background_color: new Gradient([
        new GradientStop('#112233', 0), new GradientStop('#ffffff', 100),
    ]),
)))->data;
$brands->updateBrand(new R\UpdateBrandRequest($brand->id, new BrandAttributes(
    primary_color: null, // Explicitly clear; omitted fields remain unchanged.
    contrast_icons: 'unset', // Restore Wistia's automatic choice.
)));
$effective = $brands->applyBrand(new R\ApplyBrandRequest(
    $brand->id, resource_type: 'folder', resource_id: 'folder-id', clear_overrides: false,
))->data;
```

`BrandAttributes` exposes exact wire names, not a generic settings map. `name` is required on create; other fields are optional. On update, omitted fields are unchanged. Nullable colors, fonts, radius, control settings and logos accept explicit null. `name` is a string, not nullable in the schema. `Omitted::Value` distinguishes omission from clearing. Colors accept three/six hex digits with an optional `#`, or typed gradients; Wistia normalizes color strings. Control settings are `enabled`, `disabled` or `unset`. Numeric positions/sizes must be finite; undocumented bounds are not invented locally.

`LogoInput` accepts an existing account image's `https://embed-ssl.wistia.com/deliveries/…` URL, optional `LogoDimensionsInput` and size. Dimensions and size may be null. A new logo must first be uploaded through the Wistia app; these methods do not fetch arbitrary images or upload files. Wistia ignores `player_logo` on plans without custom branding, while `page_logo` still applies; a successful response does not prove that a player logo took effect. Account membership and entitlements remain remote checks.

## Inheritance, defaults and limits

A brand has no effect until applied. Media inherit from their folder; folders inherit from the account default. Applying to a folder styles items without their own brand. Supported apply targets are `media`, `folder`, and `channel`, not webinars.

Applying the default brand detaches the resource so it inherits again. The returned brand is the effective brand, which can differ from the requested brand. By default applying clears brand-mapped appearance overrides; send `clear_overrides: false` to preserve them. Changes to a brand propagate to its users. Wistia ignores attempts to rename the default brand, and the default brand cannot be deleted.

Deleting normally sends users back to the account default. `DeleteBrandRequest(sync_to_customizations: true)` first preserves the brand's values in each user's own customizations. This is a remote destructive operation; this change tests only its request/response contract, not live deletion or fallback behavior.

Every account has a default brand. Extra brands are plan-limited. The library does not guess entitlements or impose an invented brand-count limit. Remote 403/422 failures remain typed shared exceptions.

## Pages and typed responses

`listBrands(new R\ListBrandsRequest(...))` returns one typed page. `paginateBrands($request, maxPages: 1000)` is lazy and finite; call `items()`, `pages()` or `page()` on its paginator. Offset requests support positive `page`/`per_page`, `sort_by` (`created`, `id`, `updated`, `name`) and `sort_direction` (0 descending, 1 ascending). Cursor requests use `R\Cursor(enabled: 1)`, `before`, or `after`, and support only `created`, `id`, `updated` sorting. Mixing page and cursor is rejected; sorting and size survive traversal. Repeated/missing continuation cursors and exhausted bounds fail rather than looping.

Named result DTOs expose `data` and `status`. Brand responses are typed `Brands\Brand`, with nested typed logos and gradients; lists contain `Brand` objects. `presentFields` distinguishes omitted response fields from explicit null. Optional absence maps to null, while disallowed nulls, unexpected properties and malformed nested values fail decoding. Wistia's sparse brand schema does not require fields such as `id`; applications should account for absent values. Preload results require `has_brand_kits` and a typed `brandfetch_brand`; nullable domain/color/logo values represent no match. Brand-kit colors expose typed `colors` and `brand_gradients` lists (including empty lists).

## Account preload and color picker

```php
use Automattic\Wistia\DataAccountService;

$account = new DataAccountService($client);
$preload = $account->getBrandPreload(new R\GetBrandPreloadRequest())->data;
$colors = $account->getBrandKitColors(new R\GetBrandKitColorsRequest())->data;
// Remote write: empty color leaves it unchanged; empty logo ID clears the logo.
$account->updateBrandPreload(new R\UpdateBrandPreloadRequest(
    selected_player_color: '', selected_logo_hashed_id: '',
));
```

Preload discovers Brandfetch suggestions using the account domain and indicates whether any brand kits are already configured; it is not a request to create a brand. Updating preload can set the account's player color and set or clear its logo hashed ID. Player color accepts exactly six hex digits (optional `#`), unlike brand colors which also accept shorthand. Omitting the color or sending an empty string leaves the current color unchanged; there is no color-clear operation. An empty logo ID clears the logo; omission leaves it unchanged. A null constructor default means omission, not JSON null. Brand-kit colors are flattened/named for a picker, excluding null/unset colors, and gradients are returned separately.

## Permissions, errors and verification

Brand reads require Read all data; brand writes require All data. Delegated team-member tokens use their assigned contact's permissions. Restricted expiring tokens follow operation-specific brand/target permissions. Creating a brand requires account authorization `create-brands`; the created brand is not automatically covered for follow-up calls. Preload reads and the color picker allow any token scope, including delegated expiring tokens regardless of their named resources, for the token's own account. Updating preload requires an OAuth owner/manager (or Wistia admin), or an expiring token with account authorization `manage-brand-preload`; any token scope alone does not grant that role.

All calls use shared sanitized authentication, permission, API, rate-limit, transport and decoding exceptions. Requests are sent once; ambiguous write failures expose `remoteOutcomeUncertain`. Applications own reconciliation and safe retry decisions. Local validation does not simulate plan limits, inheritance or default-brand enforcement.

`tests/Contract/Data/*Brand*Test.php` uses synthetic responses for all nine operations, exact paths/encoding, auth/version headers, nested DTOs, null/omission/empty clearing, pagination and failures. These are offline contracts, not live verification of Brandfetch, paid plans, propagation, inheritance, default-brand behavior or image ownership. No live calls, credentials, purchases, account trials or destructive verification are included.

## Migration

Use explicit `DataBrandsService`/`DataAccountService` methods with the named `Request\Data` DTOs and typed results. Legacy magic dispatch, loose argument arrays and raw response objects have no compatibility layer. The account service currently implements only these three branding operations; unrelated account, expiring-token and search operations remain planned.

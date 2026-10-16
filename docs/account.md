# Account and access management

P13 / [#21](https://github.com/Automattic/wistia-php/issues/21) adds 11 modern Data operations. All use Bearer authentication and API version `2026-09`. Construct services around the shared `Client`; the [Data reference](reference/data.md#getaccountdetails) maps each method to its endpoint, restrictions and offline contract test. Preload/color-picker methods already delivered by P12 remain on `DataAccountService`; see [branding](brands.md).

## Read account, usage, credits and token metadata

```php
use Automattic\Wistia\DataAccountService;
use Automattic\Wistia\Request\Data as R;

$account = new DataAccountService($client);
$summary = $account->getCurrentAccount(new R\GetCurrentAccountRequest())->data;
$usage = $account->getAccountUsage(new R\GetAccountUsageRequest())->data;
$credits = $account->getCreditBalance(new R\GetCreditBalanceRequest())->data;
$tokenInfo = $account->getCurrentToken(new R\GetCurrentTokenRequest())->data;
```

These are actual HTTP calls when used with the default transport. Tests use injected synthetic transports, not credentials or a Wistia account.

Results expose typed `Account\*` records in `data` and HTTP `status`. Nested usage plan/limits/media/storage/users/bandwidth/links and OAuth application metadata are typed. Wire field names are preserved; `presentFields` distinguishes absent optional fields from explicit null. Unknown or mistyped closed-schema fields produce sanitized `DecodeException` failures.

- Account `id` is an integer; `video_limit` can be null.
- Usage `limits` is null for contacts without owner/manager billing visibility. Billing/usage links can also be null. `plan.tier` accepts legacy names, not just modern tiers. Storage and bandwidth values use bytes (1 GB = 1,000,000,000 bytes). Billable seat counts exclude viewers; `billable_count` aggregates agency parent/team users while `account_billable_count` covers only this account.
- Credit `balance` stays an exact nonnegative decimal **string**, never a float. It is a near-real-time hint that can lag one metered operation; a remote operation's 402 is authoritative. `next_grant_at` is a nullable expected billing boundary, not a guarantee of processing time or grant expiry. Reading a balance authorizes no purchase.
- Token metadata describes the token used for the call, including expiring tokens, and contains no credential value. `application` is null for non-OAuth tokens. `expires_at` may be absent or null.
- Account/usage/credit reads accept any token scope (including ordinary delegated contact authorization), but expiring tokens with authorizations receive 403. Token metadata can be read using an expiring token.

## Allowed domains and pagination

```php
use Automattic\Wistia\DataAllowedDomainsService;

$domains = new DataAllowedDomainsService($client);
$page = $domains->listAllowedDomains(new R\ListAllowedDomainsRequest(page: 1, per_page: 50));
foreach ($domains->paginateAllowedDomains(
    new R\ListAllowedDomainsRequest(cursor: new R\Cursor(enabled: 1), sort_by: 'domain'),
    maxPages: 100,
)->items() as $domain) {
    // $domain->domain and $domain->created_at are typed strings.
}
```

`showAllowedDomain` and `deleteAllowedDomain` take a raw domain string and encode it once as one path segment. `createAllowedDomain` sends JSON `domain` unchanged; Wistia strips `www` and can return an existing domain. All three return an `Account\AllowedDomain` record, including deletion. They do not toggle account domain restrictions: allowed domains affect embeds only when those restrictions are enabled remotely.

Reads require **Read all data**; writes require **Read, update & delete anything**, or applicable delegated-contact authorization. Expiring REST tokens need an `account` rule with `manage-allowed-domains`; without that permission the list can be empty rather than forbidden. Do not treat an empty list as evidence that restrictions are disabled.

Pagination preserves sorting and direction. Offset mode supports `id`, `domain`, `created`; cursor mode supports only `id` and `domain`. `sort_direction` is 0 descending / 1 ascending. Do not combine `page` and `cursor`. Traversal is lazy and finite and rejects repeated/missing advancing cursors; see [pagination](pagination.md). Stopping traversal cancels no remote work.

## Invitations and desktop prompt dismissal

`DataContactsService::inviteContacts(new R\InviteContactsRequest($contacts))` sends one JSON **string** under `contacts`, preserving commas, whitespace and newlines. It returns status 201 and `data->contacts`, a typed list of contact IDs (integers), emails and roles; existing contacts may be returned. The helper does not change roles or start a trial. The server controls invitation eligibility (including account role/business-domain restrictions); inspect usage `can_invite_teammates` as information, not a local authorization guarantee.

`dismissDesktopInstallPrompt(new R\DismissDesktopInstallPromptRequest())` sends a bodyless POST and returns `data->dismissed_at`. Wistia records the first dismissal timestamp and returns it on subsequent calls; the library does not cache or replay the request.

Both require **Read, update & delete anything**. Expiring REST tokens can invite with an `account` rule granting `manage-team`, but cannot dismiss the desktop prompt (403). No invitation or dismissal was performed during development.

## Experimental expiring access tokens

**Experimental: Wistia says this API is under development and can change at any time.**

```php
use Automattic\Wistia\DataExpiringAccessTokensService;
use Automattic\Wistia\Account\{ExpiringTokenOptions, TokenAuthorization};

// Executes a state-changing remote call when using the default transport.
$result = (new DataExpiringAccessTokensService($client))->createExpiringAccessToken(
    new R\CreateExpiringAccessTokenRequest(new ExpiringTokenOptions(
        expires_at: '2027-01-01T12:00:00Z',
        scopes: ['all:delegate_to_contact_permissions'],
        authorizations: [new TokenAuthorization('account', (string) $summary->id, ['manage-allowed-domains'])],
    )),
);
// Deliberately read $result->data->token into application-owned secure storage.
```

Omitted options leave Wistia's defaults intact: expiry two days from creation and scopes `["graphql:all"]`. Null omits an option; explicit empty scope/authorization lists are sent as empty lists. `expires_at` accepts an ISO 8601 timestamp with a timezone; local validation does not promise remote eligibility or expiry acceptance.

`TokenAuthorization` takes a supported resource `type`, string `id`, and a string-list `permissions`. Types are `media`, `folder`, `account`, `webinar`, `channel`, `review-bundle`, `brand`. Account rules use the account's numeric ID **as a string** and must name the token's own account; other rules use hashed IDs. Any permission implicitly allows viewing that object, while other permissions must be explicit. Rules differ by resource: consult the retained permission matrix in `DataCreateExpiringAccessTokenBodyApplicationJson` in the [inventory](rewrite/operations.json) and Wistia's [source reference](https://docs.wistia.com/reference/post_expiring-token.md). Ownership, permission validity and entitlements remain server decisions. Granting a permission does not implement or exercise purchases or generation.

Creating tokens requires **Read, update & delete anything**, or delegated team-member permissions. Creating one from another expiring token returns 501. Scope `graphql:all` supports GraphQL/embed use; `all:delegate_to_contact_permissions` supports REST subject to the rules and each endpoint's restrictions.

The returned credential is explicitly readable at `data->token`. Its result and nested record redact debug and JSON output and refuse PHP serialization. Applications own secure storage, expiry handling and safe downstream use; do not log the field or convert the object to an array. No implicit storage, refresh, retry or revocation is provided.

## Failures, migration and evidence

Invalid empty required values, pagination combinations/sorts and malformed typed token options fail locally before a request. Domain normalization, invitation addresses, permissions, account ownership and plan access remain remote validation. Shared failures distinguish authentication (401), permission (403), rate limiting (429 with metadata), API errors, transport and decoding errors. Bodies and credentials are not copied into exceptions. Every call is a single attempt; failed writes can have `remoteOutcomeUncertain=true`. Reconcile before any manual replay; see [foundation](foundation.md#results-and-failures).

Move legacy magic account calls to `DataAccountService` and explicit request DTOs, then read typed `data` and catch shared exceptions. No compatibility layer or raw-object/error-code interface is retained. Other resources remain separately scoped.

All 11 operations have synthetic offline contract coverage and behavior cases in `tests/Contract/Data/AccountBehaviorTest.php`. No live API, plan entitlement, invitation delivery, domain enforcement or token usability/expiry is verified. Paid-plan restrictions remain remote even when no restriction is specified by the pinned source. Account trials, purchases, generation, credentials, destructive/live checks and release remain excluded from this delivery.

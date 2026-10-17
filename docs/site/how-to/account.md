# Read account data and manage access

Read account details, usage and token metadata with `DataAccountService`. Use separate services for allowed domains, invitations and experimental expiring tokens.

Start with a [configured client](../tutorials/first-api-call.md#create-the-client). Billing visibility, feature access and resource permissions remain server decisions.

## Read account information

```php
use Automattic\Wistia\DataAccountService;
use Automattic\Wistia\Request\Data\GetCurrentAccountRequest;
use Automattic\Wistia\Request\Data\GetAccountUsageRequest;
use Automattic\Wistia\Request\Data\GetCreditBalanceRequest;
use Automattic\Wistia\Request\Data\GetCurrentTokenRequest;

$account = new DataAccountService($client);
$summary = $account->getCurrentAccount(new GetCurrentAccountRequest())->data;
$usage = $account->getAccountUsage(new GetAccountUsageRequest())->data;
$credits = $account->getCreditBalance(new GetCreditBalanceRequest())->data;
$tokenInfo = $account->getCurrentToken(new GetCurrentTokenRequest())->data;
```

These make real read requests. Token metadata describes the calling credential without returning its value.

Usage limits and billing links can be null when the caller lacks billing visibility. Storage/bandwidth use bytes, with decimal GB. Credit `balance` is an exact decimal **string**, not a float; it can lag a metered operation. Reading it does not authorize a purchase.

Account/usage/credit reads reject expiring tokens with authorizations; token metadata can be read with an expiring token.

## Manage allowed domains

`DataAllowedDomainsService` lists, shows, creates and deletes allowed domains. Pass raw domain names; Wistia handles normalization such as stripping `www`.

Creating/deleting an allowed domain changes configuration, but does not toggle domain restrictions themselves. The list affects embeds only when restrictions are enabled remotely. An empty list under a restricted token does not prove that restrictions are disabled.

Lists support [bounded pagination](pagination.md). Offset sorts include `id`, `domain`, `created`; cursor mode permits `id` or `domain`.

## Invite people deliberately

`DataContactsService::inviteContacts()` sends invitations and returns typed contacts. It takes a single contacts string, preserving comma/newline separators; it is not a role-changing or trial-starting API.

Invitations write remote state. Use the intended addresses and appropriate account permissions. Account usage's `can_invite_teammates` is information, not an authorization guarantee.

## Use expiring tokens cautiously

**The expiring-token API is experimental and can change.**

`DataExpiringAccessTokensService` creates a credential with typed `ExpiringTokenOptions` and `TokenAuthorization` rules. Restrict its scopes, resources and expiry to the intended use. Omitting scopes uses Wistia's default `graphql:all`, which is not the REST delegation scope.

Account authorization IDs are the account's numeric ID represented as a string; other resources use hashed IDs. Creating a resource does not automatically add it to that token's grants. Creating a token from another expiring token is unsupported remotely.

The returned `data->token` is a credential. Store only what you need in application-owned secret storage. Do not log, dump, cast or serialize the result to persist it. There is no automatic refresh or revocation helper.

## Look up the operation before changing access

Use [account methods](../../../src/DataAccountService.php), [allowed domains](../../../src/DataAllowedDomainsService.php), [invitations](../../../src/DataContactsService.php) and [expiring tokens](../../../src/DataExpiringAccessTokensService.php). Token scopes do not override contact/resource permissions or plan limits.

On any write failure, apply the [uncertain-outcome recovery rules](errors.md) before resubmission.

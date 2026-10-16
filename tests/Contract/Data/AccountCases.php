<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{DataAccountService, DataAllowedDomainsService, DataContactsService, DataExpiringAccessTokensService};
use Automattic\Wistia\Account\{ExpiringTokenOptions, TokenAuthorization};
use Automattic\Wistia\Request\Data as R;

final class AccountCases
{
    public static function account(): string
    {
        return '{"id":42,"name":"Studio","url":"https://studio.wistia.com","media_count":10,"video_limit":null,"folder_count":2,"channel_count":1}';
    }
    public static function usage(bool $visible = true): string
    {
        return json_encode([
            'plan'=>['tier'=>'legacy-custom','name'=>'Studio','is_standard'=>false,'is_trial'=>false,'trial_expires_at'=>null],
            'can_upload'=>true,'upload_blocked_reason'=>null,
            'limits'=>$visible ? ['primary_resource'=>'storage',
                'media'=>['count'=>10,'flexible_limit'=>null,'inflexible_limit'=>100],
                'storage'=>['used_bytes'=>12345678900,'flexible_limit_bytes'=>null,'inflexible_limit_bytes'=>20000000000],
                'users'=>['billable_count'=>8,'account_billable_count'=>3,'viewers_count'=>5,'flexible_limit'=>null,'inflexible_limit'=>20],
                'bandwidth'=>['used_bytes'=>123,'limit_bytes'=>null,'period'=>'annual','resets_at'=>null]] : null,
            'links'=>['plans_url'=>'https://studio.wistia.com/plans','usage_url'=>$visible?'https://studio.wistia.com/usage':null,'billing_url'=>null],
            'role'=>$visible?'owner':'viewer','verified_domain'=>true,'can_invite_teammates'=>$visible,
        ],JSON_THROW_ON_ERROR);
    }
    public static function domain(string $name = 'example.test', ?string $cursor = null): string
    {
        return json_encode(['domain'=>$name,'created_at'=>'2026-10-15T12:00:00Z','cursor'=>$cursor],JSON_THROW_ON_ERROR);
    }
    public static function token(): string
    {
        return '{"type":"oauth","application":{"name":"Studio app","scopes":["all:read"]},"scopes":["all:read"],"name":null,"expires_at":null}';
    }
    public static function all(): array
    {
        return [
            'GetCurrentAccount'=>[DataAccountService::class,'getCurrentAccount',new R\GetCurrentAccountRequest(),'GET','/account',null,200,self::account()],
            'GetAccountUsage'=>[DataAccountService::class,'getAccountUsage',new R\GetAccountUsageRequest(),'GET','/account_usage',null,200,self::usage()],
            'GetCreditBalance'=>[DataAccountService::class,'getCreditBalance',new R\GetCreditBalanceRequest(),'GET','/credits/balance',null,200,'{"balance":"12345678901234567890.000001","next_grant_at":null}'],
            'GetCurrentToken'=>[DataAccountService::class,'getCurrentToken',new R\GetCurrentTokenRequest(),'GET','/token',null,200,self::token()],
            'ListAllowedDomains'=>[DataAllowedDomainsService::class,'listAllowedDomains',new R\ListAllowedDomainsRequest(page:2,per_page:1,sort_by:'domain',sort_direction:0),'GET','/allowed_domains?page=2&per_page=1&sort_by=domain&sort_direction=0',null,200,'['.self::domain().']'],
            'CreateAllowedDomain'=>[DataAllowedDomainsService::class,'createAllowedDomain',new R\CreateAllowedDomainRequest('www.example.test'),'POST','/allowed_domains','{"domain":"www.example.test"}',200,self::domain()],
            'ShowAllowedDomain'=>[DataAllowedDomainsService::class,'showAllowedDomain',new R\ShowAllowedDomainRequest('a/b?c#d% &ü'),'GET','/allowed_domains/a%2Fb%3Fc%23d%25%20%26%C3%BC',null,200,self::domain()],
            'DeleteAllowedDomain'=>[DataAllowedDomainsService::class,'deleteAllowedDomain',new R\DeleteAllowedDomainRequest('a/b?c#d% &ü'),'DELETE','/allowed_domains/a%2Fb%3Fc%23d%25%20%26%C3%BC',null,200,self::domain()],
            'InviteContacts'=>[DataContactsService::class,'inviteContacts',new R\InviteContactsRequest("a@example.test, b@example.test\nc@example.test"),'POST','/contacts',json_encode(['contacts'=>"a@example.test, b@example.test\nc@example.test"]),201,'{"contacts":[{"id":1,"email":"a@example.test","role":"standard_user"}]}'],
            'DismissDesktopInstallPrompt'=>[DataContactsService::class,'dismissDesktopInstallPrompt',new R\DismissDesktopInstallPromptRequest(),'POST','/contact/dismiss_desktop_install_prompt',null,200,'{"dismissed_at":"2026-10-15T12:00:00Z"}'],
            'CreateExpiringAccessToken'=>[DataExpiringAccessTokensService::class,'createExpiringAccessToken',new R\CreateExpiringAccessTokenRequest(new ExpiringTokenOptions('2026-10-17T12:00:00+02:00',['all:delegate_to_contact_permissions'],[new TokenAuthorization('account','42',['manage-team'])])),'POST','/expiring_token','{"expiring_access_token":{"expires_at":"2026-10-17T12:00:00+02:00","scopes":["all:delegate_to_contact_permissions"],"authorizations":[{"type":"account","id":"42","permissions":["manage-team"]}]}}',200,'{"token":"private-issued-token"}'],
        ];
    }
}

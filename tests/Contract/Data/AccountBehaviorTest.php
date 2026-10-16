<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{Client, Configuration, DataAccountService, DataAllowedDomainsService};
use Automattic\Wistia\Account\{ExpiringTokenOptions, TokenAuthorization};
use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as D;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AccountBehaviorTest extends TestCase
{
    private function client(array $bodies, int $status = 200): array
    {
        $transport = new class($bodies, $status) implements Transport {
            public array $requests = [];
            public function __construct(private array $bodies, private int $status) {}
            public function send(TransportRequest $request): TransportResponse {
                $this->requests[] = $request;
                if ($this->bodies === []) { throw new \LogicException('Unexpected extra request.'); }
                return new TransportResponse($this->status,['Content-Type'=>['application/json']],array_shift($this->bodies));
            }
        };
        return [new Client(new Configuration('synthetic-token'),$transport),$transport];
    }
    #[DataProvider('invalidInputs')]
    public function testInvalidInputsDoNotSend(callable $build): void
    {
        [$client,$transport]=$this->client([]);
        try { $build($client); self::fail('Expected local rejection.'); }
        catch (\InvalidArgumentException|\TypeError $e) {
            self::assertCount(0,$transport->requests);
            self::assertStringNotContainsString('private', $e->getMessage());
        }
    }
    public static function invalidInputs(): array
    {
        return [
            [fn()=>new R\CreateAllowedDomainRequest(' ')], [fn()=>new R\ShowAllowedDomainRequest('')],
            [fn()=>new R\DeleteAllowedDomainRequest(' ')], [fn()=>new R\InviteContactsRequest(" \n")],
            [fn()=>new R\ListAllowedDomainsRequest(page:0)], [fn()=>new R\ListAllowedDomainsRequest(per_page:0)],
            [fn()=>new R\ListAllowedDomainsRequest(page:1,cursor:new R\Cursor(enabled:1))],
            [fn()=>new R\ListAllowedDomainsRequest(cursor:new R\Cursor(enabled:1),sort_by:'created')],
            [fn()=>new R\ListAllowedDomainsRequest(sort_by:'name')], [fn()=>new R\ListAllowedDomainsRequest(sort_direction:2)],
            [fn()=>new ExpiringTokenOptions(expires_at:'tomorrow private')],
            [fn()=>new ExpiringTokenOptions(expires_at:'2026-02-30T10:00:00Z')],
            [fn()=>new ExpiringTokenOptions(scopes:[1])], [fn()=>new ExpiringTokenOptions(scopes:['key'=>'all:read'])],
            [fn()=>new ExpiringTokenOptions(authorizations:[new \stdClass()])],
            [fn()=>new ExpiringTokenOptions(authorizations:['key'=>new TokenAuthorization('media','m',['show'])])],
            [fn()=>new TokenAuthorization('private','m',['show'])], [fn()=>new TokenAuthorization('media','',['show'])],
            [fn()=>new TokenAuthorization('media','m',[''])], [fn()=>new TokenAuthorization('media','m',[1])],
        ];
    }
    public function testTokenDefaultsAreOmittedAndExplicitEmptyListsPreserved(): void
    {
        self::assertSame('{}',(new R\CreateExpiringAccessTokenRequest())->body()->contents());
        self::assertSame('{"expiring_access_token":{}}',(new R\CreateExpiringAccessTokenRequest(new ExpiringTokenOptions()))->body()->contents());
        self::assertSame('{"expiring_access_token":{"scopes":[],"authorizations":[]}}',(new R\CreateExpiringAccessTokenRequest(new ExpiringTokenOptions(scopes:[],authorizations:[])))->body()->contents());
    }
    public function testTypedNestedAndNullableResults(): void
    {
        $account=D\GetCurrentAccountResult::fromWire(json_decode(AccountCases::account()),200)->data;
        self::assertSame(42,$account->id); self::assertNull($account->video_limit);
        $usage=D\GetAccountUsageResult::fromWire(json_decode(AccountCases::usage()),200)->data;
        self::assertSame('legacy-custom',$usage->plan->tier);
        self::assertSame(12345678900,$usage->limits->storage->used_bytes);
        self::assertSame(8,$usage->limits->users->billable_count);
        self::assertSame(3,$usage->limits->users->account_billable_count);
        self::assertSame(5,$usage->limits->users->viewers_count);
        self::assertSame('annual',$usage->limits->bandwidth->period);
        self::assertNull($usage->limits->storage->flexible_limit_bytes);
        $hidden=D\GetAccountUsageResult::fromWire(json_decode(AccountCases::usage(false)),200)->data;
        self::assertNull($hidden->limits); self::assertNull($hidden->links->usage_url);
        self::assertFalse($hidden->can_invite_teammates);
        $balance=D\GetCreditBalanceResult::fromWire(json_decode('{"balance":"12345678901234567890.000001","next_grant_at":null}'),200)->data;
        self::assertSame('12345678901234567890.000001',$balance->balance); self::assertNull($balance->next_grant_at);
        $token=D\GetCurrentTokenResult::fromWire(json_decode(AccountCases::token()),200)->data;
        self::assertSame('Studio app',$token->application->name); self::assertSame(['all:read'],$token->application->scopes);
        $absent=D\GetCurrentTokenResult::fromWire(json_decode('{"type":"permanent","application":null,"scopes":[],"name":"debug"}'),200)->data;
        self::assertNull($absent->expires_at); self::assertNotContains('expires_at',$absent->presentFields);
        self::assertContains('expires_at',$token->presentFields);
        $contacts=D\InviteContactsResult::fromWire(json_decode('{"contacts":[{"id":7,"email":"a@example.test","role":"viewer"}]}'),201)->data;
        self::assertSame(7,$contacts->contacts[0]->id); self::assertSame('viewer',$contacts->contacts[0]->role);
        self::assertSame([],D\InviteContactsResult::fromWire(json_decode('{"contacts":[]}'),201)->data->contacts);
    }
    public function testReturnedCredentialRequiresExplicitAccessAndCannotBeSerialized(): void
    {
        $token=D\CreateExpiringAccessTokenResult::fromWire(json_decode('{"token":"private-issued-token"}'),200);
        self::assertSame('private-issued-token',$token->data->token);
        foreach ([$token,$token->data] as $object) {
            self::assertStringNotContainsString('private-issued-token',print_r($object,true));
            ob_start(); var_dump($object); $dump=ob_get_clean();
            self::assertStringNotContainsString('private-issued-token',$dump);
            self::assertStringNotContainsString('private-issued-token',json_encode($object,JSON_THROW_ON_ERROR));
            try { serialize($object); self::fail('Serialization must fail.'); }
            catch (\LogicException $e) { self::assertStringNotContainsString('private-issued-token',$e->getMessage()); }
        }
    }
    #[DataProvider('invalidResponses')]
    public function testMalformedNestedResultsBecomeDecodeFailures(string $operation,string $body): void
    {
        [$class,$method,$request,,,,$status]=AccountCases::all()[$operation];
        [$client]=$this->client([$body],$status);
        $this->expectException(DecodeException::class); (new $class($client))->$method($request);
    }
    public static function invalidResponses(): array
    {
        $usage=json_decode(AccountCases::usage()); $usage->limits->storage->used_bytes='123';
        $limits=json_decode(AccountCases::usage()); $limits->limits->users->extra=1;
        return [
            ['GetCurrentAccount','{}'],['GetCurrentAccount',str_replace('"id":42','"id":"42"',AccountCases::account())],
            ['GetCurrentAccount',str_replace('"video_limit":null','"video_limit":false',AccountCases::account())],
            ['GetAccountUsage',json_encode($usage)],['GetAccountUsage',json_encode($limits)],
            ['GetCreditBalance','{"balance":1.2,"next_grant_at":null}'],
            ['GetCreditBalance','{"balance":"-1","next_grant_at":null}'],
            ['GetCreditBalance','{"balance":"1e3","next_grant_at":null}'],
            ['GetCreditBalance',"{\"balance\":\"1\\n\",\"next_grant_at\":null}"],
            ['GetCurrentToken','{"type":"unknown","application":null,"scopes":[],"name":null}'],
            ['GetCurrentToken','{"type":"oauth","application":{"name":"app","scopes":[1]},"scopes":[],"name":null}'],
            ['ListAllowedDomains','{}'],['ListAllowedDomains','[null]'],['ShowAllowedDomain','{"domain":"example.test"}'],
            ['ShowAllowedDomain','{"domain":"example.test","created_at":"date","cursor":1}'],
            ['InviteContacts','{"contacts":[{"id":"1","email":"private","role":"viewer"}]}'],
            ['DismissDesktopInstallPrompt','{"dismissed_at":null}'],
            ['CreateExpiringAccessToken','{}'],['CreateExpiringAccessToken','{"token":123}'],
            ['CreateExpiringAccessToken','{"token":"private","extra":true}'],
        ];
    }
    public function testLazyOffsetTraversalPreservesOrder(): void
    {
        [$client,$transport]=$this->client(['['.AccountCases::domain().']','[]']);
        $pages=(new DataAllowedDomainsService($client))->paginateAllowedDomains(new R\ListAllowedDomainsRequest(page:2,per_page:1,sort_by:'created',sort_direction:0));
        self::assertCount(0,$transport->requests);
        $items=iterator_to_array($pages->items()); self::assertSame('example.test',$items[0]->domain);
        self::assertSame('https://api.wistia.com/modern/allowed_domains?sort_by=created&sort_direction=0&per_page=1&page=3',$transport->requests[1]->url);
    }
    #[DataProvider('cursorDirections')]
    public function testCursorTraversalPreservesDomainSort(bool $before): void
    {
        [$client,$transport]=$this->client(['['.AccountCases::domain('example.test','next +/=').']','[]']);
        $cursor=$before ? new R\Cursor(before:'start') : new R\Cursor(enabled:1);
        $p=(new DataAllowedDomainsService($client))->paginateAllowedDomains(new R\ListAllowedDomainsRequest(cursor:$cursor,per_page:1,sort_by:'domain',sort_direction:0));
        self::assertCount(1,iterator_to_array($p->items()));
        self::assertStringContainsString('sort_by=domain&sort_direction=0',$transport->requests[1]->url);
        self::assertStringContainsString('cursor%5B'.($before?'before':'after').'%5D=next%20%2B%2F%3D',$transport->requests[1]->url);
    }
    public static function cursorDirections(): array { return [[true],[false]]; }
    public function testRepeatedCursorStopsTraversal(): void
    {
        [$client]=$this->client(['['.AccountCases::domain('a','same').']','['.AccountCases::domain('b','same').']']);
        $p=(new DataAllowedDomainsService($client))->paginateAllowedDomains(new R\ListAllowedDomainsRequest(cursor:new R\Cursor(enabled:1),per_page:1));
        $this->expectException(\UnexpectedValueException::class); iterator_to_array($p->items());
    }
    public function testFiniteBoundDoesNotImplyRemoteCancellation(): void
    {
        [$client,$transport]=$this->client(['['.AccountCases::domain().']']);
        $p=(new DataAllowedDomainsService($client))->paginateAllowedDomains(new R\ListAllowedDomainsRequest(per_page:1),maxPages:1);
        try { iterator_to_array($p->items()); self::fail('Expected exhaustion.'); }
        catch (\OverflowException) { self::assertCount(1,$transport->requests); }
    }
}

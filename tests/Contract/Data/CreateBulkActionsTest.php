<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{Client, Configuration, DataBulkActionsService, DataBackgroundJobStatusService};
use Automattic\Wistia\Bulk\{Action, Change, Job, Operation, ResourceType, Scope, ScopeType};
use Automattic\Wistia\Customizations as Customizations;
use Automattic\Wistia\Exception\{ApiException, AuthenticationException, DecodeException, PermissionException, RateLimitException, TransportException, WistiaException};
use Automattic\Wistia\Http\TransportResponse;
use Automattic\Wistia\Jobs\{BackgroundJobPoller, PollingOptions, PollingTermination};
use Automattic\Wistia\Request\Data\{CreateBulkActionsRequest, ShowBackgroundJobStatusRequest};
use Automattic\Wistia\Tests\Offline\{RecordingTransport, ExceptionAssertions};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CreateBulkActionsTest extends TestCase
{
    private const ACCEPTED = '{"message":"queued","background_job_status":{"id":42,"hashed_id":"job-hash","status":"queued"}}';
    private function transport(int $status = 202, string $body = self::ACCEPTED, string $type = 'application/json'): RecordingTransport
    {
        return new RecordingTransport(new TransportResponse($status, ['Content-Type'=>[$type], 'Retry-After'=>['17']], $body));
    }
    private function service(RecordingTransport $transport): DataBulkActionsService
    {
        return new DataBulkActionsService(new Client(new Configuration('synthetic-token'), $transport));
    }
    private function request(): CreateBulkActionsRequest
    {
        return new CreateBulkActionsRequest(actions: [new Action(Change::updateMedia(name:'New'), 'media')]);
    }
    public function testExactEndpointAndTypedJobHandoff(): void
    {
        $t = $this->transport();
        $result = $this->service($t)->createBulkActions($this->request());
        self::assertSame('queued', $result->message);
        self::assertSame('job-hash', $result->backgroundJobStatus->hashedId);
        self::assertSame(42, $result->backgroundJobStatus->id);
        self::assertSame('queued', $result->backgroundJobStatus->status->value);
        self::assertNull($result->backgroundJobStatus->object);
        self::assertCount(1, $t->requests);
        $r = $t->requests[0];
        self::assertSame('POST', $r->method);
        self::assertSame('https://api.wistia.com/modern/bulk', $r->url);
        self::assertSame('Bearer synthetic-token', $r->headers['Authorization']);
        self::assertSame('2026-09', $r->headers['X-Wistia-API-Version']);
        self::assertSame('application/json', $r->headers['Content-Type']);
        self::assertEquals(json_decode('{"actions":[{"operation":"update","resource_type":"media","id":"media","payload":{"name":"New"}}]}'), json_decode($r->body));
        self::assertStringNotContainsString('job-hash', print_r($result, true));

        // Submission acceptance is not action success: poll the returned ID with the shared finite helper.
        $t->response = new TransportResponse(200, ['Content-Type'=>['application/json']], '{"background_job_status":{"id":42,"hashed_id":"job-hash","status":"finished","object":{"type":"bulk_operation","summary":{"total":2,"succeeded":1,"failed":1},"results":[{"position":0,"operation":"create","resource_type":"folder","id":"created-folder","status":"succeeded"},{"position":1,"operation":"update","resource_type":"media","id":"media","status":"failed","error":"private per-action error"}]}}}');
        $poller = new BackgroundJobPoller(new DataBackgroundJobStatusService(new Client(new Configuration('synthetic-token'), $t)));
        $poll = $poller->poll(new ShowBackgroundJobStatusRequest($result->backgroundJobStatus->hashedId), new PollingOptions(1, 0));
        self::assertSame(PollingTermination::ReportedFailure, $poll->termination);
        self::assertSame(1, $poll->attempts);
        self::assertSame(1, $poll->last->backgroundJobStatus->object->summary->failed);
        self::assertSame('created-folder', $poll->last->backgroundJobStatus->object->results[0]->id);
        self::assertSame('private per-action error', $poll->last->backgroundJobStatus->object->results[1]->error);
        self::assertStringNotContainsString('private', print_r($poll, true));
        self::assertCount(2, $t->requests);
        self::assertSame('GET', $t->requests[1]->method);
    }
    #[DataProvider('payloads')]
    public function testTypedPayloadContracts(Change $change, string $operation, string $resource, string $expected): void
    {
        $request = new CreateBulkActionsRequest(actions:[new Action($change, $operation === 'create' ? null : 'target')]);
        $wire = json_decode($request->body()->contents());
        self::assertSame($operation, $wire->actions[0]->operation);
        self::assertSame($resource, $wire->actions[0]->resource_type);
        self::assertEquals(json_decode($expected), $wire->actions[0]->payload);
        self::assertStringNotContainsString('bulk-body-only', $request->body()->contents());
    }
    public static function payloads(): iterable
    {
        yield [Change::createFolder(name:'Folder', anonymousCanDownload:false), 'create', 'folder', '{"name":"Folder","anonymousCanDownload":false}'];
        yield [Change::updateFolder(public:false), 'update', 'folder', '{"public":false}'];
        yield [Change::createSubfolder('parent', 'Child', null), 'create', 'subfolder', '{"folder_id":"parent","name":"Child","description":null}'];
        yield [Change::updateSubfolder(description:null), 'update', 'subfolder', '{"description":null}'];
        yield [Change::createChannel(name:null, auto_publish_enabled:false), 'create', 'channel', '{"name":null,"auto_publish_enabled":false}'];
        yield [Change::updateChannel(custom_url:null), 'update', 'channel', '{"custom_url":null}'];
        yield [Change::createChannelEpisode('channel', media_id:'media', publish_status:'draft'), 'create', 'channel_episode', '{"channel_id":"channel","media_id":"media","publish_status":"draft"}'];
        yield [Change::updateChannelEpisode(title:null), 'update', 'channel_episode', '{"title":null}'];
        yield [Change::updateMedia(tags:[], custom_metadata:(object)['topic'=>null, 'flag'=>false, 'count'=>0]), 'update', 'media', '{"tags":[],"custom_metadata":{"topic":null,"flag":false,"count":0}}'];
        yield [Change::createCaptions('media', "1\n00:00:00,000 --> 00:00:01,000\nHi\n", 'en'), 'create', 'captions', '{"media_id":"media","caption_file":"1\n00:00:00,000 --> 00:00:01,000\nHi\n","language":"en"}'];
        yield [Change::updateCaptions('inline SRT'), 'update', 'captions', '{"caption_file":"inline SRT"}'];
        yield [Change::moveMedia('folder', 'subfolder'), 'move', 'media', '{"folder_id":"folder","subfolder_id":"subfolder"}'];
        yield [Change::moveMedia('folder'), 'move', 'media', '{"folder_id":"folder"}'];
        foreach (['Access','Accessibility','Appearance','Chapters','Engagement','LeadCapture','Playback','RelatedMedia','Sharing','Thumbnail'] as $concern) {
            $class = 'Automattic\\Wistia\\Customizations\\Update'.$concern.'CustomizationsInput';
            $method = 'update'.$concern;
            $wire = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $concern));
            $input = $concern === 'LeadCapture' ? new $class(provider:'wistia_form') : new $class();
            yield [Change::$method($input), 'update', 'customization_'.$wire, $concern === 'LeadCapture' ? '{"provider":"wistia_form"}' : '{}'];
        }
        yield [Change::updateAppearance(new Customizations\UpdateAppearanceCustomizationsInput(playerColor:null, roundedPlayer:0, branding:false,
            playerColorGradient:new Customizations\UpdateAppearanceCustomizationsInputPlayerColorGradient(on:true, colors:[['#fff',0],['#000',1]]))),
            'update','customization_appearance','{"playerColor":null,"roundedPlayer":0,"branding":false,"playerColorGradient":{"on":true,"colors":[["#fff",0],["#000",1]]}}'];
    }
    public function testDeleteForEachSupportedResourceOmitsPayload(): void
    {
        foreach ([ResourceType::Media, ResourceType::Folder, ResourceType::Subfolder, ResourceType::Channel, ResourceType::ChannelEpisode, ResourceType::Captions] as $type) {
            $wire = (new Action(Change::delete($type), 'record'))->jsonSerialize();
            self::assertSame('delete', $wire->operation); self::assertSame($type->value, $wire->resource_type);
            self::assertObjectNotHasProperty('payload', $wire);
        }
    }
    public function testScopedAndExplicitIdJobsKeepExactShape(): void
    {
        $wire = json_decode((new CreateBulkActionsRequest(job:new Job(Change::updateMedia(name:'Same'), new Scope(ScopeType::Folder, 'parent'))))->body()->contents());
        self::assertObjectNotHasProperty('actions', $wire);
        self::assertEquals((object)['type'=>'folder','id'=>'parent'], $wire->job->scope);
        self::assertObjectNotHasProperty('ids', $wire->job);
        $wire = json_decode((new CreateBulkActionsRequest(job:new Job(Change::moveMedia('dest'), ids:['a','b'])))->body()->contents());
        self::assertSame(['a','b'], $wire->job->ids); self::assertObjectNotHasProperty('scope', $wire->job);
        self::assertEquals((object)['folder_id'=>'dest'], $wire->job->payload);
        self::assertEquals((object)['type'=>'account'], (new Scope(ScopeType::Account))->jsonSerialize());
    }
    public function testAllParentResourceCombinations(): void
    {
        foreach (ResourceType::cases() as $resource) {
            if (str_starts_with($resource->value, 'customization_')) {
                $concern = str_replace(' ', '', ucwords(str_replace('_', ' ', substr($resource->value, 14))));
                $class = 'Automattic\\Wistia\\Customizations\\Update'.$concern.'CustomizationsInput';
                $input = $concern === 'LeadCapture' ? new $class(provider:'wistia_form') : new $class();
                $change = Change::{'update'.$concern}($input);
            } else { $change = Change::delete($resource); }
            $allowed = match ($resource) {
                ResourceType::Captions => ['account','media','folder','channel'],
                ResourceType::ChannelEpisode => ['account','channel','media'],
                ResourceType::Subfolder => ['account','folder'],
                ResourceType::Folder, ResourceType::Channel => ['account'],
                default => ['account','folder','subfolder','channel'],
            };
            foreach (ScopeType::cases() as $parent) {
                try {
                    $job = new Job($change, new Scope($parent, $parent === ScopeType::Account ? null : 'parent'));
                    self::assertContains($parent->value, $allowed);
                    self::assertSame($parent->value, $job->jsonSerialize()->scope->type->value);
                } catch (\InvalidArgumentException) { self::assertNotContains($parent->value, $allowed); }
            }
        }
    }
    #[DataProvider('invalidInputs')]
    public function testInvalidInputNeverSends(callable $make): void
    {
        $t = $this->transport();
        try { $request = $make(); if ($request instanceof CreateBulkActionsRequest) { $this->service($t)->createBulkActions($request); } self::fail('Expected local failure'); }
        catch (\InvalidArgumentException|\TypeError|\ValueError $e) {
            self::assertCount(0, $t->requests);
            self::assertStringNotContainsString('private', $e->getMessage());
        }
    }
    public static function invalidInputs(): iterable
    {
        $change = Change::updateMedia(name:'New'); $action = new Action($change, 'm');
        yield [fn()=>new CreateBulkActionsRequest()];
        yield [fn()=>new CreateBulkActionsRequest([], new Job($change, ids:['m']))];
        yield [fn()=>new CreateBulkActionsRequest([])];
        yield [fn()=>new CreateBulkActionsRequest([1=>$action])];
        yield [fn()=>new CreateBulkActionsRequest([(object)['operation'=>'purchase']])];
        yield [fn()=>new CreateBulkActionsRequest(array_fill(0,1001,$action))];
        yield [fn()=>new CreateBulkActionsRequest([new Action(Change::updateCaptions(str_repeat('x',2_000_000)),'track')])];
        yield [fn()=>new Job($change)]; yield [fn()=>new Job($change, new Scope(ScopeType::Account), ['m'])];
        yield [fn()=>new Job($change, ids:[])]; yield [fn()=>new Job($change, ids:array_fill(0,1001,'m'))];
        yield [fn()=>new Job($change, ids:['a'=> 'm'])]; yield [fn()=>new Job($change, ids:[1])];
        yield [fn()=>new Job($change, ids:[''])]; yield [fn()=>new Job(Change::createFolder(), ids:['m'])];
        yield [fn()=>new Scope(ScopeType::Account,'m')]; yield [fn()=>new Scope(ScopeType::Folder)]; yield [fn()=>new Scope(ScopeType::Folder,'')];
        yield [fn()=>new Action($change)]; yield [fn()=>new Action($change,'')]; yield [fn()=>new Action(Change::createFolder(),'unexpected')];
        yield [fn()=>Change::delete(ResourceType::CustomizationAppearance)];
        yield [fn()=>Operation::from('purchase')]; yield [fn()=>ResourceType::from('localization')];
        yield [fn()=>Change::createSubfolder('folder',str_repeat('x',256))];
        yield [fn()=>Change::createChannel(auto_publish_enabled:true, podcast_enabled:true)];
        yield [fn()=>Change::createChannelEpisode('channel', publish_status:'scheduled')];
        yield [fn()=>Change::updateChannelEpisode(publish_at:'not-a-date')];
        yield [fn()=>Change::updateMedia(tags:[1])];
        yield [fn()=>Change::createCaptions('', 'srt')]; yield [fn()=>Change::updateCaptions('')]; yield [fn()=>Change::moveMedia('')];
    }
    public function testLimitsUseEncodedBytesAndFreezeMutableInputs(): void
    {
        $metadata = (object)['key'=>'before'];
        $change = Change::updateMedia(custom_metadata:$metadata); $metadata->key='after';
        $change->jsonSerialize()->payload->custom_metadata->key='also after';
        $request = new CreateBulkActionsRequest([new Action($change, 'm')]);
        self::assertSame('before', json_decode($request->body()->contents())->actions[0]->payload->custom_metadata->key);
        $actions = array_fill(0,1000,new Action(Change::delete(ResourceType::Media),'m'));
        self::assertCount(1000,json_decode((new CreateBulkActionsRequest($actions))->body()->contents())->actions);
        self::assertCount(1000, (new Job(Change::delete(ResourceType::Media), ids:array_fill(0,1000,'m')))->ids);
        $base = strlen((new CreateBulkActionsRequest([new Action(Change::updateCaptions('x'),'m')]))->body()->contents()) - 1;
        $max = new CreateBulkActionsRequest([new Action(Change::updateCaptions(str_repeat('x',1_999_999-$base)),'m')]);
        self::assertSame(1_999_999, strlen($max->body()->contents()));
        try { new CreateBulkActionsRequest([new Action(Change::updateCaptions(str_repeat('x',2_000_000-$base)),'m')]); self::fail(); }
        catch (\InvalidArgumentException) { self::assertTrue(true); }
        // JSON escapes non-ASCII text; measure encoded bytes rather than PHP character counts.
        $this->expectException(\InvalidArgumentException::class);
        new CreateBulkActionsRequest([new Action(Change::updateCaptions(str_repeat('é',340_000)),'m')]);
    }
    #[DataProvider('failures')]
    public function testSanitizedSingleAttemptErrors(int $status, string $class): void
    {
        $t = $this->transport($status, 'private body synthetic-token');
        try { $this->service($t)->createBulkActions($this->request()); self::fail(); }
        catch (WistiaException $e) {
            self::assertInstanceOf($class,$e); self::assertSame($status,$e->status);
            self::assertSame('17',$e->rateLimit->retryAfter); self::assertCount(1,$t->requests);
            self::assertSame($status>=500 || in_array($status,[200,408,302],true),$e->remoteOutcomeUncertain);
            self::assertNull($e->getPrevious()); self::assertStringNotContainsString('private',$e->getMessage());
            ExceptionAssertions::assertSanitized($e, 'synthetic-token');
        }
    }
    public static function failures(): array
    {
        return [[400,ApiException::class],[401,AuthenticationException::class],[403,PermissionException::class],[413,ApiException::class],
            [429,RateLimitException::class],[500,ApiException::class],[408,ApiException::class],[302,ApiException::class],[200,DecodeException::class]];
    }
    public function testDisconnectIsUncertainAndNeverReplayed(): void
    {
        $t = $this->transport(); $t->failure = new \RuntimeException('private synthetic-token');
        try { $this->service($t)->createBulkActions($this->request()); self::fail(); }
        catch (TransportException $e) {
            self::assertTrue($e->remoteOutcomeUncertain); self::assertCount(1,$t->requests); self::assertNull($e->getPrevious());
            ExceptionAssertions::assertSanitized($e, 'private synthetic-token');
        }
    }
    #[DataProvider('malformedResponses')]
    public function testMalformedSuccessRemainsUncertain(string $body, string $type): void
    {
        $t = $this->transport(body:$body,type:$type);
        try { $this->service($t)->createBulkActions($this->request()); self::fail(); }
        catch (DecodeException $e) { self::assertTrue($e->remoteOutcomeUncertain); self::assertCount(1,$t->requests); }
    }
    public static function malformedResponses(): array
    {
        return [['{','application/json'],['null','application/json'],['[]','application/json'],[self::ACCEPTED,'text/html'],
            ['{"message":"ok"}','application/json'],
            [str_replace('"id":42','"id":"42"',self::ACCEPTED),'application/json'],
            [str_replace('"queued"}}','"unknown"}}',self::ACCEPTED),'application/json'],
            [str_replace('"status":"queued"','"status":"queued","object":{}',self::ACCEPTED),'application/json']];
    }
}

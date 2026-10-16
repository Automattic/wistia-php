<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\{Client, Configuration, DataMediaService, DataDeletedMediaService, DataTagsService, DataBackgroundJobStatusService};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Jobs\{BackgroundJobPoller, PollingOptions, PollingRuntime, PollingTermination};
use Automattic\Wistia\Tests\Contract\Data\MediaCases;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MediaLifecycleTest extends TestCase
{
    private function client(array $responses): array
    {
        $transport = new class($responses) implements Transport {
            public array $requests=[];
            public function __construct(private array $responses) {}
            public function send(TransportRequest $request): TransportResponse {
                $this->requests[]=$request;
                if ($this->responses===[]) { throw new \LogicException('Unexpected extra request.'); }
                [$status,$body]=array_shift($this->responses);
                return new TransportResponse($status,['Content-Type'=>['application/json']],$body);
            }
        };
        return [new Client(new Configuration('synthetic-token'),$transport),$transport];
    }
    public function testMediaExactQueryEncodingAndDeepTypes():void
    {
        [$client,$t]=$this->client([[200,MediaCases::all()['ListMedia'][7]]]);
        $data=(new DataMediaService($client))->listMedia(new R\ListMediaRequest(per_page:2,cursor:new R\Cursor(enabled:1),
            sort_by:'id',sort_direction:0,folder_id:'folder/ +',name:'a & b',description_format:'markdown',include:'speakers',type:'Video',hashed_ids:['a','b'],tags:['one','two +'],archived:false))->data;
        self::assertSame('https://api.wistia.com/modern/medias?per_page=2&cursor%5Benabled%5D=1&sort_by=id&sort_direction=0&folder_id=folder%2F%20%2B&name=a%20%26%20b&description_format=markdown&include=speakers&type=Video&hashed_ids%5B%5D=a&hashed_ids%5B%5D=b&tags%5B%5D=one&tags%5B%5D=two%20%2B&archived=false',$t->requests[0]->url);
        self::assertSame(7,$data[0]->id); self::assertSame('private embed',$data[0]->embedCode);
        self::assertSame(123,$data[0]->assets[0]->file_size); self::assertNull($data[0]->assets[0]->width);
        self::assertSame('ms',$data[0]->speakers[0]->media_speaker_id); self::assertSame('topic',$data[0]->tags[0]->name);
        self::assertNull($data[0]->subfolder); self::assertNull($data[0]->folder);
        self::assertContains('subfolder',$data[0]->presentFields);
    }
    #[DataProvider('lists')]
    public function testLazyCursorTraversalPreservesFiltersAndSort(string $service,string $method,object $input,string $base,string $record):void
    {
        [$client,$t]=$this->client([[200,'['.$record.']'],[200,'[]']]);
        $p=(new $service($client))->$method($input,maxPages:3);
        self::assertCount(0,$t->requests);
        $items=iterator_to_array($p->items());self::assertCount(1,$items);
        self::assertSame($base.'&per_page=1&cursor%5Benabled%5D=1',$t->requests[0]->url);
        self::assertSame($base.'&per_page=1&cursor%5Bafter%5D=opaque%20%2B%2F',$t->requests[1]->url);
        self::assertSame('opaque +/',$items[0]->cursor);
    }
    public static function lists():array
    {
        return [
            [DataMediaService::class,'paginateMedia',new R\ListMediaRequest(per_page:1,cursor:new R\Cursor(enabled:1),sort_by:'id',sort_direction:0,tags:['a','b'],archived:false),'https://api.wistia.com/modern/medias?sort_by=id&sort_direction=0&tags%5B%5D=a&tags%5B%5D=b&archived=false','{"folder":null,"cursor":"opaque +/"}'],
            [DataTagsService::class,'paginateTags',new R\ListTagsRequest(per_page:1,cursor:new R\Cursor(enabled:1),sort_by:'updated',sort_direction:0),'https://api.wistia.com/modern/tags?sort_by=updated&sort_direction=0','{"name":"tag","cursor":"opaque +/"}'],
            [DataDeletedMediaService::class,'paginateDeletedMedia',new R\ListDeletedMediaRequest(hashed_ids:['a','b'],sort_by:'deleted',sort_direction:0,per_page:1,cursor:new R\Cursor(enabled:1)),'https://api.wistia.com/modern/deleted_media?hashed_ids%5B%5D=a&hashed_ids%5B%5D=b&sort_by=deleted&sort_direction=0','{"hashed_id":"a","cursor":"opaque +/"}'],
        ];
    }
    public function testOffsetIndividualPageAndFinalPage():void
    {
        [$client,$t]=$this->client([[200,'[{"folder":null}]'],[200,'[]']]);
        $p=(new DataMediaService($client))->paginateMedia(new R\ListMediaRequest(page:2,per_page:2,sort_by:'name'));
        self::assertCount(1,$p->page()->items);self::assertStringEndsWith('sort_by=name&per_page=2&page=2',$t->requests[0]->url);
        self::assertCount(0,$p->page(4)->items);self::assertStringEndsWith('sort_by=name&per_page=2&page=4',$t->requests[1]->url);
    }
    public function testBackwardsCursorAndCycleProtection():void
    {
        [$client,$t]=$this->client([[200,'[{"folder":null,"cursor":"same"}]']]);
        $p=(new DataMediaService($client))->paginateMedia(new R\ListMediaRequest(per_page:1,cursor:new R\Cursor(before:'same'),sort_by:'created'));
        try { iterator_to_array($p->items());self::fail('Expected non-advancing cursor'); }
        catch (\UnexpectedValueException) { self::assertCount(1,$t->requests);self::assertStringEndsWith('sort_by=created&per_page=1&cursor%5Bbefore%5D=same',$t->requests[0]->url); }
    }
    public function testPaginationBoundStopsBeforeAnotherRead():void
    {
        [$client,$t]=$this->client([[200,'[{"name":"tag"}]']]);
        $this->expectException(\OverflowException::class);
        iterator_to_array((new DataTagsService($client))->paginateTags(new R\ListTagsRequest(per_page:1),maxPages:1)->items());
    }
    public function testArchiveReturnsJobAndOptionalPollingNeverReplaysWrite():void
    {
        [$client,$t]=$this->client([[200,MediaCases::JOB],
            [200,'{"background_job_status":{"id":42,"hashed_id":"job-hash","status":"started"}}'],
            [200,'{"background_job_status":{"id":42,"hashed_id":"job-hash","status":"finished"}}']]);
        $created=(new DataMediaService($client))->archiveMedia(new R\ArchiveMediaRequest(['one']))->data;
        self::assertSame('queued',$created->background_job_status->status);self::assertCount(1,$t->requests);
        $clock=new class implements PollingRuntime {
            public function now():float { return 0.0; }
            public function wait(float $seconds):void {}
        };
        $poll=(new BackgroundJobPoller(new DataBackgroundJobStatusService($client),$clock))->poll(
            new R\ShowBackgroundJobStatusRequest($created->background_job_status->hashed_id),new PollingOptions(2,0));
        self::assertSame(PollingTermination::Completed,$poll->termination);
        self::assertSame(['PUT','GET','GET'],array_column($t->requests,'method'));
    }
    public function testMove207PreservesPartialErrorsAnd200AlsoDecodes():void
    {
        $fixture=MediaCases::all()['MoveMedia'][7];
        [$client]=$this->client([[207,$fixture],[200,MediaCases::JOB]]);$s=new DataMediaService($client);
        $r=$s->moveMedia(new R\MoveMediaRequest(['one','two'],'folder'));
        self::assertSame(207,$r->status);self::assertSame('two',$r->data->partial_errors[0]->media_id);
        self::assertSame('private denied',$r->data->partial_errors[0]->error);
        // Documented status guard must work for both result variants without undefined-property warnings.
        $errors=static fn($result)=>$result->status===207 ? ($result->data->partial_errors ?? []) : [];
        self::assertSame('two',$errors($r)[0]->media_id);
        $success=$s->moveMedia(new R\MoveMediaRequest(['one'],'folder'));
        self::assertSame(200,$success->status);self::assertSame([],$errors($success));
    }
    public function testPopulatedSubfolderRequiredNullableFields():void
    {
        [$client]=$this->client([
            [200,'{"folder":null,"subfolder":{"hashed_id":"subfolder-hash","name":"part","description":null,"position":0,"created":null,"updated":"2026-10-15T12:00:00Z","cursor":"opaque"}}'],
            [200,'{"folder":null,"subfolder":{"hashed_id":null,"position":null,"created":null,"updated":null}}'],
        ]);
        $service=new DataMediaService($client);
        $subfolder=$service->showMedia(new R\ShowMediaRequest('one'))->data->subfolder;
        self::assertSame('subfolder-hash',$subfolder->hashed_id);
        self::assertSame('part',$subfolder->name);self::assertNull($subfolder->description);
        self::assertSame(0,$subfolder->position);self::assertNull($subfolder->created);
        self::assertSame('2026-10-15T12:00:00Z',$subfolder->updated);self::assertSame('opaque',$subfolder->cursor);
        $nullable=$service->showMedia(new R\ShowMediaRequest('two'))->data->subfolder;
        self::assertNull($nullable->hashed_id);self::assertNull($nullable->position);
        self::assertNull($nullable->created);self::assertNull($nullable->updated);
        self::assertSame(['hashed_id','position','created','updated'],$nullable->presentFields);
    }
    public function testOmissionExplicitNullAndEmptyMaps():void
    {
        [$client,$t]=$this->client([[200,'{"folder":null,"protected":null}'],[200,'{"folder":null}'],[200,'{}']]);
        $s=new DataMediaService($client);
        $a=$s->showMedia(new R\ShowMediaRequest('one'))->data;$b=$s->showMedia(new R\ShowMediaRequest('two'))->data;
        self::assertNull($a->protected); self::assertNull($b->protected);
        self::assertContains('protected',$a->presentFields);self::assertNotContains('protected',$b->presentFields);
        $s->updateMedia(new R\UpdateMediaRequest('one',name:'',description:'',tags:[],custom_metadata:(object)[]));
        self::assertSame('{"name":"","description":"","tags":[],"custom_metadata":{}}',$t->requests[2]->body);
    }
    #[DataProvider('invalidInput')]
    public function testDetectablyInvalidInputIsRejectedBeforeSend(\Closure $make):void
    {
        $this->expectException(\InvalidArgumentException::class);$make();
    }
    public static function invalidInput():array
    {
        $cases=[
            fn()=>new R\ShowMediaRequest(''), fn()=>new R\ShowMediaRequest('..'),
            fn()=>new R\ListMediaRequest(page:0),fn()=>new R\ListMediaRequest(per_page:0),
            fn()=>new R\ListMediaRequest(cursor:new R\Cursor(enabled:1),sort_by:'name'),
            fn()=>new R\ListMediaRequest(sort_by:'id'),fn()=>new R\ListMediaRequest(page:1,cursor:new R\Cursor(after:'next')),
            fn()=>new R\ListMediaRequest(sort_direction:2),fn()=>new R\ListMediaRequest(description_format:'html'),
            fn()=>new R\ShowMediaRequest('one',description_format:'xml'),fn()=>new R\ListMediaRequest(include:'anything'),
            fn()=>new R\ListMediaRequest(type:'Channel'),fn()=>new R\ListMediaRequest(tags:['a'=> 'b']),
            fn()=>new R\ListTagsRequest(cursor:new R\Cursor(enabled:1),sort_by:'taggingsCount'),
            fn()=>new R\ListDeletedMediaRequest(sort_by:'updated'),
            fn()=>new R\Cursor(enabled:2),fn()=>new R\Cursor(before:'a',after:'b'),fn()=>new R\Cursor(after:''),
            fn()=>new R\ArchiveMediaRequest([]),fn()=>new R\ArchiveMediaRequest(array_fill(0,101,'a')),
            fn()=>new R\MoveMediaRequest(array_fill(0,101,'a'),'folder'),fn()=>new R\RestoreMediaRequest(array_fill(0,101,'a'),'folder'),
            fn()=>new R\MoveMediaRequest(['one'],''),fn()=>new R\RestoreDeletedMediaRequest(array_fill(0,1001,'a')),
            fn()=>new R\BulkCopyMediaRequest([],'folder'),fn()=>new R\BulkTagMediaRequest(['one'],[]),
            fn()=>new R\ImportMediaFromURLRequest('/relative'),fn()=>new R\ImportMediaFromURLRequest('file:///private/video'),
            fn()=>new R\ImportMediaFromURLRequest('https://user:pass@example.test/a'),
            fn()=>new R\CreateTagsRequest(str_repeat('a',51)),fn()=>new R\CreateTagsRequest('   '),
            fn()=>new R\CreateMediaFromTrimsRequest('one',[]),fn()=>new R\CreateMediaFromTrimsRequest('one',['bad']),
            fn()=>new R\CreateMediaFromTrimsRequest('one',['00:00:02.000-00:00:01.000']),
            fn()=>new R\CreateMediaFromTrimsRequest('one',['00:60:00.000-00:61:00.000']),
        ];return array_map(fn($v)=>[$v],$cases);
    }
    #[DataProvider('malformedImportUrls')]
    public function testMalformedImportUriNeverReachesTransport(string $url):void
    {
        [$client,$transport]=$this->client([[201,MediaCases::JOB]]);
        try {
            (new DataMediaService($client))->importMediaFromURL(new R\ImportMediaFromURLRequest($url));
            self::fail('Malformed URI must fail before a mutation is sent.');
        } catch (\InvalidArgumentException $error) {
            self::assertStringNotContainsString($url,$error->getMessage());
        } finally {
            self::assertCount(0,$transport->requests);
        }
    }
    public static function malformedImportUrls():array
    {
        return array_map(fn($url)=>[$url],[
            'https://example.test/video%ZZ.mp4', 'https://example.test/video%',
            'https://example.test/video%2', 'https://example.test/?signature=%GG',
            'https://example.test/illegal|path', 'https://example.test/path[brackets]',
            'https://[not-an-ip]/video', 'https://example.test/é',
            'https://example.test/?query=back\\slash', 'https://example.test/#fragment#twice',
        ]);
    }
    #[DataProvider('validImportUrls')]
    public function testValidImportUriBytesArePreserved(string $url):void
    {
        [$client,$transport]=$this->client([[201,MediaCases::JOB]]);
        (new DataMediaService($client))->importMediaFromURL(new R\ImportMediaFromURLRequest($url));
        self::assertCount(1,$transport->requests);
        self::assertSame($url,json_decode($transport->requests[0]->body)->url);
    }
    public static function validImportUrls():array
    {
        return array_map(fn($url)=>[$url],[
            'https://example.test/video%2Fpart.mp4?sig=a%2Bb%3D&x=0#fragment',
            'HTTPS://example.test:8443/a;param?x=%5B%5D&utf8=%C3%A9',
            'https://[2001:db8::1]/video', 'https://192.0.2.1/video?x=one+two',
            "https://example.test/!$&'()*+,;=:@~_-?query=/path?also=valid",
        ]);
    }
    public function testDocumentedListBoundariesAndUnicodeTag():void
    {
        self::assertCount(100,(new R\ArchiveMediaRequest(array_fill(0,100,'a')))->hashed_ids);
        self::assertCount(1000,(new R\RestoreDeletedMediaRequest(array_fill(0,1000,'a')))->media_hashed_ids);
        self::assertSame(str_repeat('é',50),(new R\CreateTagsRequest(str_repeat('é',50)))->name);
        self::assertSame(['00:00:00.000-00:00:01.000'],(new R\CreateMediaFromTrimsRequest('one',['00:00:00.000-00:00:01.000'],true))->trims);
    }
    #[DataProvider('badShapes')]
    public function testMalformedNestedResponsesAreSanitized(string $operation,string $body):void
    {
        [$class,$method,$input,,,,$status]=MediaCases::all()[$operation];
        [$client,$t]=$this->client([[$status,$body]]);
        try { (new $class($client))->$method($input); self::fail('Expected decode failure'); }
        catch (DecodeException $e) { self::assertCount(1,$t->requests);self::assertStringNotContainsString('private',$e->getMessage());self::assertSame(MediaCases::all()[$operation][3]!=='GET',$e->remoteOutcomeUncertain); }
    }
    public static function badShapes():array
    {
        return [
            ['ListMedia','{}'],['ListMedia','[{}]'],['ShowMedia','[]'],['ShowMedia','{}'],['ShowMedia','{"folder":null,"extra":"private"}'],
            ['ShowMedia','{"folder":null,"assets":{}}'],['ShowMedia','{"folder":null,"assets":[{"file_size":"private"}]}'],
            ['ShowMedia','{"folder":null,"speakers":[{"name":null}]}'],['ShowMedia','{"folder":null,"tags":[null]}'],
            ['ShowMedia','{"folder":null,"thumbnail":{"width":"private"}}'],['ShowMedia','{"folder":null,"id":999999999999999999999999}'],
            ['ShowMedia','{"folder":null,"duration":"private"}'],['ShowMedia','{"folder":null,"progress":1.1}'],
            ['ShowMedia','{"folder":null,"status":"unknown-private"}'],['ShowMedia','{"folder":null,"type":"Channel"}'],
            ['ShowMedia','{"folder":null,"subfolder":{"id":"private"}}'],['ShowMedia','{"folder":null,"name":null}'],
            ['SwapMedia','{"message":"queued","media":{},"background_job_status":{"id":1,"hashed_id":"h","status":"unknown"}}'],
            ['ArchiveMedia','{"message":"queued","background_job_status":null}'],['ArchiveMedia','{"message":"queued","background_job_status":{"id":"1","hashed_id":"h","status":"queued"}}'],
            ['ArchiveMedia','{"message":"queued","background_job_status":{"id":1,"status":"queued"}}'],
            ['MoveMedia',substr(MediaCases::JOB,0,-1).',"partial_errors":[{"media_id":null}]}'],
            ['ListTags','[null]'],['ListTags','[{"taggings_count":1.5}]'],
            ['ListDeletedMedia','[{"folder":{"name":3}}]'],['RestoreDeletedMedia','{"background_job_status":{"id":"1"}}'],
        ];
    }
}

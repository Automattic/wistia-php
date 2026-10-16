<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{Client, Configuration, DataFoldersService, DataFolderSharingsService, DataSubfoldersService};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Folders\{CreateSharing, UpdateSharing};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Exception\DecodeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FolderBehaviorTest extends TestCase
{
    private function transport(array $bodies): Transport
    {
        return new class($bodies) implements Transport {
            public array $requests = [];
            public function __construct(private array $bodies) {}
            public function send(TransportRequest $request): TransportResponse {
                $this->requests[] = $request;
                if ($this->bodies === []) { throw new \LogicException('Unexpected extra request.'); }
                return new TransportResponse(200, ['Content-Type'=>['application/json']], array_shift($this->bodies));
            }
        };
    }
    public static function lists(): array
    {
        return [
            [DataFoldersService::class,'paginateFolders',R\ListFoldersRequest::class,FolderCases::FOLDER,'/folders'],
            [DataFolderSharingsService::class,'paginateFolderSharings',R\ListFolderSharingsRequest::class,FolderCases::SHARING,'/folders/id%2F%20%2B%3F/sharings'],
            [DataSubfoldersService::class,'paginateSubfolders',R\ListSubfoldersRequest::class,FolderCases::SUBFOLDER,'/folders/id%2F%20%2B%3F/subfolders'],
        ];
    }
    #[DataProvider('lists')]
    public function testCursorTraversalPreservesFiltersAndParent(string $service, string $method, string $request, string $fixture, string $path): void
    {
        $args=$request===R\ListFoldersRequest::class ? [] : ['id/ +?'];
        foreach ([false,true] as $backwards) {
            $transport=$this->transport(['['.$fixture.']','[]']);
            $input=new $request(...[...$args,'per_page'=>1,'cursor'=>new R\Cursor(before:$backwards?'start':null,after:$backwards?null:'start'),'sort_by'=>'id','sort_direction'=>0,'hashed_ids'=>['a','b']]);
            $pages=(new $service(new Client(new Configuration('synthetic'),$transport)))->$method($input);
            self::assertCount(1,iterator_to_array($pages->items())); self::assertCount(2,$transport->requests);
            $next=$transport->requests[1];
            self::assertStringStartsWith('https://api.wistia.com/modern'.$path.'?',$next->url);
            parse_str(parse_url($next->url,PHP_URL_QUERY),$query);
            self::assertSame(['a','b'],$query['hashed_ids']); self::assertSame('id',$query['sort_by']);
            self::assertSame('0',$query['sort_direction']); self::assertArrayNotHasKey('enabled',$query['cursor']);
            self::assertSame('next',$query['cursor'][$backwards?'before':'after']); self::assertArrayNotHasKey('page',$query);
        }
    }
    #[DataProvider('lists')]
    public function testOffsetTraversalPreservesFiltersAndStopsOnShortPage(string $service, string $method, string $request, string $fixture, string $path): void
    {
        $transport=$this->transport(['['.$fixture.']','[]']);
        $args=$request===R\ListFoldersRequest::class ? [] : ['id/ +?'];
        $input=new $request(...[...$args,'page'=>2,'per_page'=>1,'sort_by'=>'created','sort_direction'=>0,'hashed_ids'=>['a']]);
        self::assertCount(1,iterator_to_array((new $service(new Client(new Configuration('synthetic'),$transport)))->$method($input)->items()));
        parse_str(parse_url($transport->requests[1]->url,PHP_URL_QUERY),$query);
        self::assertSame('3',$query['page']); self::assertSame('created',$query['sort_by']); self::assertSame(['a'],$query['hashed_ids']);
        self::assertArrayNotHasKey('cursor',$query);
    }
    public static function activeSubfolderCursors(): array
    {
        return [[new R\Cursor(1)], [new R\Cursor(before:'start')], [new R\Cursor(after:'start')]];
    }
    #[DataProvider('activeSubfolderCursors')]
    public function testOmittedSubfolderCursorSortUsesIdForPagesAndTraversal(R\Cursor $cursor): void
    {
        $transport=$this->transport(['[]','['.FolderCases::SUBFOLDER.']','[]']);
        $service=new DataSubfoldersService(new Client(new Configuration('synthetic'),$transport));
        $request=new R\ListSubfoldersRequest('folder',per_page:1,cursor:$cursor);
        $service->listSubfolders($request);
        self::assertCount(1,iterator_to_array($service->paginateSubfolders($request)->items()));
        self::assertCount(3,$transport->requests);
        foreach ($transport->requests as $sent) {
            parse_str(parse_url($sent->url,PHP_URL_QUERY),$query);
            self::assertSame('id',$query['sort_by']);
            self::assertArrayNotHasKey('page',$query);
        }
    }
    public function testOffsetSubfolderSortStillUsesServerDefault(): void
    {
        self::assertSame('',(new R\ListSubfoldersRequest('folder'))->query()->encode());
        self::assertSame('cursor%5Benabled%5D=0',(new R\ListSubfoldersRequest('folder',cursor:new R\Cursor(0)))->query()->encode());
    }
    public function testNonAdvancingCursorFailsRatherThanLoops(): void
    {
        $transport=$this->transport(['['.FolderCases::FOLDER.']','['.FolderCases::FOLDER.']']);
        $pages=(new DataFoldersService(new Client(new Configuration('synthetic'),$transport)))->paginateFolders(new R\ListFoldersRequest(per_page:1,cursor:new R\Cursor(1),sort_by:'id'));
        $this->expectException(\UnexpectedValueException::class);
        iterator_to_array($pages->items());
    }
    public function testOmissionNullAndEmptyTextAreDistinct(): void
    {
        self::assertSame('{}',(new R\UpdateSubfolderRequest('folder','sub'))->body()->contents());
        self::assertSame('{"description":null}',(new R\UpdateSubfolderRequest('folder','sub',description:null))->body()->contents());
        self::assertSame('{"description":""}',(new R\UpdateSubfolderRequest('folder','sub',description:''))->body()->contents());
        self::assertSame('{"name":"name"}',(new R\CreateSubfolderRequest('folder','name'))->body()->contents());
        self::assertSame('{}',(new R\CreateFolderRequest())->body()->contents());
        self::assertSame('{}',(new R\CopyFolderRequest('folder'))->body()->contents());
        self::assertSame('{}',(new R\UpdateFolderSharingRequest('folder','sharing'))->body()->contents());
        self::assertSame('{"sharing":{}}',(new R\UpdateFolderSharingRequest('folder','sharing',new UpdateSharing()))->body()->contents());
        self::assertSame('{"sharing":{"with":"private@example.test"}}',(new R\CreateFolderSharingRequest('folder',new CreateSharing('private@example.test')))->body()->contents());
        self::assertSame('{"hashed_ids":[]}',(new R\BulkDeleteSubfoldersRequest('folder',[]))->body()->contents());
    }
    public static function invalidInputs(): array
    {
        return [
            [fn()=>new R\ShowFolderRequest('')], [fn()=>new R\ShowSubfolderRequest('folder','')],
            [fn()=>new R\ListFoldersRequest(page:0)], [fn()=>new R\ListSubfoldersRequest('folder',per_page:0)],
            [fn()=>new R\ListFoldersRequest(page:1,cursor:new R\Cursor(1))],
            [fn()=>new R\ListFoldersRequest(cursor:new R\Cursor(1),sort_by:'name')],
            [fn()=>new R\ListFolderSharingsRequest('folder',cursor:new R\Cursor(after:'next'),sort_by:'created')],
            [fn()=>new R\ListSubfoldersRequest('folder',cursor:new R\Cursor(1),sort_by:'position')],
            [fn()=>new R\ListFoldersRequest(sort_direction:2)], [fn()=>new R\ListFoldersRequest(hashed_ids:[1])],
            [fn()=>new R\BulkDeleteSubfoldersRequest('folder',['a'=>'id'])],
            [fn()=>new R\ShowSubfolderRequest('folder','sub','html')],
            [fn()=>new CreateSharing('private@example.test',sendEmailNotification:'false')],
            [fn()=>new R\CreateSubfolderRequest('folder',str_repeat('é',256))],
            [fn()=>new R\UpdateSubfolderRequest('folder','sub',description:str_repeat('é',1001))],
            [fn()=>new R\UpdateSubfolderRequest('folder','sub',description:"\xff")],
        ];
    }
    #[DataProvider('invalidInputs')]
    public function testInvalidInputIsRejectedBeforeExecution(\Closure $create): void
    {
        $this->expectException(\InvalidArgumentException::class); $create();
    }
    public function testUnicodeLengthBoundaryAndIdOnlyCursorSort(): void
    {
        self::assertSame(str_repeat('é',255),(new R\CreateSubfolderRequest('f',str_repeat('é',255)))->name);
        self::assertSame(str_repeat('é',1000),(new R\UpdateSubfolderRequest('f','s',description:str_repeat('é',1000)))->description);
        self::assertStringContainsString('sort_by=id',(new R\ListSubfoldersRequest('f',cursor:new R\Cursor(1),sort_by:'id'))->query()->encode());
    }
    public function testCopyIsBareJobAndBulkDeleteHasEnvelope(): void
    {
        $copy=\Automattic\Wistia\Result\Data\CopyFolderResult::fromWire(json_decode(FolderCases::JOB),202);
        $bulk=\Automattic\Wistia\Result\Data\BulkDeleteSubfoldersResult::fromWire(json_decode(FolderCases::QUEUED),200);
        self::assertSame('job',$copy->data->hashed_id); self::assertSame('queued',$copy->data->status);
        self::assertSame('job',$bulk->data->background_job_status->hashed_id);
        self::assertSame('queued',$bulk->data->background_job_status->status);
    }
    public function testTypedNestedMediaSharingAndNullableSubfolder(): void
    {
        $value=json_decode(FolderCases::SUBFOLDER);$value->medias=[json_decode(FolderCases::MEDIA)];
        $result=\Automattic\Wistia\Result\Data\ShowSubfolderResult::fromWire($value,200);
        self::assertSame(100,$result->data->medias[0]->thumbnail->width);
        self::assertSame('private embed',$result->data->medias[0]->embedCode);
        $sharing=\Automattic\Wistia\Result\Data\ShowFolderSharingResult::fromWire(json_decode(FolderCases::SHARING),200);
        self::assertSame('private@example.test',$sharing->data->share->email);
        self::assertSame(1,$sharing->data->folder->id);
        $sub=\Automattic\Wistia\Result\Data\ShowSubfolderResult::fromWire(json_decode('{"hashed_id":null,"position":null,"created":null,"updated":null}'),200);
        self::assertNull($sub->data->position);self::assertNotContains('name',$sub->data->presentFields);
    }
    public static function malformedRecords(): array
    {
        return [
            [R\ShowFolderRequest::class,DataFoldersService::class,'showFolder',['folder'],'{}'],
            [R\ShowFolderRequest::class,DataFoldersService::class,'showFolder',['folder'],str_replace('"kind":"shared"','"kind":"invalid"',FolderCases::FOLDER)],
            [R\ShowFolderSharingRequest::class,DataFolderSharingsService::class,'showFolderSharing',['folder',2],str_replace('"id":3','"id":"3"',FolderCases::SHARING)],
            [R\ShowSubfolderRequest::class,DataSubfoldersService::class,'showSubfolder',['folder','sub'],substr(FolderCases::SUBFOLDER,0,-1).',"medias":[{"thumbnail":{"width":"100"}}]}'],
            [R\ShowSubfolderRequest::class,DataSubfoldersService::class,'showSubfolder',['folder','sub'],'[]'],
        ];
    }
    #[DataProvider('malformedRecords')]
    public function testMalformedNestedResultsDoNotBecomeSuccess(string $request,string $service,string $method,array $args,string $body): void
    {
        $transport=$this->transport([$body]);$api=new $service(new Client(new Configuration('synthetic'),$transport));
        $this->expectException(DecodeException::class);$api->$method(new $request(...$args));
    }
}

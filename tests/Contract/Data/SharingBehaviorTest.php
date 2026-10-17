<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{Client, Configuration, DataReviewBundlesService, DataSearchService, DataShareLinksService};
use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as D;
use Automattic\Wistia\Sharing\{ContactReference, MetadataFilter, SearchEnvelopeDataMediasItem, ShareLink};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SharingBehaviorTest extends TestCase
{
    private function client(array $bodies): array
    {
        $transport = new class($bodies) implements Transport {
            public array $requests = [];
            public function __construct(private array $bodies) {}
            public function send(TransportRequest $request): TransportResponse {
                $this->requests[] = $request;
                if ($this->bodies === []) { throw new \LogicException('Unexpected extra request.'); }
                return new TransportResponse(200,['Content-Type'=>['application/json']],array_shift($this->bodies));
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
            [fn()=>new R\SearchRequest('q',resource_type:['private'])],
            [fn()=>new R\SearchRequest('q',tags:[1])],
            [fn()=>new R\SearchRequest('q',custom_metadata:['a'=>MetadataFilter::exists(false)])],
            [fn()=>new R\SearchRequest('q',resource_type:['media'],custom_metadata:['a'=>new \stdClass()])],
            [fn()=>new R\SearchRequest('q',resource_type:['media'],custom_metadata:['a[b]'=>MetadataFilter::exists(false)])],
            [fn()=>new R\SearchRequest('q',include:'private')],
            [fn()=>new R\SearchRequest('q',created_after:'2026-02-30T00:00:00Z')],
            [fn()=>new R\SearchRequest('q',created_before:'2026-01-01T00:00:00+01:00')],
            [fn()=>new R\ResolveResourceURLsRequest('private','id')],
            [fn()=>new R\ResolveResourceURLsRequest('media','')],
            [fn()=>new R\CreateReviewBundleRequest([],'Review')],
            [fn()=>new R\CreateReviewBundleRequest(array_fill(0,26,'m'),'Review')],
            [fn()=>new R\CreateReviewBundleRequest([1],'Review')],
            [fn()=>new R\CreateReviewBundleRequest(['m'],' ')],
            [fn()=>new R\DeleteReviewBundleRequest(' ')],
            [fn()=>new R\ShowShareLinkRequest('')],
            [fn()=>new R\ResolveShareLinkRequest('')],
            [fn()=>new R\DeleteShareLinkRequest('')],
            [fn()=>new R\UpdateShareLinkRequest('m','private')],
            [fn()=>new R\ListReviewBundlesRequest(page:0)],
            [fn()=>new R\ListReviewBundlesRequest(per_page:0)],
            [fn()=>new R\ListReviewBundlesRequest(page:1,cursor:new R\Cursor(enabled:1))],
            [fn()=>new R\ListReviewBundlesRequest(sort_by:'private')],
            [fn()=>new R\ListReviewBundlesRequest(sort_direction:2)],
            [fn()=>MetadataFilter::numericRange()], [fn()=>MetadataFilter::numericRange('private')],
            [fn()=>MetadataFilter::dateRange(after:'2026-02-30')], [fn()=>MetadataFilter::values([1])],
        ];
    }
    public function testMetadataEncodingPreservesEmptyFalseZeroAndRepeatedValues(): void
    {
        $request=new R\SearchRequest('',resource_type:['media'],custom_metadata:[
            'region'=>MetadataFilter::values(['emea','amer']), 'flag'=>MetadataFilter::exact('false'),
            'budget'=>MetadataFilter::numericRange('0','500'), 'missing'=>MetadataFilter::exists(false),
            'date'=>MetadataFilter::dateRange(after:'2026-01-01',before:'2026-02-01T00:00:00Z'),
        ],include:'custom_metadata',created_after:'2026-01-01T00:00:00Z');
        [$client,$transport]=$this->client([SharingCases::fixture('Search')]);
        (new DataSearchService($client))->search($request);
        self::assertSame('https://api.wistia.com/modern/search?q=&resource_type%5B%5D=media'
            .'&custom_metadata%5Bregion%5D%5B%5D=emea&custom_metadata%5Bregion%5D%5B%5D=amer'
            .'&custom_metadata%5Bflag%5D=false&custom_metadata%5Bbudget%5D%5Bmin%5D=0&custom_metadata%5Bbudget%5D%5Bmax%5D=500'
            .'&custom_metadata%5Bmissing%5D%5Bexists%5D=false&custom_metadata%5Bdate%5D%5Bafter%5D=2026-01-01'
            .'&custom_metadata%5Bdate%5D%5Bbefore%5D=2026-02-01T00%3A00%3A00Z&include=custom_metadata&created_after=2026-01-01T00%3A00%3A00Z', $transport->requests[0]->url);
    }
    public function testPositiveFixtureDeclaredFormats(): void
    {
        $inventory=json_decode(file_get_contents(__DIR__.'/../../../docs/rewrite/operations.json'),true,flags:JSON_THROW_ON_ERROR);
        foreach ($inventory['operations'] as $operation) {
            if (($operation['planned_owner_issue'] ?? null)!==22) { continue; }
            $name=substr(basename(str_replace('\\', '/', $operation['planned']['request_type'])),0,-7);
            foreach ($operation['responses'] as $status=>$response) {
                if ((int)$status<200 || (int)$status>=300) { continue; }
                foreach ($response['content'] ?? [] as $content) {
                    if (!isset($content['schema'])) { continue; }
                    $this->assertFixtureFormats(json_decode(SharingCases::fixture($name),flags:JSON_THROW_ON_ERROR),
                        $inventory['wire_schemas'][$content['schema']],$name);
                }
            }
        }
    }
    /** Only validates declared fixture formats, not a general JSON Schema validator. */
    private function assertFixtureFormats(mixed $value, array $schema, string $path): void
    {
        if ($value===null) { return; }
        foreach (['anyOf','oneOf','allOf'] as $combinator) {
            foreach ($schema[$combinator] ?? [] as $branch) { $this->assertFixtureFormats($value,$branch,$path); }
        }
        if (($schema['format'] ?? null)==='date-time') {
            self::assertMatchesRegularExpression('/^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}(?:\\.\\d+)?(?:Z|[+-]\\d{2}:\\d{2})$/D',$value,$path);
            new \DateTimeImmutable($value);
            $errors=\DateTimeImmutable::getLastErrors();
            self::assertTrue($errors===false || ($errors['warning_count']===0 && $errors['error_count']===0),$path);
        }
        if (($schema['format'] ?? null)==='uri') { self::assertNotFalse(filter_var($value,FILTER_VALIDATE_URL),$path); }
        if ($value instanceof \stdClass) {
            foreach ($schema['properties'] ?? [] as $field=>$child) {
                if (property_exists($value,$field)) { $this->assertFixtureFormats($value->$field,$child,$path.'.'.$field); }
            }
        }
        if (is_array($value) && isset($schema['items'])) {
            foreach ($value as $index=>$item) { $this->assertFixtureFormats($item,$schema['items'],$path.'['.$index.']'); }
        }
    }
    public function testMetadataTimestampBoundsRetainOffsets(): void
    {
        $query=(new R\SearchRequest('',resource_type:['media'],custom_metadata:['date'=>MetadataFilter::dateRange(after:'2026-01-01T01:00:00+01:00')]))->query()->encode();
        self::assertStringContainsString('2026-01-01T01%3A00%3A00%2B01%3A00',$query);
    }
    public function testTypedSearchGroupsTranscriptAndMetadataResults(): void
    {
        $result=D\SearchResult::fromWire(json_decode(SharingCases::fixture('Search')),200);
        $data=$result->data->data;
        self::assertCount(1,$data->folders); self::assertCount(1,$data->subfolders);
        self::assertCount(1,$data->channels); self::assertCount(1,$data->channel_episodes); self::assertCount(1,$data->webinars);
        $media=$data->medias[0]; self::assertInstanceOf(SearchEnvelopeDataMediasItem::class,$media);
        self::assertSame(0.5,$media->transcript_matches[0]->start_time_seconds);
        self::assertSame('private synthetic',$media->folder_hashed_id);
        self::assertSame('ui',$media->custom_metadata_field_values[0]->last_write->source);
        self::assertStringNotContainsString('private',print_r($result,true));
        foreach ([false,0,'text',['a','b'],(object)['type'=>'contact','id'=>'c'],[(object)['type'=>'contact_group','id'=>'g']],null] as $value) {
            $wire=json_decode(SharingCases::fixture('Search'));
            $wire->data->medias[0]->custom_metadata_field_values[0]->value=$value;
            $decoded=D\SearchResult::fromWire($wire,200)->data->data->medias[0]->custom_metadata_field_values[0]->value;
            if ($value instanceof \stdClass) { self::assertInstanceOf(ContactReference::class,$decoded); }
            elseif ($value===0) { self::assertSame(0.0,$decoded); }
            elseif (is_array($value) && ($value[0] ?? null) instanceof \stdClass) { self::assertInstanceOf(ContactReference::class,$decoded[0]); }
            else { self::assertSame($value,$decoded); }
        }
    }
    public function testOptionalAndNullableFieldsRemainDistinct(): void
    {
        $bundle=D\CreateReviewBundleResult::fromWire((object)[],201)->data;
        self::assertNull($bundle->cursor); self::assertSame([],$bundle->presentFields);
        $bundle=D\CreateReviewBundleResult::fromWire((object)['cursor'=>null,'allow_downloads'=>false],201)->data;
        self::assertSame(['cursor','allow_downloads'],$bundle->presentFields); self::assertFalse($bundle->allow_downloads);
        self::assertSame('{"media_hashed_ids":["m"],"name":"Review"}',(new R\CreateReviewBundleRequest(['m'],'Review'))->body()->contents());
        self::assertSame(25,count(json_decode((new R\CreateReviewBundleRequest(array_fill(0,25,'m'),'Review'))->body()->contents())->media_hashed_ids));
    }
    #[DataProvider('visibilities')]
    public function testVisibilityAndNullableSlug(string $visibility): void
    {
        $request=new R\UpdateShareLinkRequest('m',$visibility);
        self::assertSame('{"visibility":"'.$visibility.'"}',$request->body()->contents());
        $wire=json_decode(SharingCases::fixture('ShowShareLink')); $wire->visibility=$visibility; $wire->slug=null;
        $link=ShareLink::fromWire($wire); self::assertNull($link->slug); self::assertSame($visibility,$link->visibility);
    }
    public static function visibilities(): array { return [['unlocked'],['account'],['locked'],['domain_verified']]; }
    public function testOffsetPaginationPreservesAllFiltersAndStartsAtRequestedPage(): void
    {
        [$client,$transport]=$this->client(['[{"hashed_id":"b"}]','[]']);
        $request=new R\ListReviewBundlesRequest(hashed_ids:['b','c'],name:'Review',media_hashed_id:'m',folder_hashed_id:'f',sort_by:'updated',sort_direction:0,page:3,per_page:1);
        $iterator=(new DataReviewBundlesService($client))->paginateReviewBundles($request);
        self::assertCount(0,$transport->requests); self::assertCount(1,iterator_to_array($iterator->items()));
        foreach ($transport->requests as $i=>$sent) {
            self::assertSame('https://api.wistia.com/modern/review_bundles?hashed_ids%5B%5D=b&hashed_ids%5B%5D=c&name=Review&media_hashed_id=m&folder_hashed_id=f&sort_by=updated&sort_direction=0&per_page=1&page='.($i+3),$sent->url);
        }
    }
    public function testCursorPaginationPreservesFiltersOrderingAndDirection(): void
    {
        foreach ([false,true] as $backward) {
            [$client,$transport]=$this->client(['[{"hashed_id":"b","cursor":"next"}]','[]']);
            $cursor=$backward ? new R\Cursor(before:'start') : new R\Cursor(after:'start');
            $request=new R\ListReviewBundlesRequest(name:'Review',sort_by:'name',sort_direction:0,per_page:1,cursor:$cursor);
            self::assertCount(1,iterator_to_array((new DataReviewBundlesService($client))->paginateReviewBundles($request)->items()));
            foreach ($transport->requests as $i=>$sent) {
                self::assertSame('https://api.wistia.com/modern/review_bundles?name=Review&sort_by=name&sort_direction=0&per_page=1&cursor%5B'.($backward?'before':'after').'%5D='.($i===0?'start':'next'),$sent->url);
            }
        }
    }
    public function testNonAdvancingCursorStopsWithoutExtraRequests(): void
    {
        [$client,$transport]=$this->client(['[{"cursor":"start"}]']);
        $pages=(new DataReviewBundlesService($client))->paginateReviewBundles(new R\ListReviewBundlesRequest(per_page:1,cursor:new R\Cursor(after:'start')));
        try { iterator_to_array($pages->items()); self::fail('Expected non-advancing cursor failure'); }
        catch (\UnexpectedValueException) { self::assertCount(1,$transport->requests); }
    }
    public function testFiniteBoundStopsTraversal(): void
    {
        [$client,$transport]=$this->client(['[{"hashed_id":"b"}]']);
        $pages=(new DataReviewBundlesService($client))->paginateReviewBundles(new R\ListReviewBundlesRequest(per_page:1), maxPages:1);
        try { iterator_to_array($pages->items()); self::fail('Expected page bound failure'); }
        catch (\OverflowException) { self::assertCount(1,$transport->requests); }
    }
    public function testSchemaLessDeleteDoesNotInventBodyContract(): void
    {
        [$client,$transport]=$this->client(['not JSON']);
        self::assertNull((new DataShareLinksService($client))->deleteShareLink(new R\DeleteShareLinkRequest('m'))->data);
        self::assertCount(1,$transport->requests);
    }
    #[DataProvider('invalidOtherResponses')]
    public function testMalformedOtherResponseRecordsFail(string $operation, callable $mutate): void
    {
        $wire=json_decode(SharingCases::fixture($operation)); $mutate($wire);
        [$client,$transport]=$this->client([json_encode($wire)]);
        [$service,$method,$request]=SharingCases::all()[$operation];
        try { (new $service($client))->$method($request); self::fail('Expected decoding failure.'); }
        catch (DecodeException $e) {
            self::assertSame($operation==='DeleteReviewBundle' || $operation==='ShowShareLink',$e->remoteOutcomeUncertain);
            self::assertStringNotContainsString('private',$e->getMessage());
            self::assertCount(1,$transport->requests);
        }
    }
    public static function invalidOtherResponses(): array
    {
        return [
            ['ResolveResourceURLs',static function($v) { $v->urls->show=1; }],
            ['ListReviewBundles',static function($v) { $v[0]->allow_downloads='private'; }],
            ['DeleteReviewBundle',static function($v) { $v->thumbnail=1; }],
            ['ShowShareLink',static function($v) { $v->slug=1; }],
        ];
    }
    #[DataProvider('invalidResponses')]
    public function testNestedInvalidSearchShapesAreSanitized(callable $mutate): void
    {
        $wire=json_decode(SharingCases::fixture('Search')); $mutate($wire);
        [$client,$transport]=$this->client([json_encode($wire)]);
        try { (new DataSearchService($client))->search(new R\SearchRequest('q')); self::fail('Expected decode failure'); }
        catch (DecodeException $e) { self::assertFalse($e->remoteOutcomeUncertain); self::assertStringNotContainsString('private',$e->getMessage()); }
    }
    public static function invalidResponses(): array
    {
        return [
            [static function($v) { unset($v->data->folders); }],
            [static function($v) { $v->data->medias[0]->transcript_matches[0]->start_time_seconds='private'; }],
            [static function($v) { $v->data->channels=(object)[]; }],
            [static function($v) { $v->data->medias[0]->custom_metadata_field_values[0]->value=(object)['private'=>1]; }],
            [static function($v) { $v->data->folders[0]->public='private'; }],
        ];
    }
}

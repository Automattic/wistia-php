<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Offline\Pagination;
use Automattic\Wistia\Pagination\{AnalyticsCursorPagination, DataCursorPagination, OffsetPagination, Page, Paginator};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PaginatorTest extends TestCase
{
    public function testOffsetLazyIndividualAndFilterPreservation(): void
    {
        $queries=[]; $pages=[new Page([1,2]),new Page([3])];
        $p=new Paginator(new OffsetPagination([['sort_by','name'],['sort_direction',0],['tags[]','a b'],['tags[]','c'],['archived',false]],2), function($q)use(&$queries,$pages){ $queries[]=$q->encode(); return $pages[count($queries)-1]; });
        $iterator=$p->items(); self::assertSame([],$queries); self::assertSame(1,$iterator->current()); self::assertCount(1,$queries);
        self::assertSame([1,2,3],iterator_to_array($iterator)); self::assertCount(2,$queries);
        self::assertSame('sort_by=name&sort_direction=0&tags%5B%5D=a%20b&tags%5B%5D=c&archived=false&per_page=2&page=1',$queries[0]);
        self::assertSame(str_replace('page=1','page=2',$queries[0]),$queries[1]);
        $single=new Paginator(new OffsetPagination(perPage:2),fn($q)=>new Page([$q->encode()]));
        self::assertSame(['per_page=2&page=7'],$single->page(7)->items);
    }
    public function testFullOffsetFinalPageNeedsOneEmptyRead(): void
    {
        $calls=0;$p=new Paginator(new OffsetPagination(perPage:1),function()use(&$calls){return new Page(++$calls===1?[1]:[]);});
        self::assertSame([1],iterator_to_array($p->items(),false)); self::assertSame(2,$calls);
    }
    public function testDataRecordCursorsPreserveSortAndOpaqueWire(): void
    {
        $queries=[]; $pages=[[(object)['cursor'=>'a /+'],(object)['cursor'=>'b ?']],[]];
        $p=new Paginator(new DataCursorPagination([['sort_by','id'],['sort_direction',0],['folder_id','folder']],2),function($q)use(&$queries,$pages){$queries[]=$q->encode();return DataCursorPagination::fromRecords($pages[count($queries)-1]);});
        self::assertCount(2,iterator_to_array($p->items(),false));
        self::assertSame('sort_by=id&sort_direction=0&folder_id=folder&per_page=2&cursor%5Benabled%5D=1',$queries[0]);
        self::assertSame('sort_by=id&sort_direction=0&folder_id=folder&per_page=2&cursor%5Bafter%5D=b%20%3F',$queries[1]);
        $back=new DataCursorPagination(perPage:1,cursor:'b',backwards:true);
        self::assertSame('per_page=1&cursor%5Bbefore%5D=b',$back->query($back->first())->encode());
        self::assertSame('a',$back->next(new Page([1],endCursor:'b',startCursor:'a'),'b'));
    }
    public function testAnalyticsContinuesEmptyPageWhenPageInfoSaysMoreAndStopsFullFinal(): void
    {
        $queries=[];$pages=[new Page([], 'next',true),new Page([1,2],null,false)];
        $p=new Paginator(new AnalyticsCursorPagination([['start_date','2026-01-01'],['end_date','2026-02-01']],2),function($q)use(&$queries,$pages){$queries[]=$q->encode();return $pages[count($queries)-1];});
        self::assertSame([1,2],iterator_to_array($p->items(),false)); self::assertCount(2,$queries);
        self::assertSame('start_date=2026-01-01&end_date=2026-02-01&per_page=2&cursor=next',$queries[1]);
    }
    public function testDistinctEnvelopeKeysAndWebinarPreviousDirection(): void
    {
        $info=(object)['has_next_page'=>true,'has_previous_page'=>true,'end_cursor'=>'end','start_cursor'=>'start'];
        $a=AnalyticsCursorPagination::fromEnvelope((object)['data'=>[1],'page_info'=>$info]); self::assertSame([1],$a->items);
        $d=DataCursorPagination::fromWebinarRegistrations((object)['registrations'=>[2],'page_info'=>$info]); self::assertSame([2],$d->items);
        $adapter=new DataCursorPagination([['attendance','attendees'],['emails[]','synthetic@example.invalid']],2,'initial',true,true);
        self::assertSame('attendance=attendees&emails%5B%5D=synthetic%40example.invalid&per_page=2&sort_direction=0&cursor=initial',$adapter->query($adapter->first())->encode());
        self::assertSame('start',$adapter->next($d,'initial'));
        self::assertNull($adapter->next(new Page([2]),'initial'));
    }
    #[DataProvider('cursorCycles')]
    public function testRepeatedAndCyclicCursorsFailWithoutExtraFetch(array $cursors): void
    {
        $calls=0;$p=new Paginator(new AnalyticsCursorPagination(cursor:'initial'),function()use(&$calls,$cursors){return new Page([++$calls],$cursors[$calls-1],true);});
        try { iterator_to_array($p->items(),false); self::fail('Expected cursor failure'); }
        catch (\UnexpectedValueException $e) { self::assertSame(count($cursors),$calls);self::assertStringNotContainsString('initial',$e->getMessage()); }
    }
    public static function cursorCycles(): array {return [[['initial']],[['a','b','a']],[['']],[ [null] ]];}
    public function testFiniteBoundDoesNotSilentlyTruncate(): void
    {
        $calls=0;$p=new Paginator(new OffsetPagination(perPage:1),function()use(&$calls){return new Page([++$calls]);},2);
        try {iterator_to_array($p->items());self::fail();}catch(\OverflowException){self::assertSame(2,$calls);}
    }
    #[DataProvider('conflicts')]
    public function testAllAdaptersRejectPaginationFilterConflicts(string $name): void
    {
        foreach ([OffsetPagination::class,DataCursorPagination::class,AnalyticsCursorPagination::class] as $class) {
            try {new $class([[$name,1]]);self::fail('Conflict accepted');}catch(\InvalidArgumentException){self::assertTrue(true);}
        }
    }
    public static function conflicts():array{return array_map(fn($n)=>[$n],['page','per_page','cursor','cursor[enabled]','cursor[before]','cursor[after]']);}
    public function testOffsetOverflowDetected():void
    {
        $a=new OffsetPagination(perPage:1,startPage:PHP_INT_MAX);self::assertStringContainsString((string)PHP_INT_MAX,$a->query($a->first())->encode());
        $this->expectException(\OverflowException::class);$a->next(new Page([1]),PHP_INT_MAX);
    }
    public function testNullableCursorOnShortFinalRecordPage():void
    {
        $page=DataCursorPagination::fromRecords([(object)['cursor'=>null]]);
        self::assertCount(1,$page->items);self::assertNull((new DataCursorPagination(perPage:2))->next($page,null));
        self::assertNull(DataCursorPagination::fromRecords([(object)['id'=>1]])->endCursor);
    }
    public function testContinuingDataPageWithoutCursorFails():void
    {
        $page=DataCursorPagination::fromRecords([(object)['cursor'=>null]]);
        $this->expectException(\UnexpectedValueException::class);(new DataCursorPagination(perPage:1))->next($page,null);
    }
    public function testMalformedEnvelopeRejected():void { $this->expectException(\UnexpectedValueException::class);AnalyticsCursorPagination::fromEnvelope((object)['data'=>[],'page_info'=>(object)['has_next_page'=>'true']]); }
    public function testInvalidBoundsRejected():void { $this->expectException(\InvalidArgumentException::class);new OffsetPagination(perPage:101); }
    public function testWebinarDirectionConflictRejected():void { $this->expectException(\InvalidArgumentException::class);new DataCursorPagination([['sort_direction',1]],pageInfo:true); }
}

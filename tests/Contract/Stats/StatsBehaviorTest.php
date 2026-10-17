<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Stats;

use Automattic\Wistia\{Client, Configuration, StatsEventsService, StatsVisitorsService};
use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Request\Stats as R;
use Automattic\Wistia\Result\Stats as D;
use Automattic\Wistia\Stats\{Event, Visitor};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class StatsBehaviorTest extends TestCase
{
    public function testTypedNestedValuesAndWireCase(): void
    {
        $event=Event::fromWire(json_decode(StatsCases::fixture('ShowEvent')));
        self::assertSame(123,$event->thumbnail->fileSize);
        self::assertSame('image/jpeg',$event->thumbnail->contentType);
        self::assertSame(0,$event->conversion_type);
        self::assertSame(0.0,$event->lat);
        self::assertFalse($event->conversion_data->is_new_lead);
        self::assertFalse($event->user_agent_details->mobile);
        self::assertFalse($event->attributes->private_attribute->enabled);
        self::assertSame([null,'test'],$event->attributes->private_attribute->values);
        self::assertStringNotContainsString('private',print_r($event,true));
        $visitor=Visitor::fromWire(json_decode(StatsCases::fixture('ShowVisitor')));
        self::assertNull($visitor->visitor_identity->org->title);
        self::assertNull($visitor->last_event_key);
        self::assertSame(0,$visitor->load_count);
        $media=D\ShowMediaStatsResult::fromWire(json_decode(StatsCases::fixture('ShowMediaStats')),200)->data;
        self::assertSame(0.0,$media->play_rate);
        self::assertSame(0,$media->actions[0]->action_count);
        self::assertNull($media->actions[0]->url);
        $engagement=D\ShowMediaEngagementResult::fromWire(json_decode(StatsCases::fixture('ShowMediaEngagement')),200)->data;
        self::assertSame([0,2,1],$engagement->engagement_data);
        self::assertSame([0,0,1],$engagement->rewatch_data);
        self::assertSame(0,D\ShowProjectStatsResult::fromWire(json_decode(StatsCases::fixture('ShowProjectStats')),200)->data->number_of_videos);
        self::assertSame(0,D\ShowCurrentAccountStatsResult::fromWire(json_decode(StatsCases::fixture('ShowCurrentAccountStats')),200)->data->play_count);
        self::assertSame(0.0,D\ShowAccountStatsByDateResult::fromWire(json_decode(StatsCases::fixture('ShowAccountStatsByDate')),200)->data[0]->hours_watched);
    }
    public function testOmissionAndExplicitNullRemainDistinct(): void
    {
        $missing=Event::fromWire((object)[]); $null=Event::fromWire((object)['email'=>null,'conversion_type'=>'email_gate']);
        self::assertNull($missing->email); self::assertNull($null->email);
        self::assertNotContains('email',$missing->presentFields); self::assertContains('email',$null->presentFields);
        self::assertSame('email_gate',$null->conversion_type);
        self::assertSame([],D\ListEventsResult::fromWire([],200)->data);
        self::assertSame([],D\ListVisitorsResult::fromWire([],200)->data);
    }
    #[DataProvider('badRecords')]
    public function testNestedShapeFailuresAreSanitized(string $operation, #[\SensitiveParameter] string $body): void
    {
        [$class,$method,$input]=StatsCases::all()[$operation];
        $transport=self::transport([$body]);
        try { (new $class(new Client(new Configuration('synthetic-token'),$transport)))->$method($input); self::fail('Expected decoding failure.'); }
        catch (DecodeException $e) { self::assertNull($e->getPrevious()); self::assertStringNotContainsString('private',(string)$e); self::assertFalse($e->remoteOutcomeUncertain); }
    }
    public static function badRecords(): array
    {
        return [
            ['ShowEvent','{"conversion_type":false}'], ['ShowEvent','{"thumbnail":{"fileSize":"private"}}'],
            ['ShowEvent','{"thumbnail":{"file_size":1}}'], ['ShowEvent','{"conversion_data":{"is_new_lead":0}}'],
            ['ShowEvent','{"attributes":[]}'], ['ShowVisitor','{"visitor_identity":{"org":[]}}'],
            ['ShowVisitor','{"user_agent_details":{"mobile":"private"}}'], ['ShowMediaStats','{"actions":[{"rate":"private"}]}'],
            ['ShowMediaEngagement','{"engagement_data":[0,"private"]}'], ['ShowMediaEngagement','{"rewatch_data":{}}'],
            ['ShowCurrentAccountStats','{"load_count":1,"play_count":0}'], ['ShowProjectStats','{"number_of_videos":"private"}'],
            ['ShowAccountStatsByDate','[{"hours_watched":"private"}]'], ['ListEvents','[null]'], ['ListVisitors','[42]'],
            ['ShowEvent','{"email":42}'], ['ShowEvent','{"percent_viewed":null}'], ['ShowEvent','{"lat":1e999}'],
        ];
    }
    public function testServerOwnsDefaultDateWindowAndBoundsAreInclusive(): void
    {
        self::assertSame('',(new R\ShowAccountStatsByDateRequest())->query()->encode());
        self::assertSame('',(new R\ShowMediaStatsByDateRequest('22570'))->query()->encode());
        self::assertSame('start_date=2024-02-29&end_date=2024-02-29',(new R\ShowAccountStatsByDateRequest('2024-02-29','2024-02-29'))->query()->encode());
        self::assertSame('end_date=2024-03-01',(new R\ShowMediaStatsByDateRequest('hash',end_date:'2024-03-01'))->query()->encode());
    }
    #[DataProvider('invalidRequests')]
    public function testInvalidRequestsBeforeTransport(string $class, array $args): void
    {
        $this->expectException(\InvalidArgumentException::class); new $class(...$args);
    }
    public static function invalidRequests(): array
    {
        return [
            [R\ShowAccountStatsByDateRequest::class,["2024-02-29\0"]], [R\ShowAccountStatsByDateRequest::class,['0000-01-01']],
            [R\ShowAccountStatsByDateRequest::class,['2023-02-29']], [R\ShowMediaStatsByDateRequest::class,['m','2024-2-01']],
            [R\ShowAccountStatsByDateRequest::class,['2024-03-02','2024-03-01']], [R\ListEventsRequest::class,['start_date'=>'2024-04-31']],
            [R\ListEventsRequest::class,['start_date'=>'2024-03-02','end_date'=>'2024-03-01']],
            [R\ListEventsRequest::class,['per_page'=>101]], [R\ListEventsRequest::class,['page'=>0]], [R\ListVisitorsRequest::class,['per_page'=>0]],
            [R\ListVisitorsRequest::class,['page'=>-1]], [R\ListVisitorsRequest::class,['filter'=>'unknown']],
            [R\ShowEventRequest::class,['']], [R\ShowVisitorRequest::class,[' ']], [R\ShowMediaStatsRequest::class,['']],
            [R\ShowProjectStatsRequest::class,['']], [R\ShowMediaEngagementRequest::class,['']],
        ];
    }
    #[DataProvider('paginationCases')]
    public function testLazyPaginationPreservesFilters(bool $events): void
    {
        $fixture=StatsCases::fixture($events?'ShowEvent':'ShowVisitor');
        $transport=self::transport(['['.$fixture.','.$fixture.']','['.$fixture.']']);
        $client=new Client(new Configuration('synthetic-token'),$transport);
        $paginator=$events
            ? (new StatsEventsService($client))->paginateEvents(new R\ListEventsRequest(media_id:'m/a',visitor_key:'v',per_page:2,page:3,start_date:'2024-02-29',end_date:'2024-03-01'))
            : (new StatsVisitorsService($client))->paginateVisitors(new R\ListVisitorsRequest(page:3,per_page:2,filter:'identified_by_email_gate',search:'a+b@example.invalid'));
        self::assertCount(0,$transport->requests);
        self::assertCount(3,iterator_to_array($paginator->items()));
        self::assertCount(2,$transport->requests);
        $filter=$events?'media_id=m%2Fa&visitor_key=v&start_date=2024-02-29&end_date=2024-03-01':'filter=identified_by_email_gate&search=a%2Bb%40example.invalid';
        foreach ([3,4] as $i=>$page) {
            self::assertSame('https://api.wistia.com/modern/stats/'.($events?'events':'visitors').'?'.$filter.'&per_page=2&page='.$page,$transport->requests[$i]->url);
            self::assertSame('Bearer synthetic-token',$transport->requests[$i]->headers['Authorization']);
            self::assertSame('2026-09',$transport->requests[$i]->headers['X-Wistia-API-Version']);
        }
    }
    public static function paginationCases(): array { return [[true],[false]]; }
    public function testEmptyPageAndTraversalBound(): void
    {
        $transport=self::transport(['[]']); $service=new StatsEventsService(new Client(new Configuration('t'),$transport));
        self::assertSame([],iterator_to_array($service->paginateEvents(new R\ListEventsRequest())->items()));
        self::assertCount(1,$transport->requests);
        $transport=self::transport(['[{}]']); $service=new StatsVisitorsService(new Client(new Configuration('t'),$transport));
        try { iterator_to_array($service->paginateVisitors(new R\ListVisitorsRequest(per_page:1),maxPages:1)->items()); self::fail('Expected finite bound.'); }
        catch (\OverflowException $e) { self::assertCount(1,$transport->requests); }
    }
    private static function transport(array $bodies): Transport
    {
        return new class($bodies) implements Transport {
            public array $requests=[];
            public function __construct(private array $bodies) {}
            public function send(TransportRequest $request): TransportResponse {
                $this->requests[]=$request;
                return new TransportResponse(200,['Content-Type'=>['application/json']],array_shift($this->bodies));
            }
        };
    }
}

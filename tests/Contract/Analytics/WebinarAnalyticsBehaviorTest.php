<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Analytics;

use Automattic\Wistia\{AnalyticsWebinarService, Client, Configuration};
use Automattic\Wistia\Analytics as A;
use Automattic\Wistia\Request\Analytics as R;
use Automattic\Wistia\Result\Analytics as D;
use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class WebinarAnalyticsBehaviorTest extends TestCase
{
    public function testTypedMetricsPollsChatAndHistograms(): void
    {
        $aggregate = D\ShowWebinarAnalyticsResult::fromWire(json_decode(WebinarAnalyticsCases::fixture('ShowWebinarAnalytics')), 200)->data;
        self::assertInstanceOf(A\WebinarAnalytics::class, $aggregate);
        self::assertSame(0, $aggregate->total_watch_time);
        self::assertSame(0.5, $aggregate->engagement_rate);
        self::assertInstanceOf(A\WebinarPollQuestion::class, $aggregate->poll_questions[0]);
        self::assertInstanceOf(A\WebinarPollOption::class, $aggregate->poll_questions[0]->options[0]);
        self::assertSame(0.5, $aggregate->poll_questions[0]->options[0]->percent);
        $audience = D\ShowWebinarAudienceResult::fromWire(json_decode(WebinarAnalyticsCases::fixture('ShowWebinarAudience')), 200)->data[0];
        self::assertInstanceOf(A\WebinarAudienceMember::class, $audience);
        self::assertSame('private', $audience->email);
        self::assertSame(0, $audience->played_time);
        self::assertInstanceOf(A\WebinarChatBucket::class, $audience->chats[0]);
        self::assertSame(['private'], $audience->chats[0]->chats);
        $histogram = D\ShowWebinarHistogramsResult::fromWire(json_decode('{"attendees_histogram":[0,0.5],"chat_histogram":[],"visual_focus_played_histogram":[1]}'), 200)->data;
        self::assertSame([0.0, 0.5], $histogram->attendees_histogram);
        self::assertSame([], $histogram->chat_histogram);
        $member = A\WebinarAudienceMember::fromWire(json_decode('{"attendee_histogram":[0,null,1],"focused_attendee_histogram":null,"chats":[]}'));
        self::assertSame([0,null,1], $member->attendee_histogram);
        self::assertNull($member->focused_attendee_histogram);
        self::assertSame([], $member->chats);
        $bucket = D\ShowWebinarRegistrationTimeseriesResult::fromWire(json_decode('[{"timestamp":"t","registrations":0,"completion_rate":null}]'), 200)->data[0];
        self::assertInstanceOf(A\WebinarRegistrationBucket::class, $bucket);
        self::assertSame('t', $bucket->timestamp); self::assertSame(0, $bucket->registrations);
        foreach (['utm_campaign','utm_source','utm_medium','referrer_domain'] as $dimension) {
            $group = D\ShowWebinarTrafficBreakdownResult::fromWire([(object) [$dimension=>null,'attendees'=>0]], 200)->data[0];
            self::assertInstanceOf(A\WebinarTrafficGroup::class, $group);
            self::assertContains($dimension, $group->presentFields); self::assertSame(0, $group->attendees);
        }
        foreach ([$aggregate, $aggregate->poll_questions[0], $aggregate->poll_questions[0]->options[0], $audience, $audience->chats[0]] as $record) {
            self::assertStringNotContainsString('private', print_r($record, true));
        }
    }
    public function testOptionalValuesAndEmptyListsRetainPresence(): void
    {
        foreach ([A\WebinarAnalytics::class, A\WebinarRegistrationBucket::class, A\WebinarTrafficGroup::class, A\WebinarAudienceMember::class, A\WebinarHistograms::class, A\WebinarPollQuestion::class, A\WebinarPollOption::class, A\WebinarChatBucket::class] as $class) {
            self::assertSame([], $class::fromWire((object) [])->presentFields);
        }
        $missing = A\WebinarAnalytics::fromWire((object) []);
        $null = A\WebinarAnalytics::fromWire((object) ['poll_questions'=>null]);
        self::assertNull($missing->poll_questions); self::assertNotContains('poll_questions', $missing->presentFields);
        self::assertNull($null->poll_questions); self::assertContains('poll_questions', $null->presentFields);
        self::assertSame([], A\WebinarAnalytics::fromWire((object) ['poll_questions'=>[]])->poll_questions);
        self::assertSame([], D\ShowWebinarRegistrationTimeseriesResult::fromWire([],200)->data);
        self::assertSame([], D\ShowWebinarTrafficBreakdownResult::fromWire([],200)->data);
        $envelope = D\ShowWebinarAudienceResult::fromWire((object) [],200);
        self::assertNull($envelope->data); self::assertNull($envelope->page_info); self::assertSame([], $envelope->presentFields);
    }
    #[DataProvider('badRecords')]
    public function testNestedFailuresAreSanitized(string $operation, #[\SensitiveParameter] string $body): void
    {
        [,$method,$input] = WebinarAnalyticsCases::all()[$operation];
        [$service,$transport] = self::service([$body]);
        try { $service->$method($input); self::fail('Expected failure.'); }
        catch (DecodeException $e) {
            self::assertNull($e->getPrevious()); self::assertCount(1,$transport->requests);
            self::assertStringNotContainsString('private',(string)$e);
            self::assertStringNotContainsString('synthetic-token',(string)$e);
        }
    }
    public static function badRecords(): array
    {
        return [
            ['ShowWebinarAnalytics','{"registrations":false}'],
            ['ShowWebinarAnalytics','{"total_watch_time":0.5}'],
            ['ShowWebinarAnalytics','{"engagement_rate":1e999}'],
            ['ShowWebinarAnalytics','{"poll_questions":[{"text":null}]}'],
            ['ShowWebinarAnalytics','{"poll_questions":[{"options":[{"count":"private"}]}]}'],
            ['ShowWebinarAnalytics','{"poll_questions":[{"options":[{"percent":false}]}]}'],
            ['ShowWebinarAnalytics','{"poll_questions":[{"unknown":"private"}]}'],
            ['ShowWebinarRegistrationTimeseries','[{"timestamp":null}]'],
            ['ShowWebinarRegistrationTimeseries','[{"completion_rate":false}]'],
            ['ShowWebinarTrafficBreakdown','[{"utm_source":42}]'],
            ['ShowWebinarTrafficBreakdown','[{"attendees":0.5}]'],
            ['ShowWebinarAudience','{"data":null}'],
            ['ShowWebinarAudience','{"page_info":null}'],
            ['ShowWebinarAudience','{"data":{}}'],
            ['ShowWebinarAudience','{"data":[{"email":42}]}'],
            ['ShowWebinarAudience','{"data":[{"played_time":0.5}]}'],
            ['ShowWebinarAudience','{"data":[{"focused_rate":1e999}]}'],
            ['ShowWebinarAudience','{"data":[{"attendee_histogram":[false]}]}'],
            ['ShowWebinarAudience','{"data":[{"chats":null}]}'],
            ['ShowWebinarAudience','{"data":[{"chats":[{"chats":[null]}]}]}'],
            ['ShowWebinarAudience','{"data":[{"chats":[{"rounded_player_position":0.5}]}]}'],
            ['ShowWebinarAudience','{"page_info":{"has_next_page":null}}'],
            ['ShowWebinarAudience','{"page_info":{"end_cursor":42}}'],
            ['ShowWebinarHistograms','{"attendees_histogram":null}'],
            ['ShowWebinarHistograms','{"chat_histogram":[null]}'],
            ['ShowWebinarHistograms','{"visual_focus_played_histogram":[1e999]}'],
        ];
    }
    public function testOmittedOptionsFalseAndDateBoundaries(): void
    {
        self::assertSame('', (new R\ShowWebinarAnalyticsRequest('w'))->query()->encode());
        self::assertSame('include_post_event=false', (new R\ShowWebinarAnalyticsRequest('w', false))->query()->encode());
        self::assertSame('granularity=daily', (new R\ShowWebinarRegistrationTimeseriesRequest('w','daily'))->query()->encode());
        self::assertSame('group_by=utm_source', (new R\ShowWebinarTrafficBreakdownRequest('w','utm_source'))->query()->encode());
        self::assertSame('', (new R\ShowWebinarAudienceRequest('w'))->query()->encode());
        // No undocumented two-year cap or include_post_event dependency is imposed.
        self::assertSame('post_event_start_date=2000-02-29&post_event_end_date=2026-03-01', (new R\ShowWebinarAnalyticsRequest('w',post_event_start_date:'2000-02-29',post_event_end_date:'2026-03-01'))->query()->encode());
        self::assertSame('post_event_end_date=2024-03-01', (new R\ShowWebinarAnalyticsRequest('w',post_event_end_date:'2024-03-01'))->query()->encode());
        foreach (['daily','weekly','monthly'] as $value) { new R\ShowWebinarRegistrationTimeseriesRequest('w',$value); }
        foreach (['utm_campaign','utm_source','utm_medium','referrer_domain'] as $value) { new R\ShowWebinarTrafficBreakdownRequest('w',$value); }
        foreach (['registrations','attendees','impressions'] as $value) { new R\ShowWebinarTrafficBreakdownRequest('w','utm_source',$value); }
        self::assertSame('per_page=1', (new R\ShowWebinarAudienceRequest('w',1))->query()->encode());
    }
    #[DataProvider('invalidRequests')]
    public function testInvalidInputIsRejectedBeforeExecution(string $class, #[\SensitiveParameter] array $args): void
    {
        $this->expectException(\InvalidArgumentException::class); new $class(...$args);
    }
    public static function invalidRequests(): array
    {
        $rows=[];
        foreach (WebinarAnalyticsCases::all() as [,, $request]) {
            $args=get_object_vars($request); $args['webinarId']=' '; $rows[]=[$request::class,$args];
        }
        foreach ([R\ShowWebinarAnalyticsRequest::class,R\ShowWebinarRegistrationTimeseriesRequest::class] as $class) {
            $base=['webinarId'=>'w']; if ($class===R\ShowWebinarRegistrationTimeseriesRequest::class) { $base['granularity']='daily'; }
            foreach ([['post_event_start_date'=>'2023-02-29'],['post_event_end_date'=>"2024-01-01\0"],['post_event_start_date'=>'2024-03-01','post_event_end_date'=>'2024-03-01'],['post_event_start_date'=>'2024-03-02','post_event_end_date'=>'2024-03-01']] as $bad) { $rows[]=[$class,[...$base,...$bad]]; }
        }
        return [...$rows,
            [R\ShowWebinarRegistrationTimeseriesRequest::class,['w','hourly']],
            [R\ShowWebinarTrafficBreakdownRequest::class,['w','viewer_screen_size']],
            [R\ShowWebinarTrafficBreakdownRequest::class,['w','utm_source','plays']],
            [R\ShowWebinarTrafficBreakdownRequest::class,['w','utm_source',null,'up']],
            [R\ShowWebinarAudienceRequest::class,['w',0]],
            [R\ShowWebinarAudienceRequest::class,['w',101]],
            [R\ShowWebinarAudienceRequest::class,['w',null,'']],
        ];
    }
    private static function service(array $bodies): array
    {
        $transport = new class($bodies) implements Transport {
            public array $requests = [];
            public function __construct(private array $bodies) {}
            public function send(TransportRequest $request): TransportResponse {
                $this->requests[] = $request;
                if ($this->bodies === []) { throw new \LogicException('Unexpected request.'); }
                return new TransportResponse(200, ['Content-Type'=>['application/json']], array_shift($this->bodies));
            }
        };
        return [new AnalyticsWebinarService(new Client(new Configuration('synthetic-token'), $transport)), $transport];
    }
    private static function envelope(array $data, bool $next, ?string $end): string
    {
        return json_encode(['data'=>$data, 'page_info'=>['has_next_page'=>$next, 'has_previous_page'=>true, 'end_cursor'=>$end, 'start_cursor'=>'private']], JSON_THROW_ON_ERROR);
    }
    public function testAudienceTraversalUsesPageInfoEvenForEmptyPagesAndPreservesFilters(): void
    {
        [$service,$transport]=self::service([self::envelope([],true,'next+/='), self::envelope([['email'=>'private','chats'=>[['rounded_player_position'=>0,'chats'=>['private']]]]],false,null)]);
        $request = new R\ShowWebinarAudienceRequest('m/a', 1, 'initial+/=');
        $paginator = $service->paginateWebinarAudience($request, 3);
        self::assertCount(0,$transport->requests);
        $items = iterator_to_array($paginator->items());
        self::assertCount(1,$items); self::assertInstanceOf(A\WebinarAudienceMember::class,$items[0]);
        self::assertCount(2,$transport->requests);
        $base='https://api.wistia.com/modern/analytics/webinars/m%2Fa/audience?per_page=1&cursor=';
        self::assertSame($base.'initial%2B%2F%3D',$transport->requests[0]->url);
        self::assertSame($base.'next%2B%2F%3D',$transport->requests[1]->url);
        self::assertStringNotContainsString('private',print_r($paginator,true));
        self::assertStringNotContainsString('private',print_r($items[0],true));
    }
    public function testIndividualPageReturnsTypedItemsAndMetadata(): void
    {
        [$service,$transport]=self::service([self::envelope([['email'=>null]],false,null)]);
        $page=$service->paginateWebinarAudience(new R\ShowWebinarAudienceRequest('m'))->page('manual+/=');
        self::assertInstanceOf(A\WebinarAudienceMember::class,$page->items[0]);
        self::assertFalse($page->hasNextPage); self::assertTrue($page->hasPreviousPage);
        self::assertStringEndsWith('?per_page=25&cursor=manual%2B%2F%3D',$transport->requests[0]->url);
        self::assertStringNotContainsString('private',print_r($page,true));
    }
    #[DataProvider('paginatorFailures')]
    public function testPaginatorFailuresDoNotRetainCursorBearingQueryArguments(array $bodies, string $exception): void
    {
        [$service, $transport] = self::service($bodies);
        try {
            $service->paginateWebinarAudience(new R\ShowWebinarAudienceRequest(
                'm', cursor: 'private-cursor'), 2)->page();
            self::fail('Expected paginator failure.');
        } catch (\Automattic\Wistia\Exception\WistiaException $e) {
            self::assertInstanceOf($exception, $e);
            self::assertCount(1, $transport->requests);
            self::assertNull($e->getPrevious());
            foreach ($e->getTrace() as $frame) {
                foreach ($frame['args'] ?? [] as $arg) {
                    // Debug suppression alone cannot protect a publicly readable Query in a trace.
                    self::assertNotInstanceOf(\Automattic\Wistia\Encoding\Query::class, $arg);
                    if (is_string($arg)) { self::assertStringNotContainsString('private-cursor', $arg); }
                }
            }
            self::assertStringNotContainsString('private-cursor', (string) $e);
        }
    }
    public static function paginatorFailures(): array
    {
        return [
            [['{"private":'], DecodeException::class],
            [[], \Automattic\Wistia\Exception\TransportException::class],
        ];
    }
    public function testInvalidPageBoundDoesNotCaptureCursorBearingStrategy(): void
    {
        [$service, $transport] = self::service([]);
        try {
            $service->paginateWebinarAudience(new R\ShowWebinarAudienceRequest(
                'm', cursor: 'private-cursor'), maxPages: 0);
            self::fail('Expected invalid page bound.');
        } catch (\InvalidArgumentException $e) {
            self::assertCount(0, $transport->requests);
            foreach ($e->getTrace() as $frame) {
                foreach ($frame['args'] ?? [] as $arg) {
                    self::assertNotInstanceOf(\Automattic\Wistia\Pagination\PaginationStrategy::class, $arg);
                    self::assertNotInstanceOf(R\ShowWebinarAudienceRequest::class, $arg);
                    if (is_string($arg)) { self::assertStringNotContainsString('private-cursor', $arg); }
                }
            }
            self::assertStringNotContainsString('private-cursor', (string) $e);
        }
    }
    public function testForwardTraversalDoesNotRequireOptionalReverseMetadata(): void
    {
        [$service, $transport] = self::service([
            '{"data":[],"page_info":{"has_next_page":true,"end_cursor":"next"}}',
            '{"data":[{"email":null}],"page_info":{"has_next_page":false}}',
        ]);
        $pages = iterator_to_array($service->paginateWebinarAudience(
            new R\ShowWebinarAudienceRequest('m'), 2)->pages());
        self::assertCount(2, $pages); self::assertCount(2, $transport->requests);
        self::assertFalse($pages[0]->hasPreviousPage); self::assertNull($pages[0]->startCursor);
        self::assertNull($pages[1]->endCursor); self::assertFalse($pages[1]->hasNextPage);
        self::assertInstanceOf(A\WebinarAudienceMember::class, $pages[1]->items[0]);
        self::assertStringEndsWith('&cursor=next', $transport->requests[1]->url);
    }
    #[DataProvider('badPagination')]
    public function testBoundedTraversalRejectsInvalidContinuation(array $bodies,int $max,string $exception,int $calls): void
    {
        [$service,$transport]=self::service($bodies);
        try {
            iterator_to_array($service->paginateWebinarAudience(new R\ShowWebinarAudienceRequest('m'),$max)->pages());
            self::fail('Expected continuation failure.');
        } catch (\UnexpectedValueException|\OverflowException $e) {
            self::assertInstanceOf($exception,$e); self::assertCount($calls,$transport->requests);
            self::assertStringNotContainsString('private',(string)$e);
        }
    }
    public static function badPagination(): array
    {
        return [
            [[self::envelope([],true,null)],3,\UnexpectedValueException::class,1],
            [[self::envelope([],true,'')],3,\UnexpectedValueException::class,1],
            [[self::envelope([],true,'private'),self::envelope([],true,'private')],3,\UnexpectedValueException::class,2],
            [[self::envelope([],true,'a'),self::envelope([],true,'private')],2,\OverflowException::class,2],
            [['{"data":[],"page_info":{}}'],3,\UnexpectedValueException::class,1],
        ];
    }
}

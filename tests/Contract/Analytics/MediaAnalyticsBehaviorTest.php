<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Analytics;

use Automattic\Wistia\{AnalyticsMediaService, Client, Configuration};
use Automattic\Wistia\Analytics as A;
use Automattic\Wistia\Request\Analytics as R;
use Automattic\Wistia\Result\Analytics as D;
use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class MediaAnalyticsBehaviorTest extends TestCase
{
    public function testTypedAggregateAndNestedResultsPreserveWireValues(): void
    {
        $metrics = D\ShowMediaAnalyticsResult::fromWire(json_decode(MediaAnalyticsCases::fixture('ShowMediaAnalytics')), 200)->data;
        self::assertInstanceOf(A\MediaAnalytics::class, $metrics);
        self::assertSame(0, $metrics->plays);
        self::assertSame(0, $metrics->cta_conversions);
        self::assertSame(0.5, $metrics->cta_conversion_rate);
        $null = A\MediaAnalytics::fromWire((object) ['plays' => null]);
        self::assertNull($null->plays); self::assertContains('plays', $null->presentFields);
        $missing = A\MediaAnalytics::fromWire((object) []);
        self::assertNull($missing->plays); self::assertNotContains('plays', $missing->presentFields);
        $bucket = D\ShowMediaAnalyticsTimeseriesResult::fromWire(json_decode(MediaAnalyticsCases::fixture('ShowMediaAnalyticsTimeseries')), 200)->data[0];
        self::assertInstanceOf(A\MediaAnalyticsBucket::class, $bucket);
        self::assertSame('2024-02-29T00:00:00Z', $bucket->timestamp);
        self::assertSame(0, $bucket->cta_impressions);
        $location = D\ShowMediaEmbedLocationsResult::fromWire(json_decode(MediaAnalyticsCases::fixture('ShowMediaEmbedLocations')), 200)->data[0];
        self::assertInstanceOf(A\MediaEmbedLocation::class, $location);
        self::assertSame(0.5, $location->played_time);
        self::assertSame(0, $location->unique_loads);
        $other = D\ShowMediaEmbedLocationsTimeseriesResult::fromWire(json_decode('[{"timestamp":"t","entries":[{"embed_domain":null,"embed_path":null,"embed_url":null,"page_title":"All other","plays":0}]}]'), 200)->data[0];
        self::assertInstanceOf(A\MediaEmbedLocationBucket::class, $other);
        self::assertSame('All other', $other->entries[0]->page_title);
        self::assertNull($other->entries[0]->embed_url);
        $language = D\ShowMediaLanguagesResult::fromWire(json_decode('[{"language":"en","plays":0,"percent_of_total_plays":0,"captions_support":false}]'), 200)->data[0];
        self::assertInstanceOf(A\MediaLanguage::class, $language);
        self::assertFalse($language->captions_support); self::assertSame(0.0, $language->percent_of_total_plays);
        foreach (['utm_campaign', 'utm_source', 'utm_medium', 'referrer_domain', 'viewer_screen_size'] as $dimension) {
            $group = D\ShowMediaTrafficBreakdownResult::fromWire([(object) [$dimension => null, 'plays' => 0]], 200)->data[0];
            self::assertInstanceOf(A\MediaTrafficGroup::class, $group);
            self::assertContains($dimension, $group->presentFields); self::assertNull($group->$dimension);
        }
        $conversion = D\ShowMediaFormConversionsResult::fromWire(json_decode(MediaAnalyticsCases::fixture('ShowMediaFormConversions')), 200)->data[0];
        self::assertInstanceOf(A\MediaFormConversion::class, $conversion);
        self::assertInstanceOf(A\MediaFormField::class, $conversion->custom_fields[0]);
        self::assertSame('private', $conversion->email);
        self::assertSame('private', $conversion->custom_fields[0]->value);
        foreach ([$conversion, $conversion->custom_fields[0], $location, $bucket] as $record) {
            self::assertStringNotContainsString('private', print_r($record, true));
        }
    }

    public function testEmptyArraysAndOptionalFieldsRemainDistinctFromNull(): void
    {
        foreach ([D\ShowMediaAnalyticsTimeseriesResult::class, D\ShowMediaEmbedLocationsResult::class,
            D\ShowMediaEmbedLocationsTimeseriesResult::class, D\ShowMediaTrafficBreakdownResult::class, D\ShowMediaLanguagesResult::class] as $class) {
            self::assertSame([], $class::fromWire([], 200)->data);
        }
        $fields = A\MediaFormConversion::fromWire((object) ['custom_fields' => []]);
        self::assertSame([], $fields->custom_fields);
        self::assertNull(A\MediaFormConversion::fromWire((object) [])->custom_fields);
        self::assertNotContains('custom_fields', A\MediaFormConversion::fromWire((object) [])->presentFields);
    }

    #[DataProvider('badRecords')]
    public function testNestedDecodeFailuresDoNotLeakVisitorData(string $operation, string $body): void
    {
        [, $method, $input] = MediaAnalyticsCases::all()[$operation];
        [$service, $transport] = self::service([$body]);
        try { $service->$method($input); self::fail('Expected decoding failure.'); }
        catch (DecodeException $e) {
            self::assertNull($e->getPrevious()); self::assertCount(1, $transport->requests);
            self::assertStringNotContainsString('private', (string) $e);
            self::assertStringNotContainsString('synthetic-token', (string) $e);
            self::assertFalse($e->remoteOutcomeUncertain);
        }
    }
    public static function badRecords(): array
    {
        return [
            ['ShowMediaAnalytics', '{"plays":"private"}'], ['ShowMediaAnalytics', '{"played_time":0.5}'],
            ['ShowMediaAnalytics', '{"cta_conversion_rate":1e999}'], ['ShowMediaAnalytics', '{"cta_conversions":false}'],
            ['ShowMediaAnalyticsTimeseries', '[{"timestamp":null}]'], ['ShowMediaAnalyticsTimeseries', '[{"plays":"private"}]'],
            ['ShowMediaEmbedLocations', '[{"embed_url":42}]'], ['ShowMediaEmbedLocations', '[{"played_time":false}]'],
            ['ShowMediaEmbedLocationsTimeseries', '[{"entries":null}]'], ['ShowMediaEmbedLocationsTimeseries', '[{"entries":[{"plays":"private"}]}]'],
            ['ShowMediaTrafficBreakdown', '[{"utm_source":42}]'], ['ShowMediaTrafficBreakdown', '[{"engagement_rate":1e999}]'],
            ['ShowMediaLanguages', '[{"captions_support":0}]'], ['ShowMediaLanguages', '[{"language":null}]'],
            ['ShowMediaLanguages', '[{"plays":0.5}]'], ['ShowMediaLanguages', '[{"percent_of_total_plays":"private"}]'],
            ['ShowMediaFormConversions', '{"data":[],"page_info":null}'],
            ['ShowMediaFormConversions', '{"data":null}'],
            ['ShowMediaFormConversions', '{"data":{}}'],
            ['ShowMediaFormConversions', '{"data":[{"custom_fields":null}],"page_info":{}}'],
            ['ShowMediaFormConversions', '{"data":[{"email":42}],"page_info":{}}'],
            ['ShowMediaFormConversions', '{"data":[{"converted_at":null}],"page_info":{}}'],
            ['ShowMediaFormConversions', '{"data":[{"custom_fields":[{"value":false}]}],"page_info":{}}'],
            ['ShowMediaFormConversions', '{"data":[{"custom_fields":[{"label":"private","unknown":"private"}]}],"page_info":{}}'],
            ['ShowMediaFormConversions', '{"data":[],"page_info":{"has_next_page":"private"}}'],
            ['ShowMediaFormConversions', '{"data":[],"page_info":{"end_cursor":false}}'],
            ['ShowMediaFormConversions', '{"data":[],"page_info":{"unknown":"private"}}'],
        ];
    }

    #[DataProvider('directMediaListFailures')]
    public function testDirectMediaListFactoryFailuresDoNotRetainPrivateArrays(string $class, #[\SensitiveParameter] string $body): void
    {
        try {
            if (in_array($class, [A\MediaFormConversion::class, A\MediaEmbedLocationBucket::class], true)) { $class::fromWire(json_decode($body)); }
            else { $class::fromWire(json_decode($body), 200); }
            self::fail('Expected malformed direct factory input.');
        } catch (\UnexpectedValueException $e) {
            self::assertNull($e->getPrevious());
            $libraryFrames = 0;
            foreach ($e->getTrace() as $frame) {
                // PHPUnit retains its provider dataset in caller frames; those are not decoder-owned.
                // Internal array_map calls from Wire::items() have a src/ call site and stay covered.
                if (!str_starts_with($frame['file'] ?? '', dirname(__DIR__, 3) . '/src/')) { continue; }
                ++$libraryFrames;
                foreach ($frame['args'] ?? [] as $arg) {
                    // Read captured arrays/objects directly: debug suppression is insufficient.
                    self::assertStringNotContainsString('private-', json_encode($arg, JSON_THROW_ON_ERROR));
                }
            }
            self::assertGreaterThan(0, $libraryFrames);
        }
    }
    public static function directMediaListFailures(): array
    {
        $traffic = [];
        foreach (['utm_campaign', 'utm_source', 'utm_medium', 'referrer_domain', 'viewer_screen_size'] as $field) {
            $traffic[] = [D\ShowMediaTrafficBreakdownResult::class, json_encode([[$field => 'private-dimension', 'plays' => false]], JSON_THROW_ON_ERROR)];
        }
        return [
            ...$traffic,
            [D\ShowMediaAnalyticsTimeseriesResult::class, '[{"timestamp":"private-timestamp","plays":false}]'],
            [D\ShowMediaEmbedLocationsResult::class, '[{"embed_url":"https://example.invalid/?token=private-token","plays":false}]'],
            [D\ShowMediaEmbedLocationsTimeseriesResult::class, '[{"timestamp":"private-timestamp","entries":[{"embed_url":"https://example.invalid/?token=private-token","plays":false}]}]'],
            [A\MediaEmbedLocationBucket::class, '{"entries":[{"embed_url":"https://example.invalid/?token=private-token","plays":false}]}'],
            [D\ShowMediaLanguagesResult::class, '[{"language":"private-language","plays":false}]'],
            [D\ShowMediaEmbedLocationsResult::class, '{"private-list":"private-url"}'],
            [A\MediaEmbedLocationBucket::class, '{"entries":{"private-list":"private-url"}}'],
            [A\MediaFormConversion::class, '{"email":"private-email","custom_fields":[{"label":"private-answer","value":false}]}'],
            [D\ShowMediaFormConversionsResult::class, '{"data":[{"email":"private-email","custom_fields":[{"label":"private-answer","value":false}]}],"page_info":{}}'],
            [D\ShowMediaFormConversionsResult::class, '{"data":[{"email":"private-email"},{"email":false}],"page_info":{}}'],
        ];
    }
    public function testConversionEnvelopePreservesOptionalOmission(): void
    {
        $missing = D\ShowMediaFormConversionsResult::fromWire((object) [], 200);
        self::assertNull($missing->data); self::assertNull($missing->page_info);
        self::assertSame([], $missing->presentFields);
        $dataOnly = D\ShowMediaFormConversionsResult::fromWire((object) ['data' => []], 200);
        self::assertSame([], $dataOnly->data); self::assertNull($dataOnly->page_info);
        self::assertSame(['data'], $dataOnly->presentFields);
        [$service, $transport] = self::service(['{"data":[]}']);
        $response = $service->showMediaFormConversions(new R\ShowMediaFormConversionsRequest('m', '2024-01-01', '2024-01-02'));
        self::assertSame([], $response->data); self::assertNull($response->page_info);
        self::assertCount(1, $transport->requests);
        $infoOnly = D\ShowMediaFormConversionsResult::fromWire((object) ['page_info' => (object) []], 200);
        self::assertNull($infoOnly->data); self::assertInstanceOf(A\MediaPageInfo::class, $infoOnly->page_info);
        self::assertSame(['page_info'], $infoOnly->presentFields);
        foreach ([$missing, $dataOnly, $infoOnly] as $result) {
            try { $result->page(); self::fail('Cannot traverse an incomplete envelope.'); }
            catch (\UnexpectedValueException $e) { self::assertSame('Incomplete pagination metadata.', $e->getMessage()); }
        }
    }
    public function testOptionalDefaultsAndSupportedEnums(): void
    {
        $args = ['m', '2024-02-29', '2026-02-28'];
        self::assertSame('start_date=2024-02-29&end_date=2026-02-28', (new R\ShowMediaAnalyticsRequest(...$args))->query()->encode());
        foreach (['daily', 'weekly', 'monthly'] as $granularity) {
            self::assertStringEndsWith('&granularity='.$granularity, (new R\ShowMediaAnalyticsTimeseriesRequest(...[...$args, $granularity]))->query()->encode());
        }
        foreach (['utm_campaign', 'utm_source', 'utm_medium', 'referrer_domain', 'viewer_screen_size'] as $dimension) {
            self::assertStringEndsWith('&group_by='.$dimension, (new R\ShowMediaTrafficBreakdownRequest(...[...$args, $dimension]))->query()->encode());
        }
        foreach (['plays', 'loads', 'engagement_rate', 'play_rate', 'played_time', 'unique_visitors'] as $sort) {
            self::assertStringEndsWith('&sort_by='.$sort, (new R\ShowMediaEmbedLocationsRequest(...[...$args, $sort]))->query()->encode());
        }
        self::assertStringEndsWith('&per_page=1', (new R\ShowMediaLanguagesRequest(...[...$args, 1]))->query()->encode());
        self::assertSame('start_date=2024-02-29&end_date=2026-02-28', (new R\ShowMediaFormConversionsRequest(...$args))->query()->encode());
        foreach (['example.invalid/p?next=https://other.invalid', 'example.invalid:8080/p/https://other.invalid'] as $url) {
            self::assertStringEndsWith('&embed_url='.rawurlencode($url), (new R\ShowMediaEmbedLocationsRequest(...[...$args, null, null, $url]))->query()->encode());
        }
    }
    #[DataProvider('invalidRequests')]
    public function testInvalidRequestFailsLocally(string $class, array $args): void
    {
        $this->expectException(\InvalidArgumentException::class); new $class(...$args);
    }
    public static function invalidRequests(): array
    {
        $rows = []; $a = ['m', '2024-01-01', '2024-01-02'];
        foreach (MediaAnalyticsCases::all() as [,, $request]) {
            $args = get_object_vars($request);
            foreach ([['mediaId',' '], ['start_date','2023-02-29'], ['start_date',"2024-01-01\0"],
                ['end_date','2024-02-29'], ['end_date','2026-03-01']] as [$key,$value]) {
                $copy = $args; $copy[$key] = $value; $rows[] = [$request::class,$copy];
            }
            if (array_key_exists('per_page', $args)) {
                foreach ([0,101] as $value) { $copy=$args; $copy['per_page']=$value; $rows[]=[$request::class,$copy]; }
            }
        }
        return [...$rows,
            [R\ShowMediaAnalyticsTimeseriesRequest::class, [...$a, 'hourly']],
            [R\ShowMediaEmbedLocationsRequest::class, [...$a, 'wrong']],
            [R\ShowMediaEmbedLocationsRequest::class, [...$a, 'plays', 'up']],
            [R\ShowMediaEmbedLocationsRequest::class, [...$a, null, null, 'ftp://example.invalid']],
            [R\ShowMediaEmbedLocationsRequest::class, [...$a, null, null, 'https://user:private@example.invalid']],
            [R\ShowMediaEmbedLocationsTimeseriesRequest::class, [...$a, 'daily', 'wrong']],
            [R\ShowMediaTrafficBreakdownRequest::class, [...$a, 'language']],
            [R\ShowMediaTrafficBreakdownRequest::class, [...$a, 'utm_source', 'played_time']],
            [R\ShowMediaTrafficBreakdownRequest::class, [...$a, 'utm_source', 'plays', 'up']],
            [R\ShowMediaFormConversionsRequest::class, [...$a, 25, '']],
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
        return [new AnalyticsMediaService(new Client(new Configuration('synthetic-token'), $transport)), $transport];
    }
    private static function envelope(array $data, bool $next, ?string $end): string
    {
        return json_encode(['data'=>$data, 'page_info'=>['has_next_page'=>$next, 'has_previous_page'=>true, 'end_cursor'=>$end, 'start_cursor'=>'private']], JSON_THROW_ON_ERROR);
    }
    public function testConversionTraversalUsesPageInfoEvenForEmptyPagesAndPreservesFilters(): void
    {
        [$service,$transport]=self::service([self::envelope([],true,'next+/='), self::envelope([['email'=>'private','custom_fields'=>[['label'=>'private','value'=>'private']]]],false,null)]);
        $request = new R\ShowMediaFormConversionsRequest('m/a', '2024-01-01', '2024-01-02', 1, 'initial+/=');
        $paginator = $service->paginateMediaFormConversions($request, 3);
        self::assertCount(0,$transport->requests);
        $items = iterator_to_array($paginator->items());
        self::assertCount(1,$items); self::assertInstanceOf(A\MediaFormConversion::class,$items[0]);
        self::assertCount(2,$transport->requests);
        $base='https://api.wistia.com/modern/analytics/medias/m%2Fa/conversions?start_date=2024-01-01&end_date=2024-01-02&per_page=1&cursor=';
        self::assertSame($base.'initial%2B%2F%3D',$transport->requests[0]->url);
        self::assertSame($base.'next%2B%2F%3D',$transport->requests[1]->url);
        self::assertStringNotContainsString('private',print_r($paginator,true));
        self::assertStringNotContainsString('private',print_r($items[0],true));
    }
    public function testIndividualPageReturnsTypedItemsAndMetadata(): void
    {
        [$service,$transport]=self::service([self::envelope([['email'=>null]],false,null)]);
        $page=$service->paginateMediaFormConversions(new R\ShowMediaFormConversionsRequest('m','2024-01-01','2024-01-02'))->page('manual+/=');
        self::assertInstanceOf(A\MediaFormConversion::class,$page->items[0]);
        self::assertFalse($page->hasNextPage); self::assertTrue($page->hasPreviousPage);
        self::assertStringEndsWith('&per_page=25&cursor=manual%2B%2F%3D',$transport->requests[0]->url);
        self::assertStringNotContainsString('private',print_r($page,true));
    }
    #[DataProvider('paginatorFailures')]
    public function testPaginatorFailuresDoNotRetainCursorBearingQueryArguments(array $bodies, string $exception): void
    {
        [$service, $transport] = self::service($bodies);
        try {
            $service->paginateMediaFormConversions(new R\ShowMediaFormConversionsRequest(
                'm', '2024-01-01', '2024-01-02', cursor: 'private-cursor'), 2)->page();
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
            $service->paginateMediaFormConversions(new R\ShowMediaFormConversionsRequest(
                'm', '2024-01-01', '2024-01-02', cursor: 'private-cursor'), maxPages: 0);
            self::fail('Expected invalid page bound.');
        } catch (\InvalidArgumentException $e) {
            self::assertCount(0, $transport->requests);
            foreach ($e->getTrace() as $frame) {
                foreach ($frame['args'] ?? [] as $arg) {
                    self::assertNotInstanceOf(\Automattic\Wistia\Pagination\PaginationStrategy::class, $arg);
                    self::assertNotInstanceOf(R\ShowMediaFormConversionsRequest::class, $arg);
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
        $pages = iterator_to_array($service->paginateMediaFormConversions(
            new R\ShowMediaFormConversionsRequest('m', '2024-01-01', '2024-01-02'), 2)->pages());
        self::assertCount(2, $pages); self::assertCount(2, $transport->requests);
        self::assertFalse($pages[0]->hasPreviousPage); self::assertNull($pages[0]->startCursor);
        self::assertNull($pages[1]->endCursor); self::assertFalse($pages[1]->hasNextPage);
        self::assertInstanceOf(A\MediaFormConversion::class, $pages[1]->items[0]);
        self::assertStringEndsWith('&cursor=next', $transport->requests[1]->url);
    }
    #[DataProvider('badPagination')]
    public function testBoundedTraversalRejectsInvalidContinuation(array $bodies,int $max,string $exception,int $calls): void
    {
        [$service,$transport]=self::service($bodies);
        try {
            iterator_to_array($service->paginateMediaFormConversions(new R\ShowMediaFormConversionsRequest('m','2024-01-01','2024-01-02'),$max)->pages());
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

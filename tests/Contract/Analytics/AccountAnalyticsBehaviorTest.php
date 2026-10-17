<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Analytics;

use Automattic\Wistia\{AnalyticsAccountService, Client, Configuration};
use Automattic\Wistia\Analytics\{AccountAnalytics, AccountAnalyticsBucket, AccountEmbedLocation, RankedMedia, RankedChannel, RankedProject};
use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Request\Analytics as R;
use Automattic\Wistia\Result\Analytics as D;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AccountAnalyticsBehaviorTest extends TestCase
{
    public function testTypedMetricsKeepZeroNullAndOmissionDistinct(): void
    {
        $data = D\ShowAccountAnalyticsResult::fromWire(json_decode(AccountAnalyticsCases::fixture('ShowAccountAnalytics')), 200)->data;
        self::assertSame(0, $data->unique_loads);
        self::assertSame(0.0, $data->play_rate);
        self::assertSame(0.5, $data->engagement_rate);
        self::assertNull($data->unique_plays);
        self::assertContains('unique_plays', $data->presentFields);
        $missing = AccountAnalytics::fromWire((object) []);
        self::assertNull($missing->unique_plays);
        self::assertNotContains('unique_plays', $missing->presentFields);
        $bucket = D\ShowAccountAnalyticsTimeseriesResult::fromWire(json_decode(AccountAnalyticsCases::fixture('ShowAccountAnalyticsTimeseries')), 200)->data[0];
        self::assertInstanceOf(AccountAnalyticsBucket::class, $bucket);
        self::assertSame('2024-02-29T00:00:00Z', $bucket->timestamp);
        self::assertSame(0, $bucket->plays);
        $location = D\ShowAccountEmbedLocationsResult::fromWire(json_decode(AccountAnalyticsCases::fixture('ShowAccountEmbedLocations')), 200)->data[0];
        self::assertInstanceOf(AccountEmbedLocation::class, $location);
        self::assertSame(0.5, $location->played_time);
        self::assertSame(0, $location->media_count);
        self::assertStringNotContainsString('private', print_r($location, true));
        $found = D\FindMediaByEmbedLocationResult::fromWire((object) ['media_hashed_ids' => []], 200);
        self::assertSame([], $found->media_hashed_ids);
        self::assertContains('media_hashed_ids', $found->presentFields);
        self::assertNull(D\FindMediaByEmbedLocationResult::fromWire((object) [], 200)->media_hashed_ids);
    }
    #[DataProvider('rankedRecords')]
    public function testRankingVariants(string $body, string $class, string $key, mixed $expected): void
    {
        $result = D\ShowAccountTopContentResult::fromWire(json_decode($body), 200);
        self::assertInstanceOf($class, $result->data[0]);
        self::assertSame($expected, $result->data[0]->$key);
        self::assertStringNotContainsString('private', print_r($result, true));
    }
    public static function rankedRecords(): array
    {
        return [
            [AccountAnalyticsCases::fixture('ShowAccountTopContent'), RankedMedia::class, 'played_time', 1.5],
            ['[{"channel_hashed_id":"private","channel_title":"private","channel_episode_count":0,"channel_created_at":null,"channel_url":null,"plays":0,"played_time":2}]', RankedChannel::class, 'played_time', 2.0],
            ['[{"project_hashed_id":null,"project_name":"private","project_created_at":null,"project_url":null,"project_video_count":0,"plays":null}]', RankedProject::class, 'project_video_count', 0],
        ];
    }
    public function testArrayEndpointsAcceptEmptyResultsWithoutInventedPagination(): void
    {
        foreach ([D\ShowAccountAnalyticsTimeseriesResult::class, D\ShowAccountTopContentResult::class, D\ShowAccountEmbedLocationsResult::class] as $class) {
            self::assertSame([], $class::fromWire([], 200)->data);
        }
    }
    #[DataProvider('badRecords')]
    public function testBadNestedRecordsAreSanitized(string $operation, string $body): void
    {
        [, $method, $input] = AccountAnalyticsCases::all()[$operation];
        $transport = new class($body) implements Transport {
            public int $calls = 0;
            public function __construct(private string $body) {}
            public function send(TransportRequest $request): TransportResponse {
                ++$this->calls;
                return new TransportResponse(200, ['Content-Type' => ['application/json']], $this->body);
            }
        };
        try {
            (new AnalyticsAccountService(new Client(new Configuration('synthetic-token'), $transport)))->$method($input);
            self::fail('Expected decoding failure.');
        } catch (DecodeException $e) {
            self::assertSame(1, $transport->calls);
            self::assertNull($e->getPrevious());
            self::assertStringNotContainsString('private', (string) $e);
            self::assertStringNotContainsString('synthetic-token', (string) $e);
            self::assertFalse($e->remoteOutcomeUncertain);
        }
    }
    public static function badRecords(): array
    {
        return [
            ['ShowAccountAnalytics', '{"plays":"private"}'], ['ShowAccountAnalytics', '{"play_rate":false}'],
            ['ShowAccountAnalytics', '{"played_time":1.5}'], ['ShowAccountAnalytics', '{"engagement_rate":1e999}'],
            ['ShowAccountAnalyticsTimeseries', '[{"timestamp":null}]'], ['ShowAccountAnalyticsTimeseries', '[{"unique_visitors":false}]'],
            ['ShowAccountAnalyticsTimeseries', '[null]'], ['ShowAccountTopContent', '[{}]'],
            ['ShowAccountTopContent', '[{"media_hashed_id":42}]'], ['ShowAccountTopContent', '[{"media_hashed_id":"m","channel_hashed_id":"c"}]'],
            ['ShowAccountTopContent', '[{"channel_hashed_id":"c","channel_episode_count":0.5}]'],
            ['ShowAccountTopContent', '[{"project_hashed_id":"p","project_video_count":"private"}]'],
            ['ShowAccountEmbedLocations', '[{"loads":"private"}]'], ['ShowAccountEmbedLocations', '[{"embed_domain":42}]'],
            ['FindMediaByEmbedLocation', '{"media_hashed_ids":[null]}'], ['FindMediaByEmbedLocation', '{"media_hashed_ids":{}}'],
            ['FindMediaByEmbedLocation', '{"media_hashed_ids":null}'],
        ];
    }
    public function testOptionalValuesAreOmittedAndServerOwnsRetentionClock(): void
    {
        self::assertSame('start_date=2024-01-01&end_date=2026-01-01', (new R\ShowAccountAnalyticsRequest('2024-01-01', '2026-01-01'))->query()->encode());
        self::assertSame('start_date=2024-02-29&end_date=2026-02-28', (new R\ShowAccountAnalyticsRequest('2024-02-29', '2026-02-28'))->query()->encode());
        self::assertSame('start_date=2024-01-01&end_date=2024-01-02', (new R\ShowAccountTopContentRequest('2024-01-01', '2024-01-02'))->query()->encode());
        self::assertSame('embed_url=example.invalid%2F', (new R\FindMediaByEmbedLocationRequest('example.invalid/'))->query()->encode());
        self::assertSame('embed_url=https%3A%2F%2Fexample.invalid%2Fp&end_date=2024-03-01', (new R\FindMediaByEmbedLocationRequest('https://example.invalid/p', end_date: '2024-03-01'))->query()->encode());
        self::assertSame('embed_url=example.invalid&start_date=2020-01-01', (new R\FindMediaByEmbedLocationRequest('example.invalid', start_date: '2020-01-01'))->query()->encode());
        self::assertStringEndsWith('&per_page=1', (new R\ShowAccountEmbedLocationsRequest('2024-01-01', '2024-01-02', per_page: 1))->query()->encode());
        self::assertStringEndsWith('&group_by=project', (new R\ShowAccountTopContentRequest('2024-01-01', '2024-01-02', group_by: 'project'))->query()->encode());
        self::assertSame(1000, substr_count((new R\ShowAccountTopContentRequest('2024-01-01', '2024-01-02', hashed_ids: array_fill(0, 1000, 'id')))->query()->encode(), 'hashed_ids%5B%5D='));
        foreach (['daily', 'weekly', 'monthly'] as $granularity) {
            self::assertStringEndsWith('&granularity=' . $granularity, (new R\ShowAccountAnalyticsTimeseriesRequest('2024-01-01', '2024-01-02', $granularity))->query()->encode());
        }
    }
    public function testSchemelessEmbedUrlMayContainAbsoluteUrlsInPathAndQuery(): void
    {
        foreach (['example.invalid/pricing?next=https://other.invalid', 'example.invalid/path/https://other.invalid', 'example.invalid:8080/pricing?next=https://other.invalid'] as $url) {
            $request = new R\FindMediaByEmbedLocationRequest($url);
            self::assertSame('embed_url=' . rawurlencode($url), $request->query()->encode());
        }
    }
    #[DataProvider('invalidRequests')]
    public function testInvalidInputBeforeTransport(string $class, array $args): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new $class(...$args);
    }
    public static function invalidRequests(): array
    {
        $dates = ['2024-01-01', '2024-01-02'];
        return [
            [R\ShowAccountAnalyticsRequest::class, ['2023-02-29', '2024-01-01']],
            [R\ShowAccountAnalyticsRequest::class, ["2024-01-01\0", '2024-01-02']],
            [R\ShowAccountAnalyticsRequest::class, ['0000-01-01', '2024-01-02']],
            [R\ShowAccountAnalyticsRequest::class, ['2024-01-01', '2024-01-01']],
            [R\ShowAccountAnalyticsRequest::class, ['2024-01-02', '2024-01-01']],
            [R\ShowAccountAnalyticsRequest::class, ['2024-01-01', '2026-01-02']],
            [R\ShowAccountAnalyticsRequest::class, ['2024-02-29', '2026-03-01']],
            [R\ShowAccountAnalyticsTimeseriesRequest::class, [...$dates, 'hourly']],
            [R\ShowAccountTopContentRequest::class, [...$dates, 'folder']],
            [R\ShowAccountTopContentRequest::class, [...$dates, 'channel', ['id']]],
            [R\ShowAccountTopContentRequest::class, [...$dates, 'project', []]],
            [R\ShowAccountTopContentRequest::class, [...$dates, 'media', ['bad' => 'id']]],
            [R\ShowAccountTopContentRequest::class, [...$dates, 'media', [42]]],
            [R\ShowAccountTopContentRequest::class, [...$dates, 'media', [' ']]],
            [R\ShowAccountTopContentRequest::class, [...$dates, 'media', array_fill(0, 1001, 'id')]],
            [R\ShowAccountTopContentRequest::class, [...$dates, 'media', null, 'unknown']],
            [R\ShowAccountTopContentRequest::class, [...$dates, 'media', null, 'plays', 'up']],
            [R\ShowAccountTopContentRequest::class, ['start_date' => $dates[0], 'end_date' => $dates[1], 'per_page' => 1001]],
            [R\ShowAccountTopContentRequest::class, ['start_date' => $dates[0], 'end_date' => $dates[1], 'per_page' => 0]],
            [R\ShowAccountEmbedLocationsRequest::class, [...$dates, 'wrong']],
            [R\ShowAccountEmbedLocationsRequest::class, [...$dates, 'plays', 'wrong']],
            [R\ShowAccountEmbedLocationsRequest::class, [...$dates, 'plays', 'asc', 101]],
            [R\ShowAccountEmbedLocationsRequest::class, [...$dates, 'plays', 'asc', 0]],
            [R\FindMediaByEmbedLocationRequest::class, ['']],
            [R\FindMediaByEmbedLocationRequest::class, ['ftp://example.invalid']],
            [R\FindMediaByEmbedLocationRequest::class, ['https://user:private@example.invalid']],
            [R\FindMediaByEmbedLocationRequest::class, ["example.invalid/\n"]],
            [R\FindMediaByEmbedLocationRequest::class, ['example.invalid', '2024-02-30']],
            [R\FindMediaByEmbedLocationRequest::class, ['example.invalid', ...$dates, 'wrong']],
            [R\FindMediaByEmbedLocationRequest::class, ['example.invalid', ...$dates, 'exact', 0]],
            [R\FindMediaByEmbedLocationRequest::class, ['example.invalid', ...$dates, 'exact', 1001]],
        ];
    }
}

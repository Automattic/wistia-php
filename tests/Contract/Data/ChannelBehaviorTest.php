<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{Client, Configuration, DataChannelsService, DataChannelCollaboratorsService, DataChannelEpisodesService};
use Automattic\Wistia\Channels\{Channel, ChannelEpisode, ChannelCollaborator, ChannelPodcastSettings, EpisodePodcastSettings};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Exception\DecodeException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ChannelBehaviorTest extends TestCase
{
    private function client(array $bodies): array
    {
        $transport = new class($bodies) implements Transport {
            public array $requests = [];
            public function __construct(private array $bodies) {}
            public function send(TransportRequest $request): TransportResponse
            {
                $this->requests[] = $request;
                if ($this->bodies === []) { throw new \LogicException('Unexpected request'); }
                return new TransportResponse(200, ['Content-Type'=>['application/json']], array_shift($this->bodies));
            }
        };
        return [new Client(new Configuration('synthetic-token'), $transport), $transport];
    }
    public static function lists(): iterable
    {
        foreach ([false,true] as $cursor) {
            $c = $cursor ? new R\Cursor(enabled:1) : null;
            yield 'channels '.(int)$cursor => [DataChannelsService::class, 'paginateChannels', new R\ListChannelsRequest(cursor:$c,per_page:2,sort_by:'created',sort_direction:0,hashed_ids:['one','two']), ChannelCases::CHANNEL, '/channels', ['hashed_ids'=>['one','two'],'sort_by'=>'created','sort_direction'=>'0'], $cursor];
            yield 'collaborators '.(int)$cursor => [DataChannelCollaboratorsService::class,'paginateChannelCollaborators',new R\ListChannelCollaboratorsRequest('parent/ +?',cursor:$c,per_page:2,sort_by:'id',sort_direction:0),ChannelCases::COLLABORATOR,'/channels/parent%2F%20%2B%3F/collaborators',['sort_by'=>'id','sort_direction'=>'0'],$cursor];
            yield 'nested episodes '.(int)$cursor => [DataChannelEpisodesService::class,'paginateChannelEpisodesByChannel',new R\ListChannelEpisodesByChannelRequest('parent/ +?',cursor:$c,per_page:2,sort_by:'id',sort_direction:0,media_id:['1','m'],hashed_ids:['one','two'],published:false,title:'private title'),ChannelCases::EPISODE,'/channels/parent%2F%20%2B%3F/channel_episodes',['media_id'=>['1','m'],'hashed_ids'=>['one','two'],'published'=>'false','title'=>'private title','sort_by'=>'id','sort_direction'=>'0'],$cursor];
            yield 'account episodes '.(int)$cursor => [DataChannelEpisodesService::class,'paginateChannelEpisodes',new R\ListChannelEpisodesRequest(channel_id:'parent',cursor:$c,per_page:2,sort_by:'created',sort_direction:0,media_id:['1','m'],hashed_ids:['one','two'],published:false,title:'private title'),ChannelCases::EPISODE,'/channel_episodes',['channel_id'=>'parent','media_id'=>['1','m'],'hashed_ids'=>['one','two'],'published'=>'false','title'=>'private title','sort_by'=>'created','sort_direction'=>'0'],$cursor];
        }
    }
    #[DataProvider('lists')]
    public function testTraversalRetainsEveryFilterAndStopsOnShortPage(string $class,string $method,object $request,string $fixture,string $path,array $filters,bool $cursor): void
    {
        [$client,$transport]=$this->client(['['.$fixture.','.str_replace('"next"','"second"',$fixture).']','['.str_replace('"next"','"third"',$fixture).']']);
        $pages=(new $class($client))->$method($request);
        self::assertCount(0,$transport->requests,'Traversal must be lazy');
        self::assertCount(3,iterator_to_array($pages->items()));
        self::assertCount(2,$transport->requests);
        foreach ($transport->requests as $r) {
            self::assertSame('https://api.wistia.com/modern'.$path,strtok($r->url,'?'));
            parse_str(parse_url($r->url,PHP_URL_QUERY),$q);
            foreach ($filters as $k=>$v) { self::assertSame($v,$q[$k]); }
            self::assertSame('2',$q['per_page']);
        }
        parse_str(parse_url($transport->requests[1]->url,PHP_URL_QUERY),$q);
        if ($cursor) { self::assertSame(['after'=>'second'],$q['cursor']); self::assertArrayNotHasKey('page',$q); }
        else { self::assertSame('2',$q['page']); self::assertArrayNotHasKey('cursor',$q); }
    }
    public function testBackwardCursorAndEmptyPage(): void
    {
        [$client,$transport]=$this->client(['['.ChannelCases::EPISODE.','.str_replace('"next"','"last"',ChannelCases::EPISODE).']','[]']);
        $pages=(new DataChannelEpisodesService($client))->paginateChannelEpisodes(new R\ListChannelEpisodesRequest(cursor:new R\Cursor(before:'initial'),per_page:2,sort_by:'id'));
        self::assertCount(2,iterator_to_array($pages->items()));
        self::assertStringContainsString('cursor%5Bbefore%5D=initial',$transport->requests[0]->url);
        self::assertStringContainsString('cursor%5Bbefore%5D=next',$transport->requests[1]->url);
    }
    public function testNonAdvancingCursorStops(): void
    {
        [$client,$transport]=$this->client(['['.ChannelCases::EPISODE.']','['.ChannelCases::EPISODE.']']);
        $pages=(new DataChannelEpisodesService($client))->paginateChannelEpisodes(new R\ListChannelEpisodesRequest(cursor:new R\Cursor(enabled:1),per_page:1));
        try { iterator_to_array($pages->items()); self::fail('Must detect repeated cursor'); }
        catch (\UnexpectedValueException) { self::assertCount(2,$transport->requests); }
    }
    public function testOmissionNullFalseAndZeroAreDistinct(): void
    {
        self::assertSame('{}',(new R\UpdateChannelRequest('channel'))->body()->contents());
        self::assertSame('{"name":null,"auto_publish_enabled":false}',(new R\UpdateChannelRequest('channel',name:null,auto_publish_enabled:false))->body()->contents());
        self::assertSame('{}',json_encode(new ChannelPodcastSettings()));
        self::assertEquals(json_decode('{"owner_email":null,"explicit":false}'),(new ChannelPodcastSettings(owner_email:null,explicit:false))->jsonSerialize());
        self::assertSame('{}',json_encode(new EpisodePodcastSettings()));
        self::assertSame('{"episode_number":0,"season_number":null,"explicit_content":false}',json_encode(new EpisodePodcastSettings(episode_number:0,season_number:null,explicit_content:false)));
    }
    public function testTypedNestedResultsPreserveWireFields(): void
    {
        $channel=Channel::fromWire(json_decode(ChannelCases::CHANNEL));
        self::assertSame('private@example.test',$channel->podcast_settings->owner_email);
        self::assertFalse($channel->podcast_settings->explicit);
        $episode=ChannelEpisode::fromWire(json_decode(ChannelCases::EPISODE));
        self::assertSame(0,$episode->podcast_settings->episode_number); self::assertNull($episode->podcast_settings->season_number);
        self::assertFalse($episode->published); self::assertSame('2027-01-01T00:00:00Z',$episode->publish_at);
        self::assertNull($episode->title); self::assertContains('title',$episode->presentFields);
        $collaborator=ChannelCollaborator::fromWire(json_decode(ChannelCases::COLLABORATOR));
        self::assertSame('viewer',$collaborator->role); self::assertSame('private@example.test',$collaborator->share->email);
        self::assertStringNotContainsString('private',print_r([$channel,$episode,$collaborator],true));
    }
    public static function invalidInputs(): iterable
    {
        yield [fn()=>new R\CreateChannelCollaboratorRequest('channel','invalid','viewer')];
        yield [fn()=>new R\CreateChannelCollaboratorRequest('channel','private@example.test','owner')];
        yield [fn()=>new R\DeleteChannelCollaboratorRequest('channel',0)];
        yield [fn()=>new R\ShowChannelRequest('')];
        yield [fn()=>new R\ListChannelsRequest(page:0)];
        yield [fn()=>new R\ListChannelsRequest(per_page:0)];
        yield [fn()=>new R\ListChannelsRequest(page:1,cursor:new R\Cursor(enabled:1))];
        yield [fn()=>new R\ListChannelsRequest(cursor:new R\Cursor(enabled:1),sort_by:'updated')];
        yield [fn()=>new R\ListChannelCollaboratorsRequest('channel',cursor:new R\Cursor(enabled:1),sort_by:'created')];
        yield [fn()=>new R\ListChannelEpisodesRequest(cursor:new R\Cursor(enabled:1),sort_by:'title')];
        yield [fn()=>new R\ListChannelEpisodesByChannelRequest('channel',cursor:new R\Cursor(enabled:1),sort_by:'published_at')];
        yield [fn()=>new R\ListChannelEpisodesRequest(media_id:['key'=>'value'])];
        yield [fn()=>new R\CreateChannelRequest(auto_publish_enabled:true,podcast_enabled:true)];
        yield [fn()=>new R\UpdateChannelRequest('channel',auto_publish_enabled:true,podcast_enabled:true)];
        yield [fn()=>new ChannelPodcastSettings(category1:'unknown')];
        yield [fn()=>new ChannelPodcastSettings(language:'unknown')];
        yield [fn()=>new EpisodePodcastSettings(episode_type:'unknown')];
        yield [fn()=>new R\CreateChannelEpisodeRequest('channel',publish_status:'scheduled')];
        yield [fn()=>new R\CreateChannelEpisodeRequest('channel',publish_status:'draft',publish_at:'2027-01-01T00:00:00Z')];
        yield [fn()=>new R\CreateChannelEpisodeRequest('channel',publish_status:'published',publish_at:'2027-01-01T00:00:00Z')];
        foreach (['2027-01-01T00:00:00+01:00','2027-02-30T00:00:00Z','tomorrow','2027-01-01T25:00:00Z'] as $at) {
            yield [fn()=>new R\PublishChannelEpisodeRequest('episode',publish_at:$at)];
            yield [fn()=>new R\UpdateChannelEpisodeRequest('episode',publish_at:$at)];
        }
    }
    #[DataProvider('invalidInputs')]
    public function testDetectableInvalidInputFailsBeforeTransport(\Closure $make): void
    {
        [$client,$transport]=$this->client([]);
        try { $make(); self::fail('Invalid input must fail'); }
        catch (\InvalidArgumentException $e) { self::assertCount(0,$transport->requests); self::assertStringNotContainsString('private',$e->getMessage()); }
    }
    public function testPublishingTransitionsAndImmediatePublish(): void
    {
        $draft=json_decode(ChannelCases::EPISODE); unset($draft->publish_at);
        $published=clone $draft; $published->published=true;
        [$client,$transport]=$this->client([json_encode($published),json_encode($draft),ChannelCases::EPISODE]);
        $service=new DataChannelEpisodesService($client);
        self::assertTrue($service->publishChannelEpisode(new R\PublishChannelEpisodeRequest('episode'))->data->published);
        self::assertSame('{}',$transport->requests[0]->body);
        self::assertFalse($service->unPublishChannelEpisode(new R\UnPublishChannelEpisodeRequest('episode'))->data->published);
        self::assertNull($transport->requests[1]->body);
        self::assertSame('2027-01-01T00:00:00Z',$service->updateChannelEpisode(new R\UpdateChannelEpisodeRequest('episode',publish_status:'scheduled',publish_at:'2027-01-01T00:00:00Z'))->data->publish_at);
        self::assertSame('{"publish_status":"scheduled","publish_at":"2027-01-01T00:00:00Z"}',$transport->requests[2]->body);
    }
    public static function invalidResponses(): iterable
    {
        $missing=json_decode(ChannelCases::EPISODE); unset($missing->published);
        yield [json_encode($missing)];
        $wrong=json_decode(ChannelCases::EPISODE); $wrong->published='false'; yield [json_encode($wrong)];
        $wrong=json_decode(ChannelCases::EPISODE); $wrong->podcast_settings->episode_number='0'; yield [json_encode($wrong)];
        $wrong=json_decode(ChannelCases::EPISODE); $wrong->podcast_settings->episode_type='unknown'; yield [json_encode($wrong)];
        $wrong=json_decode(ChannelCases::EPISODE); $wrong->cursor=1; yield [json_encode($wrong)];
        $wrong=json_decode(ChannelCases::EPISODE); $wrong->unexpected='private'; yield [json_encode($wrong)];
    }
    #[DataProvider('invalidResponses')]
    public function testInvalidNestedResponseDoesNotSucceed(string $body): void
    {
        [$client]=$this->client([$body]);
        $this->expectException(DecodeException::class);
        (new DataChannelEpisodesService($client))->showChannelEpisode(new R\ShowChannelEpisodeRequest('channel','episode'));
    }
}

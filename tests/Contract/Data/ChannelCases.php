<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{DataChannelsService, DataChannelCollaboratorsService, DataChannelEpisodesService};
use Automattic\Wistia\Channels\{ChannelPodcastSettings, EpisodePodcastSettings};
use Automattic\Wistia\Request\Data as R;

/** Hand-authored synthetic examples, not recorded Wistia responses. */
final class ChannelCases
{
    public const CHANNEL = '{"id":1,"created":"2026-10-16T00:00:00Z","description":"private description","hashed_id":"channel","media_count":0,"live_stream_event_count":0,"name":"private name","updated":"2026-10-16T00:00:00Z","auto_publish_enabled":false,"podcast_enabled":true,"custom_url":null,"podcast_settings":{"owner_name":"private owner","owner_email":"private@example.test","episode_format":"serial","explicit":false,"category1":"arts > books","language":"en"},"cursor":"next"}';
    public const COLLABORATOR = '{"id":2,"role":"viewer","created_at":"2026-10-16T00:00:00Z","share":{"id":3,"name":"private contact","type":"Contact","email":"private@example.test"},"cursor":"next"}';
    public const EPISODE = '{"id":4,"channel_hashed_id":"channel","created":"2026-10-16T00:00:00Z","description":"private notes","summary":"private summary","hashed_id":"episode","media_hashed_id":"media","live_stream_event_hashed_id":null,"published":false,"publish_at":"2027-01-01T00:00:00Z","title":null,"updated":"2026-10-16T00:00:00Z","podcast_settings":{"episode_type":"full","episode_number":0,"season_number":null,"explicit_content":false,"hide_from_feed":false},"cursor":"next"}';
    public static function all(): array
    {
        $c=DataChannelsService::class; $a=DataChannelCollaboratorsService::class; $e=DataChannelEpisodesService::class;
        return [
            'ListChannels'=>[$c,'listChannels',new R\ListChannelsRequest(page:2,per_page:1,sort_by:'name',sort_direction:0,hashed_ids:['a','b']),'GET','/channels?page=2&per_page=1&sort_by=name&sort_direction=0&hashed_ids%5B%5D=a&hashed_ids%5B%5D=b',null,200,'['.self::CHANNEL.']'],
            'CreateChannel'=>[$c,'createChannel',new R\CreateChannelRequest(name:'private channel',description:null,auto_publish_enabled:false,podcast_enabled:true,custom_url:null,podcast_settings:new ChannelPodcastSettings(owner_name:'private owner',owner_email:'private@example.test',explicit:false,category1:'arts > books',language:'en')),'POST','/channels','{"name":"private channel","description":null,"auto_publish_enabled":false,"podcast_enabled":true,"custom_url":null,"podcast_settings":{"owner_name":"private owner","owner_email":"private@example.test","explicit":false,"category1":"arts > books","language":"en"}}',201,self::CHANNEL],
            'ShowChannel'=>[$c,'showChannel',new R\ShowChannelRequest('id/ +?'),'GET','/channels/id%2F%20%2B%3F',null,200,self::CHANNEL],
            'UpdateChannel'=>[$c,'updateChannel',new R\UpdateChannelRequest('channel',name:null,auto_publish_enabled:false,podcast_settings:new ChannelPodcastSettings(owner_email:null)),'PUT','/channels/channel','{"name":null,"auto_publish_enabled":false,"podcast_settings":{"owner_email":null}}',200,self::CHANNEL],
            'DeleteChannel'=>[$c,'deleteChannel',new R\DeleteChannelRequest('channel'),'DELETE','/channels/channel',null,200,self::CHANNEL],
            'ListChannelCollaborators'=>[$a,'listChannelCollaborators',new R\ListChannelCollaboratorsRequest('id/ +?',sort_by:'updated',sort_direction:0),'GET','/channels/id%2F%20%2B%3F/collaborators?sort_by=updated&sort_direction=0',null,200,'['.self::COLLABORATOR.']'],
            'CreateChannelCollaborator'=>[$a,'createChannelCollaborator',new R\CreateChannelCollaboratorRequest('channel','private@example.test','viewer'),'POST','/channels/channel/collaborators','{"email":"private@example.test","role":"viewer"}',201,self::COLLABORATOR],
            'DeleteChannelCollaborator'=>[$a,'deleteChannelCollaborator',new R\DeleteChannelCollaboratorRequest('id/ +?',2),'DELETE','/channels/id%2F%20%2B%3F/collaborators/2',null,200,self::COLLABORATOR],
            'ShowChannelEpisode'=>[$e,'showChannelEpisode',new R\ShowChannelEpisodeRequest('id/ +?','ep/ +?'),'GET','/channels/id%2F%20%2B%3F/channel_episodes/ep%2F%20%2B%3F',null,200,self::EPISODE],
            'ListChannelEpisodesByChannel'=>[$e,'listChannelEpisodesByChannel',new R\ListChannelEpisodesByChannelRequest('id/ +?',sort_by:'title',sort_direction:0,page:2,per_page:1,media_id:['1','m'],hashed_ids:['a','b'],published:false,title:'private title'),'GET','/channels/id%2F%20%2B%3F/channel_episodes?sort_by=title&sort_direction=0&page=2&per_page=1&media_id%5B%5D=1&media_id%5B%5D=m&hashed_ids%5B%5D=a&hashed_ids%5B%5D=b&published=false&title=private%20title',null,200,'['.self::EPISODE.']'],
            'CreateChannelEpisode'=>[$e,'createChannelEpisode',new R\CreateChannelEpisodeRequest('channel',media_id:'media',title:'private title',publish_status:'scheduled',publish_at:'2027-01-01T00:00:00Z',podcast_settings:new EpisodePodcastSettings(episode_type:'full',episode_number:0,season_number:null,explicit_content:false,hide_from_feed:false)),'POST','/channels/channel/channel_episodes','{"media_id":"media","title":"private title","publish_status":"scheduled","publish_at":"2027-01-01T00:00:00Z","podcast_settings":{"episode_type":"full","episode_number":0,"season_number":null,"explicit_content":false,"hide_from_feed":false}}',200,self::EPISODE],
            'ListChannelEpisodes'=>[$e,'listChannelEpisodes',new R\ListChannelEpisodesRequest(channel_id:'channel',sort_by:'id',sort_direction:0,cursor:new R\Cursor(enabled:1),media_id:['1','m'],hashed_ids:['a','b'],published:false,title:'private title'),'GET','/channel_episodes?channel_id=channel&sort_by=id&sort_direction=0&cursor%5Benabled%5D=1&media_id%5B%5D=1&media_id%5B%5D=m&hashed_ids%5B%5D=a&hashed_ids%5B%5D=b&published=false&title=private%20title',null,200,'['.self::EPISODE.']'],
            'UpdateChannelEpisode'=>[$e,'updateChannelEpisode',new R\UpdateChannelEpisodeRequest('ep/ +?',description:null,title:'private updated',media_hashed_id:'media',live_stream_event_hashed_id:'live',summary:null,publish_status:'draft',episode_notes:'private notes'),'PUT','/channel_episodes/ep%2F%20%2B%3F','{"description":null,"title":"private updated","media_hashed_id":"media","live_stream_event_hashed_id":"live","summary":null,"publish_status":"draft","episode_notes":"private notes"}',200,self::EPISODE],
            'DeleteChannelEpisode'=>[$e,'deleteChannelEpisode',new R\DeleteChannelEpisodeRequest('episode'),'DELETE','/channel_episodes/episode',null,200,self::EPISODE],
            'PublishChannelEpisode'=>[$e,'publishChannelEpisode',new R\PublishChannelEpisodeRequest('episode',publish_at:'2027-01-01T00:00:00Z'),'PUT','/channel_episodes/episode/publish','{"publish_at":"2027-01-01T00:00:00Z"}',200,self::EPISODE],
            'UnPublishChannelEpisode'=>[$e,'unPublishChannelEpisode',new R\UnPublishChannelEpisodeRequest('episode'),'PUT','/channel_episodes/episode/unpublish',null,200,self::EPISODE],
        ];
    }
}

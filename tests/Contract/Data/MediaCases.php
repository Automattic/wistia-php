<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{DataMediaService, DataTrimsService, DataTagsService, DataTaggingsService, DataDeletedMediaService};
use Automattic\Wistia\Request\Data as R;

/** Synthetic fixtures and independently stated wire expectations, not inventory-generated responses. */
final class MediaCases
{
    public const JOB = '{"message":"queued","background_job_status":{"id":42,"hashed_id":"job-hash","status":"queued"}}';
    public const MEDIA = '{"id":7,"hashed_id":"media-hash","name":"private media","type":"Video","archived":false,"created":"2026-10-15T12:00:00Z","updated":"2026-10-15T12:00:00Z","duration":12.5,"embedCode":"private embed","description":"private description","progress":1,"status":"ready","section":null,"thumbnail":{"url":"https://example.test/thumb","width":100,"height":56},"protected":null}';
    public static function all(): array
    {
        $m = DataMediaService::class; $tag = DataTagsService::class; $del = DataDeletedMediaService::class;
        $job = self::JOB;
        return [
            'ListMedia' => [$m, 'listMedia', new R\ListMediaRequest(), 'GET', '/medias', null, 200, '['.substr(self::MEDIA, 0, -1).',"folder":null,"subfolder":null,"tags":[{"name":"topic"}],"assets":[{"url":"https://example.test/asset","width":null,"height":1080,"file_size":123,"content_type":"video/mp4","type":"OriginalFile"}],"speakers":[{"media_speaker_id":"ms","speaker_profile_id":"sp","name":"speaker"}],"cursor":"opaque +/"}]'],
            'ShowMedia' => [$m, 'showMedia', new R\ShowMediaRequest('id/ +?'), 'GET', '/medias/id%2F%20%2B%3F', null, 200, substr(self::MEDIA, 0, -1).',"folder":{"id":5,"name":"folder","hashed_id":"folder-hash"}}'],
            'UpdateMedia' => [$m, 'updateMedia', new R\UpdateMediaRequest('id/ +?', name:'changed', tags:[], custom_metadata:(object)['clear'=>null, 'zero'=>0]), 'PUT', '/medias/id%2F%20%2B%3F', '{"name":"changed","tags":[],"custom_metadata":{"clear":null,"zero":0}}', 200, self::MEDIA],
            'DeleteMedia' => [$m, 'deleteMedia', new R\DeleteMediaRequest('id/ +?'), 'DELETE', '/medias/id%2F%20%2B%3F', null, 200, self::MEDIA],
            'CopyMedia' => [$m, 'copyMedia', new R\CopyMediaRequest('id/ +?', folder_id:12, owner:'owner@example.test'), 'POST', '/medias/id%2F%20%2B%3F/copy', '{"folder_id":12,"owner":"owner@example.test"}', 201, substr(self::MEDIA, 0, -1).',"folder":null}'],
            'SwapMedia' => [$m, 'swapMedia', new R\SwapMediaRequest('id/ +?', 'replacement'), 'PUT', '/medias/id%2F%20%2B%3F/swap', '{"replacement_media_id":"replacement"}', 200, substr($job,0,-1).',"media":'.self::MEDIA.'}'],
            'ShowMediaAggregatedStats' => [$m,'showMediaAggregatedStats',new R\ShowMediaAggregatedStatsRequest('id/ +?'),'GET','/medias/id%2F%20%2B%3F/stats',null,200,'{"id":7,"hashed_id":"media-hash","name":"private media","stats":{"pageLoads":10,"visitors":8,"percentOfVisitorsClickingPlay":50,"plays":4,"averagePercentWatched":75}}'],
            'ImportMediaFromURL' => [$m,'importMediaFromURL',new R\ImportMediaFromURLRequest('https://example.test/video?sig=a%2Bb&x=1','folder-hash'),'POST','/medias/import_url','{"url":"https://example.test/video?sig=a%2Bb&x=1","folder_id":"folder-hash"}',201,$job],
            'ArchiveMedia' => [$m,'archiveMedia',new R\ArchiveMediaRequest(['one','two']),'PUT','/medias/archive','{"hashed_ids":["one","two"]}',200,$job],
            'MoveMedia' => [$m,'moveMedia',new R\MoveMediaRequest(['one','two'],'folder-hash','subfolder-hash'),'PUT','/medias/move','{"hashed_ids":["one","two"],"folder_id":"folder-hash","subfolder_id":"subfolder-hash"}',207,substr($job,0,-1).',"partial_errors":[{"media_id":"two","error":"private denied"}]}'],
            'RestoreMedia' => [$m,'restoreMedia',new R\RestoreMediaRequest(['one'],'folder-hash'),'PUT','/medias/restore','{"hashed_ids":["one"],"folder_id":"folder-hash"}',200,substr($job,0,-1).',"container":{"type":"Folder","name":"folder","hashedId":"folder-hash"}}'],
            'BulkCopyMedia' => [$m,'bulkCopyMedia',new R\BulkCopyMediaRequest(['one'],'folder-hash'),'PUT','/medias/copy','{"hashed_ids":["one"],"folder_id":"folder-hash"}',200,substr($job,0,-1).',"destination":{"type":"Folder","name":"folder","hashedId":"folder-hash"}}'],
            'CreateMediaFromTrims' => [DataTrimsService::class,'createMediaFromTrims',new R\CreateMediaFromTrimsRequest('id/ +?', ['00:00:01.000-00:00:03.500'], false),'POST','/medias/id%2F%20%2B%3F/trims','{"trims":["00:00:01.000-00:00:03.500"],"keep_trims":false}',200,'{"background_job_status":{"id":42,"hashed_id":"job-hash","status":"queued"}}'],
            'ListTags' => [$tag,'listTags',new R\ListTagsRequest(),'GET','/tags',null,200,'[{"name":"topic","taggings_count":2,"created_at":"2026-10-15T12:00:00Z","updated_at":"2026-10-15T12:00:00Z","cursor":null}]'],
            'CreateTags' => [$tag,'createTags',new R\CreateTagsRequest('Topic'),'POST','/tags','{"name":"Topic"}',200,'{"name":"topic","taggings_count":0}'],
            'DeleteTag' => [$tag,'deleteTag',new R\DeleteTagRequest('tag/ +?'),'DELETE','/tags/tag%2F%20%2B%3F',null,200,''],
            'BulkTagMedia' => [DataTaggingsService::class,'bulkTagMedia',new R\BulkTagMediaRequest(['one'],['a','b']),'POST','/taggings/bulk_create','{"hashed_ids":["one"],"tag_names":["a","b"]}',200,$job],
            'ListDeletedMedia' => [$del,'listDeletedMedia',new R\ListDeletedMediaRequest(),'GET','/deleted_media',null,200,'[{"hashed_id":"one","name":"private media","type":"Video","duration":null,"deleted_at":"2026-10-15T12:00:00Z","restore_expires_at":"2026-11-14T12:00:00Z","folder":{"hashed_id":"folder-hash","name":null},"cursor":"next"}]'],
            'RestoreDeletedMedia' => [$del,'restoreDeletedMedia',new R\RestoreDeletedMediaRequest(['one']),'POST','/deleted_media/restore','{"media_hashed_ids":["one"]}',202,$job],
        ];
    }
}

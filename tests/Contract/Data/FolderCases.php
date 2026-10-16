<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{DataFoldersService, DataFolderSharingsService, DataSubfoldersService};
use Automattic\Wistia\Folders\{CreateSharing, UpdateSharing};
use Automattic\Wistia\Request\Data as R;

/** Independently stated synthetic fixtures and wire assertions, not live responses. */
final class FolderCases
{
    public const FOLDER = '{"id":1,"name":"private folder","description":null,"media_count":0,"medias":{"url":"https://example.test/medias"},"created":"2026-10-16T00:00:00Z","updated":"2026-10-16T00:00:00Z","hashed_id":"folder","public":false,"public_id":null,"anonymous_can_upload":false,"anonymous_can_download":false,"kind":"shared","personal_library":false,"cursor":"next"}';
    public const SHARING = '{"id":2,"is_admin":false,"can_share":false,"can_download":true,"can_upload":false,"share":{"id":3,"name":"private name","type":"Contact","email":"private@example.test"},"folder":{"id":1,"name":"private folder"},"cursor":"next"}';
    public const SUBFOLDER = '{"hashed_id":"sub","name":"private subfolder","description":null,"position":0,"created":"2026-10-16T00:00:00Z","updated":"2026-10-16T00:00:00Z","cursor":"next"}';
    public const JOB = '{"id":4,"hashed_id":"job","status":"queued"}';
    public const QUEUED = '{"message":"queued","background_job_status":{"id":4,"hashed_id":"job","status":"queued"}}';
    public const MEDIA = '{"id":5,"name":"private media","type":"Video","archived":false,"created":"2026-10-16T00:00:00Z","updated":"2026-10-16T00:00:00Z","duration":12.5,"embedCode":"private embed","hashed_id":"media","description":"private description","progress":0.5,"status":"processing","section":null,"thumbnail":{"url":"https://example.test/thumb","width":100,"height":50},"protected":null}';
    public static function all(): array
    {
        $f=DataFoldersService::class; $s=DataFolderSharingsService::class; $u=DataSubfoldersService::class;
        return [
            'ListFolders'=>[$f,'listFolders',new R\ListFoldersRequest(sort_by:'name',sort_direction:0,hashed_ids:['a','b']),'GET','/folders?sort_by=name&sort_direction=0&hashed_ids%5B%5D=a&hashed_ids%5B%5D=b',null,200,'['.self::FOLDER.']'],
            'CreateFolder'=>[$f,'createFolder',new R\CreateFolderRequest(name:'private folder',adminEmail:'manager@example.test',description:'',anonymousCanUpload:false,anonymousCanDownload:true,public:false,personalLibrary:true),'POST','/folders','{"name":"private folder","adminEmail":"manager@example.test","description":"","anonymousCanUpload":false,"anonymousCanDownload":true,"public":false,"personalLibrary":true}',201,self::FOLDER],
            'ShowFolder'=>[$f,'showFolder',new R\ShowFolderRequest('id/ +?'),'GET','/folders/id%2F%20%2B%3F',null,200,self::FOLDER],
            'UpdateFolder'=>[$f,'updateFolder',new R\UpdateFolderRequest('folder',name:'renamed',description:'',anonymousCanUpload:false,anonymousCanDownload:false,public:false),'PUT','/folders/folder','{"name":"renamed","description":"","anonymousCanUpload":false,"anonymousCanDownload":false,"public":false}',200,self::FOLDER],
            'DeleteFolder'=>[$f,'deleteFolder',new R\DeleteFolderRequest('folder'),'DELETE','/folders/folder',null,200,self::FOLDER],
            'CopyFolder'=>[$f,'copyFolder',new R\CopyFolderRequest('folder','manager@example.test'),'POST','/folders/folder/copy','{"adminEmail":"manager@example.test"}',202,self::JOB],
            'ListFolderSharings'=>[$s,'listFolderSharings',new R\ListFolderSharingsRequest('folder',sort_by:'updated',sort_direction:0,hashed_ids:['a','b']),'GET','/folders/folder/sharings?sort_by=updated&sort_direction=0&hashed_ids%5B%5D=a&hashed_ids%5B%5D=b',null,200,'['.self::SHARING.']'],
            'CreateFolderSharing'=>[$s,'createFolderSharing',new R\CreateFolderSharingRequest('folder',new CreateSharing('private@example.test',false,false,false,false,'0')),'POST','/folders/folder/sharings','{"sharing":{"with":"private@example.test","requirePassword":false,"canShare":false,"canDownload":false,"canUpload":false,"sendEmailNotification":"0"}}',201,'{"project":"https://example.test/folder","sharing":'.self::SHARING.'}'],
            'ShowFolderSharing'=>[$s,'showFolderSharing',new R\ShowFolderSharingRequest('id/ +?',2),'GET','/folders/id%2F%20%2B%3F/sharings/2',null,200,self::SHARING],
            'UpdateFolderSharing'=>[$s,'updateFolderSharing',new R\UpdateFolderSharingRequest('id/ +?','share/ +?',new UpdateSharing(false,false,false,false)),'PUT','/folders/id%2F%20%2B%3F/sharings/share%2F%20%2B%3F','{"sharing":{"canShare":false,"canDownload":false,"canUpload":false,"isAdmin":false}}',200,self::SHARING],
            'DeleteFolderSharing'=>[$s,'deleteFolderSharing',new R\DeleteFolderSharingRequest('folder','2'),'DELETE','/folders/folder/sharings/2',null,200,self::SHARING],
            'ListSubfolders'=>[$u,'listSubfolders',new R\ListSubfoldersRequest('folder',sort_by:'position',sort_direction:0,hashed_ids:['a','b']),'GET','/folders/folder/subfolders?sort_by=position&sort_direction=0&hashed_ids%5B%5D=a&hashed_ids%5B%5D=b',null,200,'['.self::SUBFOLDER.']'],
            'CreateSubfolder'=>[$u,'createSubfolder',new R\CreateSubfolderRequest('folder','private subfolder',null),'POST','/folders/folder/subfolders','{"name":"private subfolder","description":null}',201,self::SUBFOLDER],
            'ShowSubfolder'=>[$u,'showSubfolder',new R\ShowSubfolderRequest('id/ +?','sub/ +?','markdown'),'GET','/folders/id%2F%20%2B%3F/subfolders/sub%2F%20%2B%3F?description_format=markdown',null,200,substr(self::SUBFOLDER,0,-1).',"medias":['.self::MEDIA.']}'],
            'UpdateSubfolder'=>[$u,'updateSubfolder',new R\UpdateSubfolderRequest('folder','sub',name:'',description:null),'PUT','/folders/folder/subfolders/sub','{"name":"","description":null}',200,self::SUBFOLDER],
            'DeleteSubfolder'=>[$u,'deleteSubfolder',new R\DeleteSubfolderRequest('folder','sub'),'DELETE','/folders/folder/subfolders/sub',null,200,self::SUBFOLDER],
            'BulkDeleteSubfolders'=>[$u,'bulkDeleteSubfolders',new R\BulkDeleteSubfoldersRequest('folder',['one','two']),'DELETE','/folders/folder/subfolders/bulk_delete','{"hashed_ids":["one","two"]}',200,self::QUEUED],
        ];
    }
}

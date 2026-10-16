<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{DataWebinarsService, DataWebinarCollaboratorsService, DataWebinarRegistrationsService};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Webinars\WebinarUpdate;

/** Synthetic documented wire shapes; never obtained from a Wistia account. */
final class WebinarCases
{
    public static function webinar(): string
    {
        return '{"id":"abc123","hashed_id":"abc123","title":"Workshop","description":null,"scheduled_for":"2026-11-01T15:00:00Z","event_duration":60,"time_zone":"America/New_York","lifecycle_status":"pending","registration_status":"open","created_at":"2026-10-01T00:00:00Z","updated_at":"2026-10-01T00:00:00Z","audience_link":"https://example.test/audience","host_link":"https://example.test/host","panelist_link":"https://example.test/panelist","thumbnail_url":null,"folder":{"id":"folder1","index_url":"https://example.test/folders","url":"https://example.test/folders/folder1"},"cursor":"next"}';
    }
    public static function collaborator(): string
    {
        return '{"id":1,"role":"admin","created_at":"2026-10-01T00:00:00Z","share":{"id":2,"name":"Producer","type":"Contact","email":"producer@example.test"},"cursor":"next"}';
    }
    public static function registrations(bool $more = false, string $cursor = 'next', bool $previous = false): string
    {
        return json_encode(['registrations'=>[['visitor_key'=>'private-visitor','email'=>'registrant@example.test','first_name'=>'A','last_name'=>'B','attended_at'=>null,'restriction_status'=>'allowed','engagement_rate'=>0.5,'focused_rate'=>0,'played_time'=>0,'form_field_data'=>[['label'=>'Company','value'=>'Example']], 'chats'=>[['rounded_player_position'=>20,'chats'=>['Hello']]],'qa_questions'=>[['rounded_player_position'=>30,'qa_questions'=>[['question_text'=>'When?']]]],'poll_responses'=>[['poll_question_text'=>null,'poll_option_text'=>'Yes']],'attendee_histogram'=>[1,null,0],'focused_attendee_histogram'=>null]],'page_info'=>['has_next_page'=>$more,'has_previous_page'=>$previous,'end_cursor'=>$cursor,'start_cursor'=>$cursor]], JSON_THROW_ON_ERROR);
    }
    public static function all(): array
    {
        $w=self::webinar(); $c=self::collaborator();
        return [
            'ListWebinars'=>[DataWebinarsService::class,'listWebinars',new R\ListWebinarsRequest(page:2,per_page:10,sort_by:'title',sort_direction:0,hashed_ids:['a','b'],started:'false'),'GET','/webinars?page=2&per_page=10&sort_by=title&sort_direction=0&hashed_ids%5B%5D=a&hashed_ids%5B%5D=b&started=false',null,200,'['.$w.']'],
            'CreateWebinar'=>[DataWebinarsService::class,'createWebinar',new R\CreateWebinarRequest('Workshop','2026-11-01T15:00:00Z',60,'America/New_York',description:'',folder_id:'folder1'),'POST','/webinars','{"title":"Workshop","scheduled_for":"2026-11-01T15:00:00Z","event_duration":60,"time_zone":"America/New_York","description":"","folder_id":"folder1"}',201,$w],
            'ShowWebinar'=>[DataWebinarsService::class,'showWebinar',new R\ShowWebinarRequest('a/b'),'GET','/webinars/a%2Fb',null,200,$w],
            'UpdateWebinar'=>[DataWebinarsService::class,'updateWebinar',new R\UpdateWebinarRequest('a/b',new WebinarUpdate(title:'Revised',description:'',scheduled_for:'2026-11-02T00:00:00+00:00',event_duration:15,time_zone:'UTC',folder_id:'folder2')),'PUT','/webinars/a%2Fb','{"webinar":{"title":"Revised","description":"","scheduled_for":"2026-11-02T00:00:00+00:00","event_duration":15,"time_zone":"UTC","folder_id":"folder2"}}',200,$w],
            'DeleteWebinar'=>[DataWebinarsService::class,'deleteWebinar',new R\DeleteWebinarRequest('a/b'),'DELETE','/webinars/a%2Fb',null,200,$w],
            'ListWebinarCollaborators'=>[DataWebinarCollaboratorsService::class,'listWebinarCollaborators',new R\ListWebinarCollaboratorsRequest('a/b',page:2,per_page:10,sort_by:'created',sort_direction:0),'GET','/webinars/a%2Fb/collaborators?page=2&per_page=10&sort_by=created&sort_direction=0',null,200,'['.$c.']'],
            'CreateWebinarCollaborator'=>[DataWebinarCollaboratorsService::class,'createWebinarCollaborator',new R\CreateWebinarCollaboratorRequest('a/b','producer@example.test'),'POST','/webinars/a%2Fb/collaborators','{"email":"producer@example.test"}',201,$c],
            'DeleteWebinarCollaborator'=>[DataWebinarCollaboratorsService::class,'deleteWebinarCollaborator',new R\DeleteWebinarCollaboratorRequest('a/b',1),'DELETE','/webinars/a%2Fb/collaborators/1',null,200,$c],
            'ListWebinarRegistrations'=>[DataWebinarRegistrationsService::class,'listWebinarRegistrations',new R\ListWebinarRegistrationsRequest('a/b',per_page:10,cursor:'x/y',sort_direction:0,attendance:'attendees',restriction:'allowed',emails:['a@example.test','b@example.test']),'GET','/webinars/a%2Fb/registrations?per_page=10&cursor=x%2Fy&sort_direction=0&attendance=attendees&restriction=allowed&emails%5B%5D=a%40example.test&emails%5B%5D=b%40example.test',null,200,self::registrations()],
            'CreateWebinarRegistration'=>[DataWebinarRegistrationsService::class,'createWebinarRegistration',new R\CreateWebinarRegistrationRequest('a/b','registrant@example.test','A','B'),'POST','/webinars/a%2Fb/registrations','{"email":"registrant@example.test","first_name":"A","last_name":"B"}',201,'{"visitor_key":"private-visitor","personalized_event_url":"https://example.test/event?visitor=private-visitor"}'],
        ];
    }
}

<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{Client, Configuration, DataWebinarsService, DataWebinarCollaboratorsService, DataWebinarRegistrationsService};
use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as Result;
use Automattic\Wistia\Webinars\{WebinarUpdate, Webinar, WebinarCollaborator, WebinarRegistration, RegistrationChatGroup, RegistrationQuestion, RegistrationFormField};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class WebinarBehaviorTest extends TestCase
{
    private function client(array $bodies, int $status = 200): array
    {
        $transport = new class($bodies, $status) implements Transport {
            public array $requests = [];
            public function __construct(private array $bodies, private int $status) {}
            public function send(TransportRequest $request): TransportResponse {
                $this->requests[] = $request;
                if ($this->bodies === []) { throw new \LogicException('Unexpected extra request.'); }
                return new TransportResponse($this->status,['Content-Type'=>['application/json']],array_shift($this->bodies));
            }
        };
        return [new Client(new Configuration('synthetic-token'),$transport),$transport];
    }
    #[DataProvider('invalidInputs')]
    public function testInvalidInputsAreLocal(callable $build): void
    {
        [$client,$transport]=$this->client([]);
        try { $request=$build(); (new DataWebinarsService($client))->listWebinars($request); self::fail('Expected invalid input'); }
        catch (\InvalidArgumentException $e) {
            self::assertCount(0,$transport->requests);
            self::assertStringNotContainsString('secret', $e->getMessage());
            self::assertNull($e->getPrevious());
        }
    }
    public static function invalidInputs(): array
    {
        return [
            [fn()=>new R\CreateWebinarRequest('Title','2026-02-30T00:00:00Z',15,'UTC')],
            [fn()=>new R\CreateWebinarRequest('Title','2026-11-01T00:00:00-05:00',15,'UTC')],
            [fn()=>new R\CreateWebinarRequest('Title','2026-11-01',15,'UTC')],
            [fn()=>new R\CreateWebinarRequest('Title','2026-11-01T00:00:00Z',14,'UTC')],
            [fn()=>new R\CreateWebinarRequest('Title','2026-11-01T00:00:00Z',15,'secret-zone')],
            [fn()=>new WebinarUpdate(scheduled_for:'secret')], [fn()=>new WebinarUpdate(event_duration:0)],
            [fn()=>new WebinarUpdate(time_zone:'+05:00')], [fn()=>new WebinarUpdate(folder_id:'')],
            [fn()=>new R\ShowWebinarRequest(' ')], [fn()=>new R\UpdateWebinarRequest('')],
            [fn()=>new R\DeleteWebinarCollaboratorRequest('webinar',0)],
            [fn()=>new R\CreateWebinarCollaboratorRequest('webinar','secret')],
            [fn()=>new R\CreateWebinarRegistrationRequest('webinar','secret','A','B')],
            [fn()=>new R\ListWebinarsRequest(page:0)], [fn()=>new R\ListWebinarsRequest(per_page:0)],
            [fn()=>new R\ListWebinarsRequest(page:1,cursor:new R\Cursor(enabled:1))],
            [fn()=>new R\ListWebinarsRequest(cursor:new R\Cursor(enabled:1),sort_by:'title')],
            [fn()=>new R\ListWebinarsRequest(cursor:new R\Cursor(enabled:1),sort_by:'updated')],
            [fn()=>new R\ListWebinarsRequest(sort_direction:2)], [fn()=>new R\ListWebinarsRequest(started:'yes')],
            [fn()=>new R\ListWebinarsRequest(hashed_ids:[1])],
            [fn()=>new R\ListWebinarCollaboratorsRequest('webinar',cursor:new R\Cursor(enabled:1),sort_by:'created')],
            [fn()=>new R\ListWebinarRegistrationsRequest('webinar',per_page:101)],
            [fn()=>new R\ListWebinarRegistrationsRequest('webinar',cursor:'')],
            [fn()=>new R\ListWebinarRegistrationsRequest('webinar',attendance:'secret')],
            [fn()=>new R\ListWebinarRegistrationsRequest('webinar',restriction:'secret')],
            [fn()=>new R\ListWebinarRegistrationsRequest('webinar',emails:['secret'])],
        ];
    }
    public function testMinimalAndNestedUpdatesPreserveOmissionAndEmptyText(): void
    {
        self::assertSame('{}',(new R\UpdateWebinarRequest('webinar'))->body()->contents());
        self::assertSame('{"webinar":{}}',(new R\UpdateWebinarRequest('webinar',new WebinarUpdate()))->body()->contents());
        self::assertSame('{"webinar":{"description":""}}',(new R\UpdateWebinarRequest('webinar',new WebinarUpdate(description:'')))->body()->contents());
        $request = new R\CreateWebinarRequest('Title','2026-11-01T00:00:00.123+00:00',15,'UTC');
        self::assertSame('{"title":"Title","scheduled_for":"2026-11-01T00:00:00.123+00:00","event_duration":15,"time_zone":"UTC"}',$request->body()->contents());
    }
    public function testNestedTypedRecordsAndNullableFields(): void
    {
        $webinar=Result\ShowWebinarResult::fromWire(json_decode(WebinarCases::webinar()),200)->data;
        self::assertInstanceOf(Webinar::class,$webinar); self::assertSame('folder1',$webinar->folder->id);
        self::assertNull($webinar->description); self::assertContains('description',$webinar->presentFields);
        $collaborator=Result\CreateWebinarCollaboratorResult::fromWire(json_decode(WebinarCases::collaborator()),201)->data;
        self::assertInstanceOf(WebinarCollaborator::class,$collaborator); self::assertSame('producer@example.test',$collaborator->share->email);
        $group=json_decode(WebinarCases::collaborator()); $group->share->type='ContactGroup'; unset($group->share->email);
        self::assertNull(Result\DeleteWebinarCollaboratorResult::fromWire($group,200)->data->share->email);
        $data=Result\ListWebinarRegistrationsResult::fromWire(json_decode(WebinarCases::registrations()),200)->data;
        $person=$data->registrations[0]; self::assertInstanceOf(WebinarRegistration::class,$person);
        self::assertInstanceOf(RegistrationChatGroup::class,$person->chats[0]); self::assertSame(['Hello'],$person->chats[0]->chats);
        self::assertInstanceOf(RegistrationQuestion::class,$person->qa_questions[0]->qa_questions[0]);
        self::assertInstanceOf(RegistrationFormField::class,$person->form_field_data[0]);
        self::assertSame([1,null,0],$person->attendee_histogram); self::assertSame(0.0,$person->focused_rate);
        self::assertNull($person->attended_at); self::assertContains('attended_at',$person->presentFields);
        self::assertNotContains('company',$person->presentFields); self::assertNull($person->company);
        self::assertStringNotContainsString('private-visitor',print_r($data,true));
        self::assertSame(1,count($data->page()->items));
    }
    #[DataProvider('invalidResponses')]
    public function testMalformedNestedResultsFailAsDecodeErrors(string $operation, string $body): void
    {
        [$class,$method,$request,,,,$status]=WebinarCases::all()[$operation];
        [$client]=$this->client([$body], $status); $service=new $class($client);
        $this->expectException(DecodeException::class);$service->$method($request);
    }
    public static function invalidResponses(): array
    {
        $w=json_decode(WebinarCases::webinar());$w->lifecycle_status='invented';
        $missing=json_decode(WebinarCases::webinar());unset($missing->title);
        $folder=json_decode(WebinarCases::webinar());$folder->folder=(object)['id'=>'x'];
        $collab=json_decode(WebinarCases::collaborator());$collab->role='viewer';
        return [
            ['ShowWebinar',json_encode($w)],['ShowWebinar',json_encode($missing)],['ShowWebinar',json_encode($folder)],
            ['CreateWebinarCollaborator',json_encode($collab)],
            ['ListWebinarRegistrations','{"registrations":[{"chats":[{"chats":[1]}]}]}'],
            ['ListWebinarRegistrations','{"registrations":[{"form_field_data":[{"value":null}]}]}'],
            ['ListWebinarRegistrations','{"registrations":[{"qa_questions":[{"qa_questions":[{"question_text":12}]}]}]}'],
            ['ListWebinarRegistrations','{"registrations":[{"poll_responses":[{"poll_option_text":2}]}]}'],
            ['ListWebinarRegistrations','{"registrations":[{"attendee_histogram":["bad"]}]}'],
            ['ListWebinarRegistrations','{"registrations":[{"restriction_status":"blocked"}]}'],
            ['ListWebinarRegistrations','{"page_info":{"has_next_page":1}}'],
            ['CreateWebinarRegistration','{"visitor_key":"private"}'],
        ];
    }
    public function testScalarRegistrationTraversalPreservesFiltersInBothDirections(): void
    {
        foreach ([0,1] as $direction) {
            [$client,$transport]=$this->client([WebinarCases::registrations($direction===1,'n/1',$direction===0),WebinarCases::registrations(false,'last',false)]);
            $request=new R\ListWebinarRegistrationsRequest('a/b',per_page:1,sort_direction:$direction,attendance:'attendees',restriction:'allowed',emails:['a@example.test','b@example.test']);
            $paginator=(new DataWebinarRegistrationsService($client))->paginateWebinarRegistrations($request);
            self::assertCount(0,$transport->requests);$people=iterator_to_array($paginator->items());
            self::assertCount(2,$people);self::assertInstanceOf(WebinarRegistration::class,$people[0]);self::assertCount(2,$transport->requests);
            foreach ($transport->requests as $wire) {
                self::assertStringContainsString('/webinars/a%2Fb/registrations?', $wire->url);
                self::assertStringContainsString('sort_direction='.$direction,$wire->url);
                self::assertStringContainsString('attendance=attendees&restriction=allowed&emails%5B%5D=a%40example.test&emails%5B%5D=b%40example.test',$wire->url);
                self::assertStringNotContainsString('cursor%5B', $wire->url);
            }
            self::assertStringContainsString('cursor=n%2F1',$transport->requests[1]->url);
        }
    }
    public function testRecordCursorAndOffsetTraversal(): void
    {
        foreach ([false,true] as $cursorMode) {
            [$client,$transport]=$this->client(['['.WebinarCases::webinar().']','[]']);
            $request=new R\ListWebinarsRequest(per_page:1,cursor:$cursorMode ? new R\Cursor(enabled:1) : null,sort_by:'scheduled_for',sort_direction:0,hashed_ids:['abc123'],started:'false');
            $items=iterator_to_array((new DataWebinarsService($client))->paginateWebinars($request)->items());
            self::assertCount(1,$items);self::assertCount(2,$transport->requests);
            self::assertStringContainsString($cursorMode ? 'cursor%5Bafter%5D=next' : 'page=2',$transport->requests[1]->url);
            foreach ($transport->requests as $wire) { self::assertStringContainsString('sort_by=scheduled_for&sort_direction=0&hashed_ids%5B%5D=abc123&started=false',$wire->url); }
        }
        [$client,$transport]=$this->client(['['.WebinarCases::collaborator().']','[]']);
        $items=iterator_to_array((new DataWebinarCollaboratorsService($client))->paginateWebinarCollaborators(new R\ListWebinarCollaboratorsRequest('a/b',per_page:1,cursor:new R\Cursor(before:'start'),sort_by:'id'))->items());
        self::assertCount(1,$items); self::assertStringContainsString('cursor%5Bbefore%5D=next',$transport->requests[1]->url);
    }
    public function testRegistrationEmptyPageTerminates(): void
    {
        [$client,$transport]=$this->client(['{"registrations":[],"page_info":{"has_next_page":false,"has_previous_page":false,"end_cursor":null,"start_cursor":null}}']);
        self::assertSame([],iterator_to_array((new DataWebinarRegistrationsService($client))->paginateWebinarRegistrations(new R\ListWebinarRegistrationsRequest('x'))->items()));
        self::assertCount(1,$transport->requests);
    }
    public function testRegistrationRepeatedCursorAndExhaustionAreFinite(): void
    {
        foreach ([false,true] as $bounded) {
            [$client,$transport]=$this->client([WebinarCases::registrations(true),WebinarCases::registrations(true)]);
            $p=(new DataWebinarRegistrationsService($client))->paginateWebinarRegistrations(new R\ListWebinarRegistrationsRequest('x'),maxPages:$bounded?1:5);
            try { iterator_to_array($p->items());self::fail('Expected finite traversal failure'); }
            catch (\OverflowException|\UnexpectedValueException $e) { self::assertCount($bounded?1:2,$transport->requests); }
        }
    }
    public function testContinuingRegistrationPageRequiresCursor(): void
    {
        [$client,$transport]=$this->client(['{"registrations":[],"page_info":{"has_next_page":true,"has_previous_page":false,"end_cursor":null,"start_cursor":null}}']);
        $this->expectException(\UnexpectedValueException::class);
        iterator_to_array((new DataWebinarRegistrationsService($client))->paginateWebinarRegistrations(new R\ListWebinarRegistrationsRequest('x'))->items());
    }
    public function testMissingPaginationMetadataIsNotSilentCompletion(): void
    {
        [$client]=$this->client(['{"registrations":[]}']);
        $this->expectException(\UnexpectedValueException::class);
        iterator_to_array((new DataWebinarRegistrationsService($client))->paginateWebinarRegistrations(new R\ListWebinarRegistrationsRequest('x'))->items());
    }
}

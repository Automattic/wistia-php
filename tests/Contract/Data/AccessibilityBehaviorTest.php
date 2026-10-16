<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{Client, Configuration, DataCaptionsService, DataSpeakersService, DataExtendedAudioDescriptionsService, DataLocalizationsService};
use Automattic\Wistia\Accessibility\CaptionEdit;
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Exception\{ApiException, DecodeException};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AccessibilityBehaviorTest extends TestCase
{
    private function client(array $responses): array
    {
        $transport = new class($responses) implements Transport {
            public array $requests = [];
            public function __construct(private array $responses) {}
            public function send(TransportRequest $request): TransportResponse {
                $this->requests[] = $request;
                if ($this->responses === []) { throw new \LogicException('Unexpected extra request.'); }
                [$body,$type,$status] = array_shift($this->responses);
                return new TransportResponse($status, ['Content-Type'=>[$type]], $body);
            }
        };
        return [new Client(new Configuration('synthetic-token'), $transport), $transport];
    }
    public static function textFormats(): array
    {
        return [[R\CaptionFormat::Srt, 'text/plain; charset=utf-8', "1\r\n00:00:00,000 --> 00:00:01,000\r\n中文\r\n"],
            [R\CaptionFormat::Vtt, 'text/vtt', "WEBVTT\n\n00:00.000 --> 00:01.000\nHi\n"],
            [R\CaptionFormat::Txt, 'text/plain', "Speaker 2: 中文\n"], [R\CaptionFormat::Txt, 'text/plain', '']];
    }
    #[DataProvider('textFormats')]
    public function testRawFormatsPreserveBytes(R\CaptionFormat $format, string $type, string $bytes): void
    {
        [$client,$t]=$this->client([[$bytes,$type,200]]);
        $result=(new DataCaptionsService($client))->showCaptions(new R\ShowCaptionsRequest('media','zh-Hant',include_speakers:true,format:$format));
        self::assertSame($bytes,$result->data); self::assertSame($format,$result->format);
        self::assertSame('https://api.wistia.com/modern/medias/media/captions/zh-Hant.'.$format->value.'?include_speakers=true',$t->requests[0]->url);
    }
    public function testTextFormatDoesNotAcceptJsonOrHtmlAsTranscript(): void
    {
        foreach (['application/json','text/html','text/vtt'] as $type) {
            [$client,$t]=$this->client([['private error',$type,200]]);
            try { (new DataCaptionsService($client))->showCaptions(new R\ShowCaptionsRequest('media','eng',format:R\CaptionFormat::Srt)); self::fail('Expected decode failure'); }
            catch (DecodeException $e) { self::assertCount(1,$t->requests); self::assertStringNotContainsString('private',$e->getMessage()); }
        }
    }
    public function testCaptionMetadataAndDiarizationAreTyped(): void
    {
        $caption=json_decode(AccessibilityCases::CAPTION); unset($caption->text); $caption->segments=null; $caption->version=null;
        $caption->diarized_segments=[(object)['start_ms'=>0,'end_ms'=>100,'text'=>'private turn','speaker'=>(object)['media_speaker_id'=>null,'speaker_profile_id'=>null,'detected_speaker_id'=>'detected','display_label'=>'Speaker 2','name'=>null]]];
        $caption->diarization_status='processing'; $caption->diarization_derivable=true; $caption->speaker_data_version=null;
        [$client]=$this->client([[json_encode($caption),'application/json',200]]);
        $result=(new DataCaptionsService($client))->showCaptions(new R\ShowCaptionsRequest('media','eng',include:'diarized_segments'));
        self::assertNull($result->data->text); self::assertNotContains('text',$result->data->presentFields);
        self::assertSame('processing',$result->data->diarization_status);
        self::assertSame('detected',$result->data->diarized_segments[0]->speaker->detected_speaker_id);
        self::assertNull($result->data->speaker_data_version);
        self::assertStringNotContainsString('private',print_r($result,true));
    }
    public function testMalformedNestedCaptionIsRejected(): void
    {
        $caption=json_decode(AccessibilityCases::CAPTION); $caption->segments[0]->start_ms='0';
        [$client]=$this->client([['['.json_encode($caption).']','application/json',200]]);
        $this->expectException(DecodeException::class);
        (new DataCaptionsService($client))->listCaptions(new R\ListCaptionsRequest());
    }
    public function testMatchesKeepExactFuzzyAndPerMediaOutcomesSeparate(): void
    {
        $wire=json_decode(AccessibilityCases::MATCH); $hit=$wire->results[0];
        $hit->match_status='resolved'; $hit->match_count=12; $hit->matches_truncated=true; $hit->next_occurrence=11;
        $hit->matches=[(object)['text'=>'exact','context'=>'context','start_ms'=>10,'end_ms'=>20]];
        [$client]=$this->client([[json_encode($wire),'application/json',200]]);
        $data=(new DataCaptionsService($client))->findCaptionMatches(new R\FindCaptionMatchesRequest(['media','inaccessible'],'exact'))->data;
        self::assertSame(1,$data->failed_count); self::assertTrue($data->complete);
        self::assertSame('media_not_found',$data->results[1]->status);
        self::assertSame('resolved',$data->results[0]->match_status); self::assertSame(11,$data->results[0]->next_occurrence);
        self::assertSame('exact',$data->results[0]->matches[0]->text);
        self::assertSame('private fuzzy text',$data->results[0]->suggestions[0]->text);
        self::assertSame(3,$data->results[0]->track->version);
    }
    public function testSearchPreservesIndependentOptionalBounds(): void
    {
        $start = new R\FindCaptionMatchesRequest(['a'], 'word', start_ms: 0);
        self::assertSame('{"media_ids":["a"],"target_text":"word","start_ms":0}', $start->body()->contents());
        $end = new R\FindCaptionMatchesRequest(['a'], 'word', end_ms: 1000);
        self::assertSame('{"media_ids":["a"],"target_text":"word","end_ms":1000}', $end->body()->contents());
    }
    public function testAtomicEditVersionAndEmptyReplacement(): void
    {
        [$client,$t]=$this->client([[AccessibilityCases::EDIT,'application/json',200]]);
        $result=(new DataCaptionsService($client))->editCaptionsText(new R\EditCaptionsTextRequest('composite','eng',[new CaptionEdit('old','',0,1000)],3));
        self::assertTrue($result->data->applied); self::assertSame(3,$result->data->version->from); self::assertSame(4,$result->data->version->to);
        self::assertSame('source',$result->data->media_id); self::assertNull($result->data->edits[0]->replacement);
        self::assertSame('',json_decode($t->requests[0]->body)->edits[0]->replacement_text);
    }
    public function testConflictsAndUnprocessableEditsNeverReplay(): void
    {
        foreach ([409,422] as $status) {
            [$client,$t]=$this->client([['{"error":"private transcript details"}','application/json',$status]]);
            try { (new DataCaptionsService($client))->editCaptionsText(new R\EditCaptionsTextRequest('media','eng',[new CaptionEdit('old','new')],3)); self::fail('Expected failure'); }
            catch (ApiException $e) { self::assertSame($status,$e->status); self::assertFalse($e->remoteOutcomeUncertain); self::assertCount(1,$t->requests); self::assertStringNotContainsString('private',$e->getMessage()); }
        }
    }
    public static function invalidRequests(): array
    {
        return [
            [static fn()=>new R\FindCaptionMatchesRequest([], 'a')],
            [static fn()=>new R\FindCaptionMatchesRequest(['a','a'], 'a')],
            [static fn()=>new R\FindCaptionMatchesRequest(array_map(strval(...),range(1,51)), 'a')],
            [static fn()=>new R\FindCaptionMatchesRequest(['a'],str_repeat('中',501))],
            [static fn()=>new R\FindCaptionMatchesRequest(['a'],'a',occurrence:0)],
            [static fn()=>new R\FindCaptionMatchesRequest(['a'],'a',start_ms:-1)],
            [static fn()=>new R\FindCaptionMatchesRequest(['a'],'a',start_ms:10,end_ms:0)],
            [static fn()=>new CaptionEdit('a','b',null,10)], [static fn()=>new CaptionEdit('a','b',-1,10)],
            [static fn()=>new R\EditCaptionsTextRequest('m','eng',[],1)],
            [static fn()=>new R\EditCaptionsTextRequest('m','eng',array_fill(0,21,new CaptionEdit('a','b')),1)],
            [static fn()=>new R\EditCaptionsTextRequest('m','eng',[['target_text'=>'a','replacement_text'=>'b']],1)],
            [static fn()=>new R\ListCaptionsRequest(page:1,cursor:new R\Cursor())],
            [static fn()=>new R\ListCaptionsRequest(cursor:new R\Cursor(enabled:1),sort_by:'language')],
            [static fn()=>new R\ListCaptionsRequest(per_page:0)],
            [static fn()=>new R\ListSpeakersRequest(sort_direction:2)],
            [static fn()=>new R\ShowCaptionsRequest('m','eng',include:'invalid')],
            [static fn()=>new R\CreateCaptionsRequest('m','')],
            [static fn()=>new R\ShowLocalizationRequest('m','..')],
        ];
    }
    #[DataProvider('invalidRequests')]
    public function testInvalidLocalInputSendsNothing(\Closure $construct): void
    {
        [$client,$t]=$this->client([]);
        try { $construct(); self::fail('Expected local validation'); }
        catch (\InvalidArgumentException) { self::assertCount(0,$t->requests); }
    }
    public static function paginatedServices(): array
    {
        return [
            [DataCaptionsService::class,'paginateCaptions',new R\ListCaptionsRequest(media_ids:['a','b'],languages:['eng'],include:'metadata',per_page:1,cursor:new R\Cursor(enabled:1),sort_by:'id',sort_direction:0),AccessibilityCases::CAPTION,'media_ids%5B%5D=a&media_ids%5B%5D=b&languages%5B%5D=eng&include=metadata&sort_by=id&sort_direction=0'],
            [DataSpeakersService::class,'paginateSpeakers',new R\ListSpeakersRequest(name:'A B',sort_by:'name',per_page:1,cursor:new R\Cursor(before:'first')),'{"speaker_profile_id":"p","name":"private speaker","title":null,"image_url":null,"cursor":"next"}','name=A%20B&sort_by=name'],
            [DataExtendedAudioDescriptionsService::class,'paginateMediaExtendedAudioDescriptions',new R\ListMediaExtendedAudioDescriptionsRequest(hashed_ids:['a','b'],per_page:1,cursor:new R\Cursor(enabled:1)),AccessibilityCases::AUDIO,'hashed_ids%5B%5D=a&hashed_ids%5B%5D=b'],
        ];
    }
    #[DataProvider('paginatedServices')]
    public function testCursorTraversalPreservesFilters(string $service,string $method,object $request,string $record,string $filters): void
    {
        [$client,$t]=$this->client([['['.$record.']','application/json',200],['[]','application/json',200]]);
        $pages=(new $service($client))->$method($request,3); self::assertCount(0,$t->requests);
        $items=iterator_to_array($pages->items()); self::assertCount(1,$items); self::assertCount(2,$t->requests);
        foreach ($t->requests as $sent) { self::assertStringContainsString($filters,$sent->url); }
        $backwards=$request instanceof R\ListSpeakersRequest;
        self::assertStringContainsString($backwards?'cursor%5Bbefore%5D=first':'cursor%5Benabled%5D=1',$t->requests[0]->url);
        self::assertStringContainsString($backwards?'cursor%5Bbefore%5D=next':'cursor%5Bafter%5D=next',$t->requests[1]->url);
    }
    public function testOffsetTraversalPreservesFiltersAndStartsAtRequestedPage(): void
    {
        [$client,$t]=$this->client([['['.AccessibilityCases::CAPTION.']','application/json',200],['[]','application/json',200]]);
        $items=iterator_to_array((new DataCaptionsService($client))->paginateCaptions(new R\ListCaptionsRequest(media_id:'one',page:2,per_page:1))->items());
        self::assertCount(1,$items);
        self::assertStringContainsString('media_id=one&per_page=1&page=2',$t->requests[0]->url);
        self::assertStringContainsString('media_id=one&per_page=1&page=3',$t->requests[1]->url);
    }
    public function testLocalizationTranscriptAndResultLinksAreTyped(): void
    {
        $loc=json_decode(AccessibilityCases::LOCALIZATION); $loc->transcript=(object)['hashed_id'=>'t','language_name'=>'Chinese','native_language_name'=>'中文','text'=>'private text','created_at'=>'date','updated_at'=>'date'];
        [$client]=$this->client([[json_encode($loc),'application/json',200]]);
        $result=(new DataLocalizationsService($client))->showLocalization(new R\ShowLocalizationRequest('m','l',true));
        self::assertSame('private text',$result->data->transcript->text); self::assertSame(12.5,$result->data->source_media->duration);
        self::assertStringNotContainsString('private',print_r($result,true));
        foreach (['in_progress','completed','cancelled','failed'] as $state) {
            $order=json_decode(AccessibilityCases::ORDER); $order->order->order_status=$state;
            if ($state==='completed') { $order->order->media_extended_audio_description=(object)['id'=>'audio','index_url'=>'https://example.test/audio','url'=>'https://example.test/audio/one']; }
            [$client,$t]=$this->client([[json_encode($order),'application/json',200]]);
            $data=(new DataExtendedAudioDescriptionsService($client))->getOrderStatus(new R\GetOrderStatusRequest('existing-order'))->data;
            self::assertSame($state,$data->order->order_status); self::assertCount(1,$t->requests);
            self::assertSame($state==='completed'?'audio':null,$data->order->media_extended_audio_description?->id);
        }
    }
}

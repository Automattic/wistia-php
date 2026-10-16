<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{DataCaptionsService, DataSpeakersService, DataLocalizationsService, DataExtendedAudioDescriptionsService};
use Automattic\Wistia\Accessibility\CaptionEdit;
use Automattic\Wistia\Request\Data as R;

/** Independently stated synthetic wire expectations; never calls Wistia. */
final class AccessibilityCases
{
    public const CAPTION = '{"id":"caption","media_id":"media","language":"eng","is_draft":false,"version":3,"segments":[{"start_ms":0,"end_ms":1000,"text":"private text"}],"text":"private SRT","cursor":"next"}';
    public const LOCALIZATION = '{"hashed_id":"loc","language_code":"zh-Hant","iso639_2_language_code":"zho","language_name":"Chinese","native_language_name":"中文","created_at":"2026-10-16T00:00:00Z","updated_at":"2026-10-16T00:00:00Z","ordered_at":null,"enabled_at":null,"enabled":false,"source_media":{"hashed_id":"media","name":"private source","duration":12.5,"created_at":"2026-10-16T00:00:00Z","updated_at":"2026-10-16T00:00:00Z"},"transcript":null}';
    public const AUDIO = '{"id":"audio","ietf_language_tag":"eng","media":{"id":"media","index_url":"https://example.test/medias","url":"https://example.test/media"},"cursor":"next"}';
    public const ORDER = '{"order":{"id":"order","order_status":"in_progress","ietf_language_tag":"eng","created_at":"2026-10-16T00:00:00Z","updated_at":"2026-10-16T00:00:00Z","media":{"id":"media","index_url":"https://example.test/medias","url":"https://example.test/media"},"media_extended_audio_description":null}}';
    public const MATCH = '{"requested_count":2,"succeeded_count":1,"failed_count":1,"complete":true,"results":[{"media_hashed_id":"media","status":"ok","track":{"id":"track","language":"eng","version":3,"is_draft":false},"match_status":"not_found","match_count":0,"matches":[],"suggestions":[{"text":"private fuzzy text","context":"private context","start_ms":0,"end_ms":100}],"suggestions_truncated":false},{"media_hashed_id":"inaccessible","status":"media_not_found"}]}';
    public const EDIT = '{"applied":true,"media_id":"source","language":"eng","version":{"from":3,"to":4},"edits":[{"target_text":"old","replacement_text":"","matched_text":"Old","context":"private context","original":{"start_ms":0,"end_ms":1000},"replacement":null}],"warnings":[]}';
    public static function all(): array
    {
        $c=DataCaptionsService::class; $l=DataLocalizationsService::class; $a=DataExtendedAudioDescriptionsService::class;
        return [
            'ListCaptionsByMedia'=>[$c,'listCaptionsByMedia',new R\ListCaptionsByMediaRequest('id/ +?'),'GET','/medias/id%2F%20%2B%3F/captions',null,200,'['.self::CAPTION.']'],
            'CreateCaptions'=>[$c,'createCaptions',new R\CreateCaptionsRequest('media',"1\n00:00:00,000 --> 00:00:01,000\nHi\n",'eng'),'POST','/medias/media/captions',json_encode(['caption_file'=>"1\n00:00:00,000 --> 00:00:01,000\nHi\n",'language'=>'eng']),200,''],
            'ListCaptions'=>[$c,'listCaptions',new R\ListCaptionsRequest(media_ids:['a','b'],languages:['eng','spa'],include:'metadata'),'GET','/captions?media_ids%5B%5D=a&media_ids%5B%5D=b&languages%5B%5D=eng&languages%5B%5D=spa&include=metadata',null,200,'['.self::CAPTION.']'],
            'FindCaptionMatches'=>[$c,'findCaptionMatches',new R\FindCaptionMatchesRequest(['media','inaccessible'],'old',language_code:'zh-Hant',occurrence:11,start_ms:0,end_ms:1000),'POST','/caption_matches','{"media_ids":["media","inaccessible"],"target_text":"old","language_code":"zh-Hant","occurrence":11,"start_ms":0,"end_ms":1000}',200,self::MATCH],
            'ShowCaptions'=>[$c,'showCaptions',new R\ShowCaptionsRequest('id/ +?','zh-Hant',include:'segments'),'GET','/medias/id%2F%20%2B%3F/captions/zh-Hant?include=segments',null,200,self::CAPTION],
            'UpdateCaptions'=>[$c,'updateCaptions',new R\UpdateCaptionsRequest('media','eng','replacement SRT'),'PUT','/medias/media/captions/eng','{"caption_file":"replacement SRT"}',200,''],
            'DeleteCaptions'=>[$c,'deleteCaptions',new R\DeleteCaptionsRequest('media','eng'),'DELETE','/medias/media/captions/eng',null,200,''],
            'EditCaptionsText'=>[$c,'editCaptionsText',new R\EditCaptionsTextRequest('media','zh-Hant',[new CaptionEdit('old','',0,1000)],3),'POST','/medias/media/captions/zh-Hant/edits','{"edits":[{"target_text":"old","replacement_text":"","start_ms":0,"end_ms":1000}],"expected_version":3}',200,self::EDIT],
            'ListSpeakers'=>[DataSpeakersService::class,'listSpeakers',new R\ListSpeakersRequest(name:'A B',sort_by:'name',sort_direction:0),'GET','/speakers?name=A%20B&sort_by=name&sort_direction=0',null,200,'[{"speaker_profile_id":"speaker","name":"private name","title":null,"image_url":null,"cursor":"next"}]'],
            'ListLocalizations'=>[$l,'listLocalizations',new R\ListLocalizationsRequest('media',false),'GET','/medias/media/localizations?include_transcript=false',null,200,'['.self::LOCALIZATION.']'],
            'ShowLocalization'=>[$l,'showLocalization',new R\ShowLocalizationRequest('media','loc/ +?',true),'GET','/medias/media/localizations/loc%2F%20%2B%3F?include_transcript=true',null,200,self::LOCALIZATION],
            'DeleteLocalization'=>[$l,'deleteLocalization',new R\DeleteLocalizationRequest('media','loc'),'DELETE','/medias/media/localizations/loc',null,200,self::LOCALIZATION],
            'ListMediaExtendedAudioDescriptions'=>[$a,'listMediaExtendedAudioDescriptions',new R\ListMediaExtendedAudioDescriptionsRequest(hashed_ids:['one','two'],sort_by:'language'),'GET','/media_extended_audio_descriptions?hashed_ids%5B%5D=one&hashed_ids%5B%5D=two&sort_by=language',null,200,'['.self::AUDIO.']'],
            'ShowMediaExtendedAudioDescription'=>[$a,'showMediaExtendedAudioDescription',new R\ShowMediaExtendedAudioDescriptionRequest('audio'),'GET','/media_extended_audio_descriptions/audio',null,200,substr(self::AUDIO,0,-1).',"assets":{"mp3_url":null,"webvtt":"private WebVTT","webvtt_extended":null}}'],
            'DeleteMediaExtendedAudioDescription'=>[$a,'deleteMediaExtendedAudioDescription',new R\DeleteMediaExtendedAudioDescriptionRequest('audio'),'DELETE','/media_extended_audio_descriptions/audio',null,200,self::AUDIO],
            'GetOrderStatus'=>[$a,'getOrderStatus',new R\GetOrderStatusRequest('order/ +?'),'GET','/media_extended_audio_descriptions/order_status/order%2F%20%2B%3F',null,200,self::ORDER],
        ];
    }
}

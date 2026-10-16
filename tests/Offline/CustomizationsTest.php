<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Customizations as C;
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as Results;
use Automattic\Wistia\Tests\Contract\Data\CustomizationsCases;
use PHPUnit\Framework\TestCase;

final class CustomizationsTest extends TestCase
{
    public function testOmittedNullFalseZeroAndEmptyRemainDistinct(): void
    {
        $empty = new C\UpdatePlaybackCustomizationsInput();
        self::assertSame(C\Omitted::Value, $empty->autoPlay);
        self::assertSame('{}', (new R\UpdatePlaybackCustomizationsRequest('media', $empty))->body()->contents());
        $input = new C\UpdatePlaybackCustomizationsInput(autoPlay: false, volume: 0, email: '', time: null, videoFoam: null);
        self::assertSame('{"autoPlay":false,"volume":0.0,"time":null,"videoFoam":null,"email":""}',
            (new R\UpdatePlaybackCustomizationsRequest('media', $input))->body()->contents());
        $nested = new C\UpdateAccessCustomizationsInput(
            private: null, encrypted: new C\UpdateAccessCustomizationsInputEncrypted(password_protect_password: null), plugin: null);
        self::assertSame('{"private":null,"encrypted":{"password_protect_password":null},"plugin":null}',
            (new R\UpdateAccessCustomizationsRequest('media', $nested))->body()->contents());
        self::assertSame('{"plugin":{"chapters":{"chapterList":[]}}}', json_encode(new C\UpdateChaptersCustomizationsInput(
            plugin: new C\UpdateChaptersCustomizationsInputPlugin(chapters: new C\UpdateChaptersCustomizationsInputPluginChapters(chapterList: [])))));
        self::assertSame('{"playerColor":null}', (new R\UpdateCustomizationsRequest('media',
            new C\UpdateCustomizationsInput(playerColor: null)))->body()->contents());
    }
    public function testAllProviderDiscriminatorsAndExactProviderSettings(): void
    {
        foreach (['wistia_form', 'hubspot', 'marketo', 'pardot'] as $provider) {
            $value = new C\UpdateLeadCaptureCustomizationsInput(provider: $provider);
            self::assertSame('{"provider":"'.$provider.'"}', json_encode($value));
        }
        $wistia = new C\UpdateLeadCaptureCustomizationsInput('wistia_form', settings: new C\UpdateLeadCaptureCustomizationsInputSettings(
            time: 'end', allowSkip: false, hashedId: 'form', displayMode: 'inline', showLogo: false, backgroundColor: '000000'));
        self::assertSame('{"provider":"wistia_form","settings":{"time":"end","allowSkip":false,"hashedId":"form","displayMode":"inline","showLogo":false,"backgroundColor":"000000"}}', json_encode($wistia));
    }
    public function testNestedResultsPreserveStringValuesTypedRecordsAndExtensibleFields(): void
    {
        $cases = CustomizationsCases::all();
        $playback = Results\ShowPlaybackCustomizationsResult::fromWire(json_decode($cases['ShowPlaybackCustomizations'][6]), 200)->data;
        self::assertSame('false', $playback->autoPlay);
        self::assertSame('0', $playback->volume);
        self::assertSame('0', $playback->videoFoam->minWidth);
        self::assertTrue($playback->clickForSound->opaque);
        $chapters = Results\ShowChaptersCustomizationsResult::fromWire(json_decode($cases['ShowChaptersCustomizations'][6]), 200)->data;
        self::assertSame('Intro', $chapters->plugin->audioChapters->chapterList[0]->title);
        self::assertSame('preserved', $chapters->plugin->audioChapters->chapterList[0]->additionalFields->future);
        $lead = Results\ShowLeadCaptureCustomizationsResult::fromWire(json_decode($cases['ShowLeadCaptureCustomizations'][6]), 200)->data;
        self::assertSame('false', $lead->plugin->hubspotForm->on);
        self::assertSame('portal', $lead->plugin->hubspotForm->additionalFields->portalId);
        self::assertSame(1, $lead->plugin->hubspotForm->additionalFields->future->x);
        $broad = Results\ShowCustomizationsResult::fromWire(json_decode($cases['ShowCustomizations'][6]), 200)->data;
        self::assertSame('false', $broad->plugin->socialbar_v1->showTweetCount);
        self::assertTrue($broad->plugin->additionalFields->other->opaque);
        self::assertSame('synthetic-private', $broad->encrypted->password_protect_password);
        self::assertStringNotContainsString('synthetic-private', print_r($broad, true));
        self::assertNull($broad->stillUrl);
        self::assertNotContains('stillUrl', $broad->presentFields);
    }
    public function testAlternativeRequestAndResponseShapes(): void
    {
        self::assertSame('{"videoFoam":false}', json_encode(new C\UpdatePlaybackCustomizationsInput(videoFoam: false)));
        self::assertSame('{"plugin":{"postRoll-v1":{"time":"end"}}}', json_encode(new C\UpdateEngagementCustomizationsInput(
            plugin: new C\UpdateEngagementCustomizationsInputPlugin(postRoll_v1: new C\UpdateEngagementCustomizationsInputPluginPostRollV1(time: 'end')))));
        $r = Results\ShowPlaybackCustomizationsResult::fromWire(json_decode('{"videoFoam":"true","clickForSound":"false"}'), 200);
        self::assertSame('true', $r->data->videoFoam);
        self::assertSame('false', $r->data->clickForSound);
    }
    public function testInvalidConcernInputsAreRejectedWithoutValuesInErrors(): void
    {
        $invalid = [
            fn() => new C\UpdateLeadCaptureCustomizationsInput('synthetic-private'),
            fn() => new C\UpdateSharingCustomizationsInputPluginShare(channels: ['linkedin']),
            fn() => new C\UpdateSharingCustomizationsInputPluginShare(channels: [true]),
            fn() => new C\UpdateSharingCustomizationsInputPluginShare(channels: ['x' => 'email']),
            fn() => new C\UpdateSharingCustomizationsInputPluginShare(downloadType: 'synthetic-private'),
            fn() => new C\UpdateRelatedMediaCustomizationsInputPluginRelatedMedia(hashedIdList: [42]),
            fn() => new C\UpdateChaptersCustomizationsInputPluginChapters(chapterList: [(object) ['time' => 0]]),
            fn() => new C\UpdateAppearanceCustomizationsInputPlayerColorGradient(colors: ['112233']),
            fn() => new C\UpdatePlaybackCustomizationsInput(volume: INF),
            fn() => new C\CreateCustomizationsInput(autoPlay: null),
            fn() => new C\UpdateLeadCaptureCustomizationsInput('hubspot', enabled: null),
            fn() => new R\ShowCustomizationsRequest(' '),
        ];
        foreach ($invalid as $make) {
            try { $make(); self::fail('Expected local validation failure'); }
            catch (\InvalidArgumentException $e) {
                self::assertStringNotContainsString('synthetic-private', (string) $e);
                self::assertNull($e->getPrevious());
            }
        }
    }
    public function testNestedInvalidResponsesAreNotSilentlyCoerced(): void
    {
        $invalid = [
            [Results\ShowPlaybackCustomizationsResult::class, '{"autoPlay":false}'],
            [Results\ShowPlaybackCustomizationsResult::class, '{"videoFoam":{"minWidth":0}}'],
            [Results\ShowChaptersCustomizationsResult::class, '{"plugin":{"chapters":{"chapterList":[{"time":0}]}}}'],
            [Results\ShowAccessCustomizationsResult::class, '{"encrypted":{"password_protect_password":null}}'],
            [Results\ShowSharingCustomizationsResult::class, '{"plugin":{"share":{"channels":[false]}}}'],
            [Results\ShowAppearanceCustomizationsResult::class, '{"playerColorGradient":{"colors":{}}}'],
        ];
        foreach ($invalid as [$class, $json]) {
            try { $class::fromWire(json_decode($json), 200); self::fail('Expected invalid wire shape'); }
            catch (\InvalidArgumentException $e) { self::assertNull($e->getPrevious()); }
        }
    }
}

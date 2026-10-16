<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Offline;

use Automattic\Wistia\Customizations as C;
use Automattic\Wistia\Request\Data as R;
use PHPUnit\Framework\TestCase;

final class CustomizationInputValidationTest extends TestCase
{
    private const OPTIONS = [
        'endVideoBehavior' => ['default', 'reset', 'loop'],
        'silentAutoPlay' => ['true', 'allow', 'false'],
        'resumable' => ['true', 'false', 'auto'],
        'preload' => ['metadata', 'auto', 'none', 'true', 'false'],
        'fitStrategy' => ['contain', 'cover', 'fill', 'none'],
    ];

    public function testEveryNarrativeOptionAtEveryExposedInput(): void
    {
        foreach (self::OPTIONS as $field => $values) {
            $classes = [C\CreateCustomizationsInput::class, C\UpdateCustomizationsInput::class,
                $field === 'fitStrategy' ? C\UpdateThumbnailCustomizationsInput::class : C\UpdatePlaybackCustomizationsInput::class];
            foreach ($classes as $class) {
                foreach ($values as $value) {
                    $input = new $class(...[$field => $value]);
                    self::assertEquals((object) [$field => $value], $input->jsonSerialize());
                }
                self::assertEquals(new \stdClass(), (new $class())->jsonSerialize());
                if ($class !== C\CreateCustomizationsInput::class) {
                    self::assertSame('{"'.$field.'":null}', json_encode(new $class(...[$field => null])));
                }
                foreach (['unsupported-private', '', strtoupper($values[0])] as $bad) {
                    try { new $class(...[$field => $bad]); self::fail('Expected local option validation'); }
                    catch (\InvalidArgumentException $e) {
                        self::assertStringNotContainsString($bad === '' ? 'unsupported-private' : $bad, (string) $e);
                    }
                }
            }
        }
    }

    public function testImageAltTextTraversesAllThreeClosedInputTrees(): void
    {
        $create = new R\CreateCustomizationsRequest('media', new C\CreateCustomizationsInput(
            plugin: new C\CreateCustomizationsInputPlugin(postRoll_v1: new C\CreateCustomizationsInputPluginPostRollV1(
                ctaType: 'image', altText: 'A & "B" <image>'))));
        $update = new R\UpdateCustomizationsRequest('media', new C\UpdateCustomizationsInput(
            plugin: new C\UpdateCustomizationsInputPlugin(postRoll_v1: new C\UpdateCustomizationsInputPluginPostRollV1(
                ctaType: 'image', altText: 'A & "B" <image>'))));
        $engagement = new R\UpdateEngagementCustomizationsRequest('media', new C\UpdateEngagementCustomizationsInput(
            plugin: new C\UpdateEngagementCustomizationsInputPlugin(postRoll_v1: new C\UpdateEngagementCustomizationsInputPluginPostRollV1(
                ctaType: 'image', altText: 'A & "B" <image>'))));
        foreach ([$create, $update, $engagement] as $request) {
            self::assertSame('{"plugin":{"postRoll-v1":{"ctaType":"image","altText":"A & \\"B\\" <image>"}}}', $request->body()->contents());
        }
        $partialTrees = [
            [C\UpdateCustomizationsInput::class, C\UpdateCustomizationsInputPlugin::class, C\UpdateCustomizationsInputPluginPostRollV1::class],
            [C\UpdateEngagementCustomizationsInput::class, C\UpdateEngagementCustomizationsInputPlugin::class, C\UpdateEngagementCustomizationsInputPluginPostRollV1::class],
        ];
        foreach ($partialTrees as [$root, $plugin, $postRoll]) {
            foreach (['', null, C\Omitted::Value] as $value) {
                $input = new $root(plugin: new $plugin(postRoll_v1: new $postRoll(altText: $value)));
                $expected = $value === C\Omitted::Value ? '{}' : '{"altText":'.json_encode($value).'}';
                self::assertSame('{"plugin":{"postRoll-v1":'.$expected.'}}', json_encode($input));
            }
        }
        foreach ([C\CreateCustomizationsInputPluginPostRollV1::class, C\UpdateCustomizationsInputPluginPostRollV1::class,
            C\UpdateEngagementCustomizationsInputPluginPostRollV1::class] as $class) {
            foreach (['text', 'image', 'html'] as $type) {
                self::assertSame('{"ctaType":"'.$type.'"}', json_encode(new $class(ctaType: $type)));
            }
            try { new $class(ctaType: 'unsupported-private'); self::fail('Expected CTA option validation'); }
            catch (\InvalidArgumentException $e) { self::assertStringNotContainsString('unsupported-private', (string) $e); }
        }
        try { new C\CreateCustomizationsInputPluginPostRollV1(altText: null); self::fail('Replacement must reject null'); }
        catch (\InvalidArgumentException $e) { self::assertNull($e->getPrevious()); }
    }

    public function testAltTextResponseRemainsAnExtensibleField(): void
    {
        foreach (['ShowCustomizations', 'CreateCustomizations', 'UpdateCustomizations',
            'ShowEngagementCustomizations', 'UpdateEngagementCustomizations'] as $stem) {
            $class = 'Automattic\\Wistia\\Result\\Data\\' . $stem . 'Result';
            $result = $class::fromWire(json_decode('{"plugin":{"postRoll-v1":{"ctaType":"image","altText":"Synthetic image"}}}'),
                $stem === 'CreateCustomizations' ? 201 : 200);
            self::assertSame('Synthetic image', $result->data->plugin->postRoll_v1->additionalFields->altText);
            self::assertNotContains('Synthetic image', $result->data->plugin->postRoll_v1->__debugInfo());
        }
        foreach (['{"altText":false}', '{"altText":null}', '{"unrelated":true}'] as $postRoll) {
            try {
                \Automattic\Wistia\Result\Data\ShowCustomizationsResult::fromWire(
                    json_decode('{"plugin":{"postRoll-v1":'.$postRoll.'}}'), 200);
                self::fail('Closed broad response must reject invalid or unrelated fields');
            } catch (\InvalidArgumentException $e) { self::assertNull($e->getPrevious()); }
        }
    }

    public function testGradientPairStructureAndComponentTypesWithoutInventedStopRange(): void
    {
        foreach ([[], [['112233', 0], ['#abc', 1.5]], [['abcd', -2], ['12345678', 10]]] as $colors) {
            $input = new C\UpdateAppearanceCustomizationsInput(playerColorGradient:
                new C\UpdateAppearanceCustomizationsInputPlayerColorGradient(colors: $colors));
            self::assertSame($colors, $input->playerColorGradient->colors);
            self::assertSame(json_encode((object) ['playerColorGradient' => (object) ['colors' => $colors]]), json_encode($input));
        }
        foreach ([C\Omitted::Value, null] as $value) {
            $input = new C\UpdateAppearanceCustomizationsInputPlayerColorGradient(colors: $value);
            self::assertSame($value === null ? '{"colors":null}' : '{}', json_encode($input));
        }
        foreach ([[[]], [['112233']], [['112233', 0, 'extra']], [['112233', '0']], [[0, 0]],
            [['not-a-hex-color', 0]], [['112233', INF]], [['112233', NAN]], [['color' => '112233', 'stop' => 0]]] as $colors) {
            try { new C\UpdateAppearanceCustomizationsInputPlayerColorGradient(colors: $colors); self::fail('Expected pair validation'); }
            catch (\InvalidArgumentException $e) { self::assertNull($e->getPrevious()); }
        }
    }
}

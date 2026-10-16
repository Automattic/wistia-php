<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class CreateCustomizationsInput implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public bool|null|Omitted $autoPlay = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $controlsVisibleOnLoad = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $copyLinkAndThumbnailEnabled = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $doNotTrack = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $email = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $endVideoBehavior = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $fakeFullscreen = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $fitStrategy = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $fullscreenButton = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $fullscreenOnRotateToLandscape = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $keyMoments = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $muted = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $playbackRateControl = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $playbar = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $playButton = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $playerColor = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $playlistLinks = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $playlistLoop = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $playsinline = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $playPauseNotifier = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $playSuspendedOffScreen = Omitted::Value,
        #[\SensitiveParameter] public CreateCustomizationsInputPlugin|null|Omitted $plugin = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $preload = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $qualityControl = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $qualityMax = Omitted::Value,
        #[\SensitiveParameter] public int|null|Omitted $qualityMin = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $resumable = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $seo = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $settingsControl = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $silentAutoPlay = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $smallPlayButton = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $stillUrl = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $time = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $thumbnailAltText = Omitted::Value,
        #[\SensitiveParameter] public bool|CreateCustomizationsInputVideoFoam2|null|Omitted $videoFoam = Omitted::Value,
        #[\SensitiveParameter] public float|null|Omitted $volume = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $volumeControl = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $wmode = Omitted::Value,
    ) {
        if ($this->autoPlay !== Omitted::Value) { Shape::check(Shape::encode($this->autoPlay), ['type' => 'boolean'], false); }
        if ($this->controlsVisibleOnLoad !== Omitted::Value) { Shape::check(Shape::encode($this->controlsVisibleOnLoad), ['type' => 'boolean'], false); }
        if ($this->copyLinkAndThumbnailEnabled !== Omitted::Value) { Shape::check(Shape::encode($this->copyLinkAndThumbnailEnabled), ['type' => 'boolean'], false); }
        if ($this->doNotTrack !== Omitted::Value) { Shape::check(Shape::encode($this->doNotTrack), ['type' => 'boolean'], false); }
        if ($this->email !== Omitted::Value) { Shape::check(Shape::encode($this->email), ['type' => 'string'], false); }
        if ($this->endVideoBehavior !== Omitted::Value) { Shape::check(Shape::encode($this->endVideoBehavior), ['type' => 'string', 'enum' => ['default', 'reset', 'loop']], false); }
        if ($this->fakeFullscreen !== Omitted::Value) { Shape::check(Shape::encode($this->fakeFullscreen), ['type' => 'boolean'], false); }
        if ($this->fitStrategy !== Omitted::Value) { Shape::check(Shape::encode($this->fitStrategy), ['type' => 'string', 'enum' => ['contain', 'cover', 'fill', 'none']], false); }
        if ($this->fullscreenButton !== Omitted::Value) { Shape::check(Shape::encode($this->fullscreenButton), ['type' => 'boolean'], false); }
        if ($this->fullscreenOnRotateToLandscape !== Omitted::Value) { Shape::check(Shape::encode($this->fullscreenOnRotateToLandscape), ['type' => 'boolean'], false); }
        if ($this->keyMoments !== Omitted::Value) { Shape::check(Shape::encode($this->keyMoments), ['type' => 'boolean'], false); }
        if ($this->muted !== Omitted::Value) { Shape::check(Shape::encode($this->muted), ['type' => 'boolean'], false); }
        if ($this->playbackRateControl !== Omitted::Value) { Shape::check(Shape::encode($this->playbackRateControl), ['type' => 'boolean'], false); }
        if ($this->playbar !== Omitted::Value) { Shape::check(Shape::encode($this->playbar), ['type' => 'boolean'], false); }
        if ($this->playButton !== Omitted::Value) { Shape::check(Shape::encode($this->playButton), ['type' => 'boolean'], false); }
        if ($this->playerColor !== Omitted::Value) { Shape::check(Shape::encode($this->playerColor), ['type' => 'string'], false); }
        if ($this->playlistLinks !== Omitted::Value) { Shape::check(Shape::encode($this->playlistLinks), ['type' => 'boolean'], false); }
        if ($this->playlistLoop !== Omitted::Value) { Shape::check(Shape::encode($this->playlistLoop), ['type' => 'boolean'], false); }
        if ($this->playsinline !== Omitted::Value) { Shape::check(Shape::encode($this->playsinline), ['type' => 'boolean'], false); }
        if ($this->playPauseNotifier !== Omitted::Value) { Shape::check(Shape::encode($this->playPauseNotifier), ['type' => 'boolean'], false); }
        if ($this->playSuspendedOffScreen !== Omitted::Value) { Shape::check(Shape::encode($this->playSuspendedOffScreen), ['type' => 'boolean'], false); }
        if ($this->plugin !== Omitted::Value) { Shape::check(Shape::encode($this->plugin), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['videoThumbnail' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['clickToPlayButton' => ['type' => 'boolean']]], 'socialbar-v1' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['buttons' => ['type' => 'string'], 'showTweetCount' => ['type' => 'boolean'], 'tweetText' => ['type' => 'string'], 'height' => ['type' => 'integer']]], 'chapters' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['visibleOnLoad' => ['type' => 'boolean'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]], 'on' => ['type' => 'boolean']]], 'postRoll-v1' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['rewatch' => ['type' => 'boolean'], 'text' => ['type' => 'string'], 'link' => ['type' => 'string'], 'time' => ['unevaluatedProperties' => false, 'oneOf' => [['type' => 'string'], ['type' => 'number']]], 'autoSize' => ['type' => 'boolean'], 'style' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['backgroundColor' => ['type' => 'string']]], 'ctaType' => ['type' => 'string', 'enum' => ['text', 'image', 'html']], 'altText' => ['type' => 'string'], 'on' => ['type' => 'boolean'], 'conversionOpportunityKey' => ['type' => 'string']]], 'captions-v1' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'onByDefault' => ['type' => 'boolean']]]]], false); }
        if ($this->preload !== Omitted::Value) { Shape::check(Shape::encode($this->preload), ['type' => 'string', 'enum' => ['metadata', 'auto', 'none', 'true', 'false']], false); }
        if ($this->qualityControl !== Omitted::Value) { Shape::check(Shape::encode($this->qualityControl), ['type' => 'boolean'], false); }
        if ($this->qualityMax !== Omitted::Value) { Shape::check(Shape::encode($this->qualityMax), ['type' => 'integer'], false); }
        if ($this->qualityMin !== Omitted::Value) { Shape::check(Shape::encode($this->qualityMin), ['type' => 'integer'], false); }
        if ($this->resumable !== Omitted::Value) { Shape::check(Shape::encode($this->resumable), ['type' => 'string', 'enum' => ['true', 'false', 'auto']], false); }
        if ($this->seo !== Omitted::Value) { Shape::check(Shape::encode($this->seo), ['type' => 'boolean'], false); }
        if ($this->settingsControl !== Omitted::Value) { Shape::check(Shape::encode($this->settingsControl), ['type' => 'boolean'], false); }
        if ($this->silentAutoPlay !== Omitted::Value) { Shape::check(Shape::encode($this->silentAutoPlay), ['type' => 'string', 'enum' => ['true', 'allow', 'false']], false); }
        if ($this->smallPlayButton !== Omitted::Value) { Shape::check(Shape::encode($this->smallPlayButton), ['type' => 'boolean'], false); }
        if ($this->stillUrl !== Omitted::Value) { Shape::check(Shape::encode($this->stillUrl), ['type' => 'string'], false); }
        if ($this->time !== Omitted::Value) { Shape::check(Shape::encode($this->time), ['type' => 'string'], false); }
        if ($this->thumbnailAltText !== Omitted::Value) { Shape::check(Shape::encode($this->thumbnailAltText), ['type' => 'string'], false); }
        if ($this->videoFoam !== Omitted::Value) { Shape::check(Shape::encode($this->videoFoam), ['unevaluatedProperties' => false, 'anyOf' => [['type' => 'boolean'], ['type' => 'object', 'unevaluatedProperties' => false, 'properties' => ['minWidth' => ['type' => 'integer'], 'maxWidth' => ['type' => 'integer'], 'minHeight' => ['type' => 'integer'], 'maxHeight' => ['type' => 'integer']]]]], false); }
        if ($this->volume !== Omitted::Value) { Shape::check(Shape::encode($this->volume), ['type' => 'number'], false); }
        if ($this->volumeControl !== Omitted::Value) { Shape::check(Shape::encode($this->volumeControl), ['type' => 'boolean'], false); }
        if ($this->wmode !== Omitted::Value) { Shape::check(Shape::encode($this->wmode), ['type' => 'string'], false); }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        if ($this->autoPlay !== Omitted::Value) { $out->{'autoPlay'} = Shape::encode($this->autoPlay); }
        if ($this->controlsVisibleOnLoad !== Omitted::Value) { $out->{'controlsVisibleOnLoad'} = Shape::encode($this->controlsVisibleOnLoad); }
        if ($this->copyLinkAndThumbnailEnabled !== Omitted::Value) { $out->{'copyLinkAndThumbnailEnabled'} = Shape::encode($this->copyLinkAndThumbnailEnabled); }
        if ($this->doNotTrack !== Omitted::Value) { $out->{'doNotTrack'} = Shape::encode($this->doNotTrack); }
        if ($this->email !== Omitted::Value) { $out->{'email'} = Shape::encode($this->email); }
        if ($this->endVideoBehavior !== Omitted::Value) { $out->{'endVideoBehavior'} = Shape::encode($this->endVideoBehavior); }
        if ($this->fakeFullscreen !== Omitted::Value) { $out->{'fakeFullscreen'} = Shape::encode($this->fakeFullscreen); }
        if ($this->fitStrategy !== Omitted::Value) { $out->{'fitStrategy'} = Shape::encode($this->fitStrategy); }
        if ($this->fullscreenButton !== Omitted::Value) { $out->{'fullscreenButton'} = Shape::encode($this->fullscreenButton); }
        if ($this->fullscreenOnRotateToLandscape !== Omitted::Value) { $out->{'fullscreenOnRotateToLandscape'} = Shape::encode($this->fullscreenOnRotateToLandscape); }
        if ($this->keyMoments !== Omitted::Value) { $out->{'keyMoments'} = Shape::encode($this->keyMoments); }
        if ($this->muted !== Omitted::Value) { $out->{'muted'} = Shape::encode($this->muted); }
        if ($this->playbackRateControl !== Omitted::Value) { $out->{'playbackRateControl'} = Shape::encode($this->playbackRateControl); }
        if ($this->playbar !== Omitted::Value) { $out->{'playbar'} = Shape::encode($this->playbar); }
        if ($this->playButton !== Omitted::Value) { $out->{'playButton'} = Shape::encode($this->playButton); }
        if ($this->playerColor !== Omitted::Value) { $out->{'playerColor'} = Shape::encode($this->playerColor); }
        if ($this->playlistLinks !== Omitted::Value) { $out->{'playlistLinks'} = Shape::encode($this->playlistLinks); }
        if ($this->playlistLoop !== Omitted::Value) { $out->{'playlistLoop'} = Shape::encode($this->playlistLoop); }
        if ($this->playsinline !== Omitted::Value) { $out->{'playsinline'} = Shape::encode($this->playsinline); }
        if ($this->playPauseNotifier !== Omitted::Value) { $out->{'playPauseNotifier'} = Shape::encode($this->playPauseNotifier); }
        if ($this->playSuspendedOffScreen !== Omitted::Value) { $out->{'playSuspendedOffScreen'} = Shape::encode($this->playSuspendedOffScreen); }
        if ($this->plugin !== Omitted::Value) { $out->{'plugin'} = Shape::encode($this->plugin); }
        if ($this->preload !== Omitted::Value) { $out->{'preload'} = Shape::encode($this->preload); }
        if ($this->qualityControl !== Omitted::Value) { $out->{'qualityControl'} = Shape::encode($this->qualityControl); }
        if ($this->qualityMax !== Omitted::Value) { $out->{'qualityMax'} = Shape::encode($this->qualityMax); }
        if ($this->qualityMin !== Omitted::Value) { $out->{'qualityMin'} = Shape::encode($this->qualityMin); }
        if ($this->resumable !== Omitted::Value) { $out->{'resumable'} = Shape::encode($this->resumable); }
        if ($this->seo !== Omitted::Value) { $out->{'seo'} = Shape::encode($this->seo); }
        if ($this->settingsControl !== Omitted::Value) { $out->{'settingsControl'} = Shape::encode($this->settingsControl); }
        if ($this->silentAutoPlay !== Omitted::Value) { $out->{'silentAutoPlay'} = Shape::encode($this->silentAutoPlay); }
        if ($this->smallPlayButton !== Omitted::Value) { $out->{'smallPlayButton'} = Shape::encode($this->smallPlayButton); }
        if ($this->stillUrl !== Omitted::Value) { $out->{'stillUrl'} = Shape::encode($this->stillUrl); }
        if ($this->time !== Omitted::Value) { $out->{'time'} = Shape::encode($this->time); }
        if ($this->thumbnailAltText !== Omitted::Value) { $out->{'thumbnailAltText'} = Shape::encode($this->thumbnailAltText); }
        if ($this->videoFoam !== Omitted::Value) { $out->{'videoFoam'} = Shape::encode($this->videoFoam); }
        if ($this->volume !== Omitted::Value) { $out->{'volume'} = Shape::encode($this->volume); }
        if ($this->volumeControl !== Omitted::Value) { $out->{'volumeControl'} = Shape::encode($this->volumeControl); }
        if ($this->wmode !== Omitted::Value) { $out->{'wmode'} = Shape::encode($this->wmode); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}

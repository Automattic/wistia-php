<?php
declare(strict_types=1);

namespace Automattic\Wistia\Customizations;

/** Exact-name request fields; Omitted differs from an explicit null reset. */
final readonly class UpdateCustomizationsInput implements \JsonSerializable
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
        #[\SensitiveParameter] public UpdateCustomizationsInputPlugin|null|Omitted $plugin = Omitted::Value,
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
        #[\SensitiveParameter] public bool|UpdateCustomizationsInputVideoFoam2|null|Omitted $videoFoam = Omitted::Value,
        #[\SensitiveParameter] public float|null|Omitted $volume = Omitted::Value,
        #[\SensitiveParameter] public bool|null|Omitted $volumeControl = Omitted::Value,
        #[\SensitiveParameter] public string|null|Omitted $wmode = Omitted::Value,
    ) {
        if ($this->autoPlay !== Omitted::Value && $this->autoPlay !== null) { Shape::check(Shape::encode($this->autoPlay), ['type' => 'boolean'], true); }
        if ($this->controlsVisibleOnLoad !== Omitted::Value && $this->controlsVisibleOnLoad !== null) { Shape::check(Shape::encode($this->controlsVisibleOnLoad), ['type' => 'boolean'], true); }
        if ($this->copyLinkAndThumbnailEnabled !== Omitted::Value && $this->copyLinkAndThumbnailEnabled !== null) { Shape::check(Shape::encode($this->copyLinkAndThumbnailEnabled), ['type' => 'boolean'], true); }
        if ($this->doNotTrack !== Omitted::Value && $this->doNotTrack !== null) { Shape::check(Shape::encode($this->doNotTrack), ['type' => 'boolean'], true); }
        if ($this->email !== Omitted::Value && $this->email !== null) { Shape::check(Shape::encode($this->email), ['type' => 'string'], true); }
        if ($this->endVideoBehavior !== Omitted::Value && $this->endVideoBehavior !== null) { Shape::check(Shape::encode($this->endVideoBehavior), ['type' => 'string', 'enum' => ['default', 'reset', 'loop']], true); }
        if ($this->fakeFullscreen !== Omitted::Value && $this->fakeFullscreen !== null) { Shape::check(Shape::encode($this->fakeFullscreen), ['type' => 'boolean'], true); }
        if ($this->fitStrategy !== Omitted::Value && $this->fitStrategy !== null) { Shape::check(Shape::encode($this->fitStrategy), ['type' => 'string', 'enum' => ['contain', 'cover', 'fill', 'none']], true); }
        if ($this->fullscreenButton !== Omitted::Value && $this->fullscreenButton !== null) { Shape::check(Shape::encode($this->fullscreenButton), ['type' => 'boolean'], true); }
        if ($this->fullscreenOnRotateToLandscape !== Omitted::Value && $this->fullscreenOnRotateToLandscape !== null) { Shape::check(Shape::encode($this->fullscreenOnRotateToLandscape), ['type' => 'boolean'], true); }
        if ($this->keyMoments !== Omitted::Value && $this->keyMoments !== null) { Shape::check(Shape::encode($this->keyMoments), ['type' => 'boolean'], true); }
        if ($this->muted !== Omitted::Value && $this->muted !== null) { Shape::check(Shape::encode($this->muted), ['type' => 'boolean'], true); }
        if ($this->playbackRateControl !== Omitted::Value && $this->playbackRateControl !== null) { Shape::check(Shape::encode($this->playbackRateControl), ['type' => 'boolean'], true); }
        if ($this->playbar !== Omitted::Value && $this->playbar !== null) { Shape::check(Shape::encode($this->playbar), ['type' => 'boolean'], true); }
        if ($this->playButton !== Omitted::Value && $this->playButton !== null) { Shape::check(Shape::encode($this->playButton), ['type' => 'boolean'], true); }
        if ($this->playerColor !== Omitted::Value && $this->playerColor !== null) { Shape::check(Shape::encode($this->playerColor), ['type' => 'string'], true); }
        if ($this->playlistLinks !== Omitted::Value && $this->playlistLinks !== null) { Shape::check(Shape::encode($this->playlistLinks), ['type' => 'boolean'], true); }
        if ($this->playlistLoop !== Omitted::Value && $this->playlistLoop !== null) { Shape::check(Shape::encode($this->playlistLoop), ['type' => 'boolean'], true); }
        if ($this->playsinline !== Omitted::Value && $this->playsinline !== null) { Shape::check(Shape::encode($this->playsinline), ['type' => 'boolean'], true); }
        if ($this->playPauseNotifier !== Omitted::Value && $this->playPauseNotifier !== null) { Shape::check(Shape::encode($this->playPauseNotifier), ['type' => 'boolean'], true); }
        if ($this->playSuspendedOffScreen !== Omitted::Value && $this->playSuspendedOffScreen !== null) { Shape::check(Shape::encode($this->playSuspendedOffScreen), ['type' => 'boolean'], true); }
        if ($this->plugin !== Omitted::Value && $this->plugin !== null) { Shape::check(Shape::encode($this->plugin), ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['videoThumbnail' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['clickToPlayButton' => ['type' => 'boolean']]], 'socialbar-v1' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['buttons' => ['type' => 'string'], 'showTweetCount' => ['type' => 'boolean'], 'tweetText' => ['type' => 'string'], 'height' => ['type' => 'integer']]], 'chapters' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['visibleOnLoad' => ['type' => 'boolean'], 'chapterList' => ['type' => 'array', 'items' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['id' => ['type' => 'string'], 'title' => ['type' => 'string'], 'time' => ['type' => 'string'], 'deleted' => ['type' => 'string']]]], 'on' => ['type' => 'boolean']]], 'postRoll-v1' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['rewatch' => ['type' => 'boolean'], 'text' => ['type' => 'string'], 'link' => ['type' => 'string'], 'time' => ['unevaluatedProperties' => false, 'oneOf' => [['type' => 'string'], ['type' => 'number']]], 'autoSize' => ['type' => 'boolean'], 'style' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['backgroundColor' => ['type' => 'string']]], 'ctaType' => ['type' => 'string', 'enum' => ['text', 'image', 'html']], 'altText' => ['type' => 'string'], 'on' => ['type' => 'boolean'], 'conversionOpportunityKey' => ['type' => 'string']]], 'captions-v1' => ['unevaluatedProperties' => false, 'type' => 'object', 'properties' => ['on' => ['type' => 'boolean'], 'onByDefault' => ['type' => 'boolean']]]]], true); }
        if ($this->preload !== Omitted::Value && $this->preload !== null) { Shape::check(Shape::encode($this->preload), ['type' => 'string', 'enum' => ['metadata', 'auto', 'none', 'true', 'false']], true); }
        if ($this->qualityControl !== Omitted::Value && $this->qualityControl !== null) { Shape::check(Shape::encode($this->qualityControl), ['type' => 'boolean'], true); }
        if ($this->qualityMax !== Omitted::Value && $this->qualityMax !== null) { Shape::check(Shape::encode($this->qualityMax), ['type' => 'integer'], true); }
        if ($this->qualityMin !== Omitted::Value && $this->qualityMin !== null) { Shape::check(Shape::encode($this->qualityMin), ['type' => 'integer'], true); }
        if ($this->resumable !== Omitted::Value && $this->resumable !== null) { Shape::check(Shape::encode($this->resumable), ['type' => 'string', 'enum' => ['true', 'false', 'auto']], true); }
        if ($this->seo !== Omitted::Value && $this->seo !== null) { Shape::check(Shape::encode($this->seo), ['type' => 'boolean'], true); }
        if ($this->settingsControl !== Omitted::Value && $this->settingsControl !== null) { Shape::check(Shape::encode($this->settingsControl), ['type' => 'boolean'], true); }
        if ($this->silentAutoPlay !== Omitted::Value && $this->silentAutoPlay !== null) { Shape::check(Shape::encode($this->silentAutoPlay), ['type' => 'string', 'enum' => ['true', 'allow', 'false']], true); }
        if ($this->smallPlayButton !== Omitted::Value && $this->smallPlayButton !== null) { Shape::check(Shape::encode($this->smallPlayButton), ['type' => 'boolean'], true); }
        if ($this->stillUrl !== Omitted::Value && $this->stillUrl !== null) { Shape::check(Shape::encode($this->stillUrl), ['type' => 'string'], true); }
        if ($this->time !== Omitted::Value && $this->time !== null) { Shape::check(Shape::encode($this->time), ['type' => 'string'], true); }
        if ($this->thumbnailAltText !== Omitted::Value && $this->thumbnailAltText !== null) { Shape::check(Shape::encode($this->thumbnailAltText), ['type' => 'string'], true); }
        if ($this->videoFoam !== Omitted::Value && $this->videoFoam !== null) { Shape::check(Shape::encode($this->videoFoam), ['unevaluatedProperties' => false, 'anyOf' => [['type' => 'boolean'], ['type' => 'object', 'unevaluatedProperties' => false, 'properties' => ['minWidth' => ['type' => 'integer'], 'maxWidth' => ['type' => 'integer'], 'minHeight' => ['type' => 'integer'], 'maxHeight' => ['type' => 'integer']]]]], true); }
        if ($this->volume !== Omitted::Value && $this->volume !== null) { Shape::check(Shape::encode($this->volume), ['type' => 'number'], true); }
        if ($this->volumeControl !== Omitted::Value && $this->volumeControl !== null) { Shape::check(Shape::encode($this->volumeControl), ['type' => 'boolean'], true); }
        if ($this->wmode !== Omitted::Value && $this->wmode !== null) { Shape::check(Shape::encode($this->wmode), ['type' => 'string'], true); }
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

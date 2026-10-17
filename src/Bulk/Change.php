<?php
declare(strict_types=1);

namespace Automattic\Wistia\Bulk;

use Automattic\Wistia\Encoding\Body;
use Automattic\Wistia\Media\Input;
use Automattic\Wistia\Request\Data as Requests;

/** A closed, immutable operation/payload pair, reusable for an action or a job. */
final readonly class Change implements \JsonSerializable
{
    private function __construct(public Operation $operation, public ResourceType $resourceType,
        #[\SensitiveParameter] private ?string $payload = null) {}

    // Reuse the endpoint DTO's validation and body encoding only. This private
    // placeholder satisfies path validation; it is never part of the bulk body.
    private const BODY_ONLY = 'bulk-body-only';

    public static function createFolder(
        #[\SensitiveParameter] ?string $name = null,
        #[\SensitiveParameter] ?string $adminEmail = null,
        #[\SensitiveParameter] ?string $description = null,
        #[\SensitiveParameter] ?bool $anonymousCanUpload = null,
        #[\SensitiveParameter] ?bool $anonymousCanDownload = null,
        #[\SensitiveParameter] ?bool $public = null,
        #[\SensitiveParameter] ?bool $personalLibrary = null,
    ): self {
        $body = (new Requests\CreateFolderRequest($name, $adminEmail, $description, $anonymousCanUpload, $anonymousCanDownload, $public, $personalLibrary))->body();
        return new self(Operation::Create, ResourceType::Folder, $body->contents());
    }

    public static function updateMedia(
        #[\SensitiveParameter] ?string $name = null,
        #[\SensitiveParameter] ?string $new_still_media_id = null,
        #[\SensitiveParameter] ?string $description = null,
        #[\SensitiveParameter] ?array $tags = null,
        #[\SensitiveParameter] ?\stdClass $custom_metadata = null,
    ): self {
        $body = (new Requests\UpdateMediaRequest(self::BODY_ONLY, $name, $new_still_media_id, $description, $tags, $custom_metadata))->body();
        return new self(Operation::Update, ResourceType::Media, $body->contents());
    }

    public static function updateFolder(
        #[\SensitiveParameter] ?string $name = null,
        #[\SensitiveParameter] ?string $description = null,
        #[\SensitiveParameter] ?bool $anonymousCanUpload = null,
        #[\SensitiveParameter] ?bool $anonymousCanDownload = null,
        #[\SensitiveParameter] ?bool $public = null,
    ): self {
        $body = (new Requests\UpdateFolderRequest(self::BODY_ONLY, $name, $description, $anonymousCanUpload, $anonymousCanDownload, $public))->body();
        return new self(Operation::Update, ResourceType::Folder, $body->contents());
    }

    public static function createSubfolder(
        #[\SensitiveParameter] string $folder_id,
        #[\SensitiveParameter] string $name,
        #[\SensitiveParameter] string|null|\Automattic\Wistia\Folders\Omitted $description = \Automattic\Wistia\Folders\Omitted::Value,
    ): self {
        $body = (new Requests\CreateSubfolderRequest($folder_id, $name, $description))->body();
        $payload = json_decode($body->contents(), false, 512, JSON_THROW_ON_ERROR);
        $payload->folder_id = $folder_id;
        $body = Body::json($payload);
        return new self(Operation::Create, ResourceType::Subfolder, $body->contents());
    }

    public static function updateSubfolder(
        #[\SensitiveParameter] ?string $name = null,
        #[\SensitiveParameter] string|null|\Automattic\Wistia\Folders\Omitted $description = \Automattic\Wistia\Folders\Omitted::Value,
    ): self {
        $body = (new Requests\UpdateSubfolderRequest(self::BODY_ONLY, self::BODY_ONLY, $name, $description))->body();
        return new self(Operation::Update, ResourceType::Subfolder, $body->contents());
    }

    public static function createChannel(
        #[\SensitiveParameter] string|null|\Automattic\Wistia\Channels\Omitted $name = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] string|null|\Automattic\Wistia\Channels\Omitted $description = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] bool|null $auto_publish_enabled = null,
        #[\SensitiveParameter] bool|null $podcast_enabled = null,
        #[\SensitiveParameter] string|null|\Automattic\Wistia\Channels\Omitted $custom_url = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] \Automattic\Wistia\Channels\ChannelPodcastSettings|null $podcast_settings = null,
    ): self {
        $body = (new Requests\CreateChannelRequest($name, $description, $auto_publish_enabled, $podcast_enabled, $custom_url, $podcast_settings))->body();
        return new self(Operation::Create, ResourceType::Channel, $body->contents());
    }

    public static function updateChannel(
        #[\SensitiveParameter] string|null|\Automattic\Wistia\Channels\Omitted $name = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] string|null|\Automattic\Wistia\Channels\Omitted $description = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] bool|null $auto_publish_enabled = null,
        #[\SensitiveParameter] bool|null $podcast_enabled = null,
        #[\SensitiveParameter] string|null|\Automattic\Wistia\Channels\Omitted $custom_url = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] \Automattic\Wistia\Channels\ChannelPodcastSettings|null $podcast_settings = null,
    ): self {
        $body = (new Requests\UpdateChannelRequest(self::BODY_ONLY, $name, $description, $auto_publish_enabled, $podcast_enabled, $custom_url, $podcast_settings))->body();
        return new self(Operation::Update, ResourceType::Channel, $body->contents());
    }

    public static function createChannelEpisode(
        #[\SensitiveParameter] string $channel_id,
        #[\SensitiveParameter] string|null $media_id = null,
        #[\SensitiveParameter] string|null $title = null,
        #[\SensitiveParameter] string|null $description = null,
        #[\SensitiveParameter] string|null $summary = null,
        #[\SensitiveParameter] string|null $publish_status = null,
        #[\SensitiveParameter] string|null $publish_at = null,
        #[\SensitiveParameter] \Automattic\Wistia\Channels\EpisodePodcastSettings|null $podcast_settings = null,
    ): self {
        $body = (new Requests\CreateChannelEpisodeRequest($channel_id, $media_id, $title, $description, $summary, $publish_status, $publish_at, $podcast_settings))->body();
        $payload = json_decode($body->contents(), false, 512, JSON_THROW_ON_ERROR);
        $payload->channel_id = $channel_id;
        $body = Body::json($payload);
        return new self(Operation::Create, ResourceType::ChannelEpisode, $body->contents());
    }

    public static function updateChannelEpisode(
        #[\SensitiveParameter] string|null|\Automattic\Wistia\Channels\Omitted $description = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] string|null|\Automattic\Wistia\Channels\Omitted $title = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] string|null $media_hashed_id = null,
        #[\SensitiveParameter] string|null $live_stream_event_hashed_id = null,
        #[\SensitiveParameter] string|null|\Automattic\Wistia\Channels\Omitted $summary = \Automattic\Wistia\Channels\Omitted::Value,
        #[\SensitiveParameter] string|null $publish_status = null,
        #[\SensitiveParameter] string|null $publish_at = null,
        #[\SensitiveParameter] string|null $episode_notes = null,
        #[\SensitiveParameter] \Automattic\Wistia\Channels\EpisodePodcastSettings|null $podcast_settings = null,
    ): self {
        $body = (new Requests\UpdateChannelEpisodeRequest(self::BODY_ONLY, $description, $title, $media_hashed_id, $live_stream_event_hashed_id, $summary, $publish_status, $publish_at, $episode_notes, $podcast_settings))->body();
        return new self(Operation::Update, ResourceType::ChannelEpisode, $body->contents());
    }

    public static function updateAccess(#[\SensitiveParameter] \Automattic\Wistia\Customizations\UpdateAccessCustomizationsInput $input): self
    {
        return new self(Operation::Update, ResourceType::CustomizationAccess, Body::json($input)->contents());
    }

    public static function updateAccessibility(#[\SensitiveParameter] \Automattic\Wistia\Customizations\UpdateAccessibilityCustomizationsInput $input): self
    {
        return new self(Operation::Update, ResourceType::CustomizationAccessibility, Body::json($input)->contents());
    }

    public static function updateAppearance(#[\SensitiveParameter] \Automattic\Wistia\Customizations\UpdateAppearanceCustomizationsInput $input): self
    {
        return new self(Operation::Update, ResourceType::CustomizationAppearance, Body::json($input)->contents());
    }

    public static function updateChapters(#[\SensitiveParameter] \Automattic\Wistia\Customizations\UpdateChaptersCustomizationsInput $input): self
    {
        return new self(Operation::Update, ResourceType::CustomizationChapters, Body::json($input)->contents());
    }

    public static function updateEngagement(#[\SensitiveParameter] \Automattic\Wistia\Customizations\UpdateEngagementCustomizationsInput $input): self
    {
        return new self(Operation::Update, ResourceType::CustomizationEngagement, Body::json($input)->contents());
    }

    public static function updateLeadCapture(#[\SensitiveParameter] \Automattic\Wistia\Customizations\UpdateLeadCaptureCustomizationsInput $input): self
    {
        return new self(Operation::Update, ResourceType::CustomizationLeadCapture, Body::json($input)->contents());
    }

    public static function updatePlayback(#[\SensitiveParameter] \Automattic\Wistia\Customizations\UpdatePlaybackCustomizationsInput $input): self
    {
        return new self(Operation::Update, ResourceType::CustomizationPlayback, Body::json($input)->contents());
    }

    public static function updateRelatedMedia(#[\SensitiveParameter] \Automattic\Wistia\Customizations\UpdateRelatedMediaCustomizationsInput $input): self
    {
        return new self(Operation::Update, ResourceType::CustomizationRelatedMedia, Body::json($input)->contents());
    }

    public static function updateSharing(#[\SensitiveParameter] \Automattic\Wistia\Customizations\UpdateSharingCustomizationsInput $input): self
    {
        return new self(Operation::Update, ResourceType::CustomizationSharing, Body::json($input)->contents());
    }

    public static function updateThumbnail(#[\SensitiveParameter] \Automattic\Wistia\Customizations\UpdateThumbnailCustomizationsInput $input): self
    {
        return new self(Operation::Update, ResourceType::CustomizationThumbnail, Body::json($input)->contents());
    }

    public static function createCaptions(#[\SensitiveParameter] string $media_id,
        #[\SensitiveParameter] string $caption_file, #[\SensitiveParameter] ?string $language = null): self
    {
        Input::nonempty($media_id); Input::nonempty($caption_file);
        if ($language !== null) { Input::nonempty($language); }
        $payload = (object) ['media_id' => $media_id, 'caption_file' => $caption_file];
        if ($language !== null) { $payload->language = $language; }
        return new self(Operation::Create, ResourceType::Captions, Body::json($payload)->contents());
    }

    public static function updateCaptions(#[\SensitiveParameter] string $caption_file): self
    {
        Input::nonempty($caption_file);
        return new self(Operation::Update, ResourceType::Captions,
            Body::json((object) ['caption_file' => $caption_file])->contents());
    }

    public static function moveMedia(#[\SensitiveParameter] string $folder_id,
        #[\SensitiveParameter] ?string $subfolder_id = null): self
    {
        Input::nonempty($folder_id);
        $payload = (object) ['folder_id' => $folder_id];
        if ($subfolder_id !== null) { Input::nonempty($subfolder_id); $payload->subfolder_id = $subfolder_id; }
        return new self(Operation::Move, ResourceType::Media, Body::json($payload)->contents());
    }

    public static function delete(ResourceType $resourceType): self
    {
        if (str_starts_with($resourceType->value, 'customization_')) {
            throw new \InvalidArgumentException('Customization concerns support update only.');
        }
        return new self(Operation::Delete, $resourceType);
    }

    public function jsonSerialize(): \stdClass
    {
        $out = (object) ['operation' => $this->operation->value, 'resource_type' => $this->resourceType->value];
        if ($this->payload !== null) { $out->payload = json_decode($this->payload, false, 512, JSON_THROW_ON_ERROR); }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}

<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data;
use Automattic\Wistia\Result\Data as Results;

final readonly class DataCustomizationsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function showCustomizations(#[\SensitiveParameter] Data\ShowCustomizationsRequest $request): Results\ShowCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/customizations',
            RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\ShowCustomizationsResult::class))->result;
    }

    public function createCustomizations(#[\SensitiveParameter] Data\CreateCustomizationsRequest $request): Results\CreateCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/medias/' . Path::segment($request->mediaId) . '/customizations',
            RequestSafety::MayChangeRemoteState, [201],
            $request->query(), $request->body()), new Media\Decoder(Results\CreateCustomizationsResult::class))->result;
    }

    public function updateCustomizations(#[\SensitiveParameter] Data\UpdateCustomizationsRequest $request): Results\UpdateCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/customizations',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\UpdateCustomizationsResult::class))->result;
    }

    public function deleteCustomizations(#[\SensitiveParameter] Data\DeleteCustomizationsRequest $request): Results\DeleteCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Delete,
            '/medias/' . Path::segment($request->mediaId) . '/customizations',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\DeleteCustomizationsResult::class, false))->result;
    }

    public function showAppearanceCustomizations(#[\SensitiveParameter] Data\ShowAppearanceCustomizationsRequest $request): Results\ShowAppearanceCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/appearance',
            RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\ShowAppearanceCustomizationsResult::class))->result;
    }

    public function updateAppearanceCustomizations(#[\SensitiveParameter] Data\UpdateAppearanceCustomizationsRequest $request): Results\UpdateAppearanceCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/appearance',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\UpdateAppearanceCustomizationsResult::class))->result;
    }

    public function showPlaybackCustomizations(#[\SensitiveParameter] Data\ShowPlaybackCustomizationsRequest $request): Results\ShowPlaybackCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/playback',
            RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\ShowPlaybackCustomizationsResult::class))->result;
    }

    public function updatePlaybackCustomizations(#[\SensitiveParameter] Data\UpdatePlaybackCustomizationsRequest $request): Results\UpdatePlaybackCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/playback',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\UpdatePlaybackCustomizationsResult::class))->result;
    }

    public function showThumbnailCustomizations(#[\SensitiveParameter] Data\ShowThumbnailCustomizationsRequest $request): Results\ShowThumbnailCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/thumbnail',
            RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\ShowThumbnailCustomizationsResult::class))->result;
    }

    public function updateThumbnailCustomizations(#[\SensitiveParameter] Data\UpdateThumbnailCustomizationsRequest $request): Results\UpdateThumbnailCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/thumbnail',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\UpdateThumbnailCustomizationsResult::class))->result;
    }

    public function showAccessibilityCustomizations(#[\SensitiveParameter] Data\ShowAccessibilityCustomizationsRequest $request): Results\ShowAccessibilityCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/accessibility',
            RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\ShowAccessibilityCustomizationsResult::class))->result;
    }

    public function updateAccessibilityCustomizations(#[\SensitiveParameter] Data\UpdateAccessibilityCustomizationsRequest $request): Results\UpdateAccessibilityCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/accessibility',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\UpdateAccessibilityCustomizationsResult::class))->result;
    }

    public function showChaptersCustomizations(#[\SensitiveParameter] Data\ShowChaptersCustomizationsRequest $request): Results\ShowChaptersCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/chapters',
            RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\ShowChaptersCustomizationsResult::class))->result;
    }

    public function updateChaptersCustomizations(#[\SensitiveParameter] Data\UpdateChaptersCustomizationsRequest $request): Results\UpdateChaptersCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/chapters',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\UpdateChaptersCustomizationsResult::class))->result;
    }

    public function showEngagementCustomizations(#[\SensitiveParameter] Data\ShowEngagementCustomizationsRequest $request): Results\ShowEngagementCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/engagement',
            RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\ShowEngagementCustomizationsResult::class))->result;
    }

    public function updateEngagementCustomizations(#[\SensitiveParameter] Data\UpdateEngagementCustomizationsRequest $request): Results\UpdateEngagementCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/engagement',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\UpdateEngagementCustomizationsResult::class))->result;
    }

    public function showRelatedMediaCustomizations(#[\SensitiveParameter] Data\ShowRelatedMediaCustomizationsRequest $request): Results\ShowRelatedMediaCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/related_media',
            RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\ShowRelatedMediaCustomizationsResult::class))->result;
    }

    public function updateRelatedMediaCustomizations(#[\SensitiveParameter] Data\UpdateRelatedMediaCustomizationsRequest $request): Results\UpdateRelatedMediaCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/related_media',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\UpdateRelatedMediaCustomizationsResult::class))->result;
    }

    public function showSharingCustomizations(#[\SensitiveParameter] Data\ShowSharingCustomizationsRequest $request): Results\ShowSharingCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/sharing',
            RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\ShowSharingCustomizationsResult::class))->result;
    }

    public function updateSharingCustomizations(#[\SensitiveParameter] Data\UpdateSharingCustomizationsRequest $request): Results\UpdateSharingCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/sharing',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\UpdateSharingCustomizationsResult::class))->result;
    }

    public function showLeadCaptureCustomizations(#[\SensitiveParameter] Data\ShowLeadCaptureCustomizationsRequest $request): Results\ShowLeadCaptureCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/lead_capture',
            RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\ShowLeadCaptureCustomizationsResult::class))->result;
    }

    public function updateLeadCaptureCustomizations(#[\SensitiveParameter] Data\UpdateLeadCaptureCustomizationsRequest $request): Results\UpdateLeadCaptureCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/lead_capture',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\UpdateLeadCaptureCustomizationsResult::class))->result;
    }

    public function showAccessCustomizations(#[\SensitiveParameter] Data\ShowAccessCustomizationsRequest $request): Results\ShowAccessCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/access',
            RequestSafety::ReadOnly, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\ShowAccessCustomizationsResult::class))->result;
    }

    public function updateAccessCustomizations(#[\SensitiveParameter] Data\UpdateAccessCustomizationsRequest $request): Results\UpdateAccessCustomizationsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/medias/' . Path::segment($request->mediaId) . '/customizations/access',
            RequestSafety::MayChangeRemoteState, [200],
            $request->query(), $request->body()), new Media\Decoder(Results\UpdateAccessCustomizationsResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}

<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as D;

final readonly class DataAccountService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function getBrandPreload(#[\SensitiveParameter] R\GetBrandPreloadRequest $request): D\GetBrandPreloadResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/brand_preload', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(D\GetBrandPreloadResult::class))->result;
    }

    public function updateBrandPreload(#[\SensitiveParameter] R\UpdateBrandPreloadRequest $request): D\UpdateBrandPreloadResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Put,
            '/brand_preload', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\UpdateBrandPreloadResult::class))->result;
    }

    public function getBrandKitColors(#[\SensitiveParameter] R\GetBrandKitColorsRequest $request): D\GetBrandKitColorsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Get,
            '/brand_kit_colors', RequestSafety::ReadOnly, [200], $request->query(), $request->body()),
            new Media\Decoder(D\GetBrandKitColorsResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}

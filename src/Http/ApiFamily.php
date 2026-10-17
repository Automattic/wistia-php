<?php
declare(strict_types=1);

namespace Automattic\Wistia\Http;

enum ApiFamily
{
    case Data;
    case Stats;
    case Analytics;
    case Upload;
    case UploadLocalization;
    case OAuth;

    public function baseUri(): string
    {
        return match ($this) {
            self::Data, self::Stats, self::Analytics => 'https://api.wistia.com/modern',
            self::Upload => 'https://upload.wistia.com',
            self::UploadLocalization => 'https://upload-v2.wistia.com',
            self::OAuth => 'https://api.wistia.com/oauth',
        };
    }

    public function usesVersion(): bool
    {
        return match ($this) { self::Upload, self::UploadLocalization, self::OAuth => false, default => true };
    }
}

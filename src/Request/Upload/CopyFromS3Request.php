<?php
declare(strict_types=1);

namespace Automattic\Wistia\Request\Upload;

use Automattic\Wistia\Upload\UploadOptions;

final readonly class CopyFromS3Request
{
    public function __construct(
        #[\SensitiveParameter] public string $bucket,
        #[\SensitiveParameter] public string $source_file,
        #[\SensitiveParameter] public ?string $region = null,
        #[\SensitiveParameter] public ?UploadOptions $options = null,
    ) {
        // Syntax guards only: bucket eligibility, IAM setup and object existence are remote concerns.
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]*$/D', $bucket)) {
            throw new \InvalidArgumentException('Use a nonempty S3 bucket name, not an ARN, URL or path.');
        }
        if ($source_file === '' || str_starts_with($source_file, '/') || preg_match('/[\x00-\x1f\x7f]/', $source_file)) {
            throw new \InvalidArgumentException('Use a nonempty S3 object path without a leading slash or control characters.');
        }
        if ($region !== null && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)+$/D', $region)) {
            throw new \InvalidArgumentException('Use an AWS region identifier or omit it for the server default.');
        }
    }
    public function __debugInfo(): array { return []; }
}

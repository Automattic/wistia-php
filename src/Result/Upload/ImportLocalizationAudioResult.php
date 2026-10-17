<?php
declare(strict_types=1);

namespace Automattic\Wistia\Result\Upload;

use Automattic\Wistia\Result\Result;

/** Accepted with no response body. Does not assert completed remote processing. */
final readonly class ImportLocalizationAudioResult implements Result
{
    public function __construct(public int $status) {}
}

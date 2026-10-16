<?php
declare(strict_types=1);
namespace Automattic\Wistia\Accessibility;

use Automattic\Wistia\Http\{JsonDecoder, ResponseDecoder, TransportResponse};
use Automattic\Wistia\Request\Data\CaptionFormat;
use Automattic\Wistia\Result\Data\ShowCaptionsResult;

/** @internal Explicit selected format; text bytes never pass through JSON. */
final readonly class CaptionDecoder implements ResponseDecoder
{
    public function __construct(private CaptionFormat $format) {}
    public function decode(#[\SensitiveParameter] TransportResponse $response): ShowCaptionsResult
    {
        if ($this->format === CaptionFormat::Json) {
            $data = ListCaptionsByMediaDataItem::fromWire((new JsonDecoder())->decode($response)->value);
        } else {
            $type = strtolower(trim(explode(';', $response->header('Content-Type') ?? '')[0]));
            $expected = $this->format === CaptionFormat::Vtt ? 'text/vtt' : 'text/plain';
            if ($type !== $expected) { throw new \UnexpectedValueException('Unexpected caption content type.'); }
            $data = $response->body;
        }
        return new ShowCaptionsResult($data, $response->status, $this->format);
    }
}

<?php
declare(strict_types=1);
namespace Automattic\Wistia\Request\Data;

enum CaptionFormat: string
{
    case Json = 'json';
    case Srt = 'srt';
    case Vtt = 'vtt';
    case Txt = 'txt';

    public function suffix(): string { return $this === self::Json ? '' : '.' . $this->value; }
}

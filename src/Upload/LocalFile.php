<?php
declare(strict_types=1);

namespace Automattic\Wistia\Upload;

use GuzzleHttp\Psr7\PumpStream;

/** A local path (library-owned handle) or caller-owned readable, seekable stream. */
final readonly class LocalFile
{
    private function __construct(
        #[\SensitiveParameter] private ?string $path,
        #[\SensitiveParameter] private mixed $stream,
        #[\SensitiveParameter] public string $filename,
        #[\SensitiveParameter] public string $contentType,
    ) {
        if ($filename === '' || preg_match('/[\x00-\x1f\x7f"\\\\\/]/', $filename)
            || !preg_match('~^[a-zA-Z0-9!#$&^_.+-]+/[a-zA-Z0-9!#$&^_.+-]+$~D', $contentType)) {
            throw new \InvalidArgumentException('Use a plain filename and a valid media content type.');
        }
    }

    public static function fromPath(#[\SensitiveParameter] string $path, #[\SensitiveParameter] ?string $filename = null, #[\SensitiveParameter] string $contentType = 'application/octet-stream'): self
    {
        if (str_contains($path, "\0") || str_contains($path, '://') || !is_file($path) || !is_readable($path)) {
            throw new \InvalidArgumentException('Upload requires a readable local regular file.');
        }
        return new self($path, null, $filename ?? basename($path), $contentType);
    }

    /** The caller retains ownership; bytes are consumed from the current position without rewinding. */
    public static function fromStream(#[\SensitiveParameter] mixed $stream, #[\SensitiveParameter] string $filename, #[\SensitiveParameter] string $contentType = 'application/octet-stream'): self
    {
        self::remaining($stream);
        return new self(null, $stream, $filename, $contentType);
    }

    private static function remaining(#[\SensitiveParameter] mixed $stream): int
    {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new \InvalidArgumentException('Upload requires an open readable seekable stream.');
        }
        $meta = stream_get_meta_data($stream);
        $stat = fstat($stream);
        $position = ftell($stream);
        if (!$meta['seekable'] || (!str_contains($meta['mode'], 'r') && !str_contains($meta['mode'], '+'))
            || $stat === false || ($stat['mode'] & 0170000) !== 0100000 || $position === false || $stat['size'] <= $position) {
            throw new \InvalidArgumentException('Upload requires a nonempty readable regular file or seekable temporary stream.');
        }
        return $stat['size'] - $position;
    }

    /** @internal Opens only for this operation; the non-owning PSR stream never closes a borrowed handle. */
    public function withStream(#[\SensitiveParameter] callable $operation): mixed
    {
        $handle = $this->stream;
        if ($this->path !== null) {
            // Revalidate before opening: files may have disappeared since request construction.
            if (!is_file($this->path) || !is_readable($this->path)) {
                throw new \InvalidArgumentException('Upload file is no longer readable.');
            }
            $handle = @fopen($this->path, 'rb');
        }
        try {
            $remaining = self::remaining($handle);
            $stream = new PumpStream(static function (int $length) use ($handle): string|false {
                $bytes = fread($handle, $length);
                if ($bytes === false) { throw new \RuntimeException('Cannot read upload file.'); }
                return $bytes === '' ? false : $bytes;
            }, ['size' => $remaining]);
            return $operation($stream);
        } finally {
            if ($this->path !== null && is_resource($handle)) { fclose($handle); }
        }
    }

    public function __debugInfo(): array { return []; }
    public function __serialize(): array { throw new \LogicException('Upload files cannot be serialized.'); }
}

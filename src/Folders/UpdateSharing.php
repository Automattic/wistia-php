<?php
declare(strict_types=1);

namespace Automattic\Wistia\Folders;

final readonly class UpdateSharing implements \JsonSerializable
{
    public function __construct(
        public ?bool $canShare = null,
        public ?bool $canDownload = null,
        public ?bool $canUpload = null,
        public ?bool $isAdmin = null,
    ) {}
    public function jsonSerialize(): object
    {
        return (object) array_filter(get_object_vars($this), static fn($v) => $v !== null);
    }
    public function __debugInfo(): array { return []; }
}

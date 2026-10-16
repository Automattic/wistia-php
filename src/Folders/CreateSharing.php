<?php
declare(strict_types=1);

namespace Automattic\Wistia\Folders;

use Automattic\Wistia\Media\Input;

/** Nested sharing input. Notifications are always sent, including when the deprecated flag is "0". */
final readonly class CreateSharing implements \JsonSerializable
{
    public function __construct(
        #[\SensitiveParameter] public string $with,
        public ?bool $requirePassword = null,
        public ?bool $canShare = null,
        public ?bool $canDownload = null,
        public ?bool $canUpload = null,
        /** @deprecated Wistia always sends notifications. */
        public ?string $sendEmailNotification = null,
    ) {
        Input::nonempty($with);
        if ($sendEmailNotification !== null) { Input::oneOf($sendEmailNotification, ['0', '1']); }
    }
    public function jsonSerialize(): object
    {
        return (object) array_filter(get_object_vars($this), static fn($v) => $v !== null);
    }
    public function __debugInfo(): array { return []; }
}
